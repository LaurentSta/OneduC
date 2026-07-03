import React, { useEffect, useRef } from 'react';
import { createRoot } from 'react-dom/client';
import { useEditor, EditorContent } from '@tiptap/react';
import StarterKit from '@tiptap/starter-kit';
import Placeholder from '@tiptap/extension-placeholder';
import { OutlineDocument } from './outline-document';
import { ChapterHeading } from './chapter-heading-node';
import { LessonItem } from './lesson-item-node';
import { OutlineKeymap } from './outline-keymap';
import { createOutlineApi } from './api';
import { createSyncQueue } from './sync-queue';
import { createReconciler } from './reconcile';
import { nextClientKey } from './client-key';

function stableClientKey(node) {
  if (node.type === 'chapterHeading' && node.sectionId) return `section-${node.sectionId}`;
  if (node.type === 'lessonItem' && node.lectureId) return `lecture-${node.lectureId}`;

  return nextClientKey(node.type === 'chapterHeading' ? 'section' : 'lecture');
}

function buildInitialDoc(nodes, basePath) {
  // A brand-new module seeds a single empty chapter — no placeholder lesson
  // underneath it. Pressing Enter naturally creates the first lesson line;
  // pre-seeding one too would leave a stray empty line behind it, since
  // splitting always inserts a new sibling rather than reusing the next one.
  const source = nodes.length ? nodes : [{ type: 'chapterHeading', sectionId: null, title: '' }];

  return {
    type: 'doc',
    content: source.map((node) => ({
      type: node.type,
      attrs: {
        clientKey: stableClientKey(node),
        sectionId: node.sectionId ?? null,
        lectureId: node.lectureId ?? null,
        contentType: node.contentType ?? 'blocks',
        editUrl: node.lectureId ? `${basePath}/lectures/${node.lectureId}/edition` : null,
      },
      content: node.title ? [{ type: 'text', text: node.title }] : undefined,
    })),
  };
}

function OutlineEditorApp({ moduleId, basePath, initialNodes }) {
  const apiRef = useRef(null);
  const queueRef = useRef(null);
  const reconcilerRef = useRef(null);

  if (!apiRef.current) apiRef.current = createOutlineApi(basePath, moduleId);
  if (!queueRef.current) queueRef.current = createSyncQueue();

  const editor = useEditor({
    extensions: [
      OutlineDocument,
      ChapterHeading,
      LessonItem,
      StarterKit.configure({
        document: false,
        paragraph: false,
        heading: false,
        bulletList: false,
        orderedList: false,
        listItem: false,
        blockquote: false,
        codeBlock: false,
        horizontalRule: false,
        hardBreak: false,
        trailingNode: false,
        bold: false,
        italic: false,
        strike: false,
        code: false,
        link: false,
      }),
      Placeholder.configure({
        placeholder: ({ node }) => {
          if (node.type.name === 'chapterHeading') return 'Titre du chapitre';
          if (node.type.name === 'lessonItem') return 'Ajouter une leçon...';

          return '';
        },
      }),
      OutlineKeymap.configure({
        onPromote: ({ clientKey, lectureId, title }) => {
          queueRef.current.enqueue(async () => {
            const section = lectureId
              ? await apiRef.current.promoteLecture(lectureId)
              : await apiRef.current.createSection(title);

            reconcilerRef.current?.applyAttrs(clientKey, { sectionId: section.id, lectureId: null });
          });
        },
        onMove: ({ clientKey, type }) => {
          queueRef.current.enqueue(async () => {
            const flatNodes = [];
            editor.state.doc.forEach((node) => flatNodes.push(node));

            if (type === 'chapterHeading') {
              const sectionIds = flatNodes
                .filter((n) => n.type.name === 'chapterHeading' && n.attrs.sectionId)
                .map((n) => n.attrs.sectionId);

              if (sectionIds.length > 1) await apiRef.current.reorderSections(sectionIds);

              return;
            }

            let currentSectionId = null;
            let movedLectureId = null;
            let position = 0;

            for (const node of flatNodes) {
              if (node.type.name === 'chapterHeading') {
                currentSectionId = node.attrs.sectionId;
                position = 0;
                continue;
              }
              if (node.attrs.clientKey === clientKey) {
                movedLectureId = node.attrs.lectureId;
                break;
              }
              position += 1;
            }

            if (movedLectureId && currentSectionId) {
              await apiRef.current.moveLecture(movedLectureId, currentSectionId, position);
            }
          });
        },
      }),
    ],
    content: buildInitialDoc(initialNodes, basePath),
    onCreate: ({ editor: createdEditor }) => {
      reconcilerRef.current = createReconciler({
        editor: createdEditor,
        api: apiRef.current,
        queue: queueRef.current,
        buildEditUrl: (lectureId) => `${basePath}/lectures/${lectureId}/edition`,
      });
      reconcilerRef.current.seed(createdEditor.state.doc);
    },
    onUpdate: () => {
      reconcilerRef.current?.reconcile();
    },
    editorProps: {
      attributes: {
        class: 'outline-editor-prosemirror focus:outline-none',
      },
    },
  });

  useEffect(() => {
    function handleDeleted(event) {
      const { clientKey } = event.detail || {};
      if (!clientKey || !editor) return;

      editor.commands.command(({ tr, dispatch }) => {
        let target = null;
        tr.doc.forEach((node, offset) => {
          if (target === null && node.attrs.clientKey === clientKey) target = { from: offset, to: offset + node.nodeSize };
        });
        if (!target) return false;
        if (!dispatch) return true;

        tr.delete(target.from, target.to);
        dispatch(tr);

        return true;
      });
    }

    window.addEventListener('outline:deleted', handleDeleted);

    return () => window.removeEventListener('outline:deleted', handleDeleted);
  }, [editor]);

  return <EditorContent editor={editor} />;
}

export function mountOutlineEditor() {
  document.querySelectorAll('[data-outline-editor]').forEach((container) => {
    if (container.dataset.outlineEditorMounted === '1') return;
    container.dataset.outlineEditorMounted = '1';

    const moduleId = container.dataset.moduleId;
    const basePath = container.dataset.basePath || '';

    let initialNodes = [];
    try {
      initialNodes = JSON.parse(container.dataset.initialDoc || '[]');
      if (!Array.isArray(initialNodes)) initialNodes = [];
    } catch (e) {
      initialNodes = [];
    }

    createRoot(container).render(
      <OutlineEditorApp moduleId={moduleId} basePath={basePath} initialNodes={initialNodes} />
    );
  });
}
