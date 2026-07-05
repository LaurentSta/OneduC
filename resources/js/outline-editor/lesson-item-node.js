import { Node, mergeAttributes } from '@tiptap/core';
import { nextClientKey } from './client-key';
import {
  closeOpenMenu, openMenu, isMenuOpen, setHighlightedRow, createHandleIcon, createDotsIcon,
  createDuplicateIcon, createTrashIcon, createMenuItem,
} from './row-controls';

// Shared across every lessonItem node view instance in this module — simpler and more
// reliable than a custom dataTransfer MIME type, whose propagation across dragover/drop
// isn't consistent in every browser/automation context.
let draggingLessonClientKey = null;

function sectionKeyForRange(ranges, index) {
  for (let i = index; i >= 0; i -= 1) {
    if (ranges[i].node.type.name === 'chapterHeading') return ranges[i].node.attrs.clientKey;
  }

  return null;
}

function moveLessonByDrag(editor, draggedClientKey, targetClientKey) {
  return editor.commands.command(({ tr, state, dispatch }) => {
    const doc = state.doc;
    const ranges = [];
    doc.forEach((n, offset) => ranges.push({ node: n, from: offset, to: offset + n.nodeSize }));

    const dragIndex = ranges.findIndex((r) => r.node.attrs.clientKey === draggedClientKey);
    const dropIndex = ranges.findIndex((r) => r.node.attrs.clientKey === targetClientKey);
    if (dragIndex === -1 || dropIndex === -1 || dragIndex === dropIndex) return false;

    const dragRange = ranges[dragIndex];
    const dropRange = ranges[dropIndex];
    if (dragRange.node.type.name !== 'lessonItem' || dropRange.node.type.name !== 'lessonItem') return false;
    if (sectionKeyForRange(ranges, dragIndex) !== sectionKeyForRange(ranges, dropIndex)) return false; // different chapters: refuse

    if (!dispatch) return true;

    const dragSlice = doc.slice(dragRange.from, dragRange.to);
    tr.delete(dragRange.from, dragRange.to);

    const insertPos = tr.mapping.map(dropRange.to);
    tr.insert(insertPos, dragSlice.content);

    dispatch(tr);

    return true;
  });
}

export const LessonItem = Node.create({
  name: 'lessonItem',
  group: 'block',
  content: 'text*',
  marks: '',

  addAttributes() {
    return {
      clientKey: {
        default: null,
        parseHTML: (el) => el.getAttribute('data-client-key') || nextClientKey('lecture'),
        renderHTML: (attrs) => ({ 'data-client-key': attrs.clientKey }),
      },
      lectureId: {
        default: null,
        parseHTML: (el) => {
          const value = el.getAttribute('data-lecture-id');

          return value ? Number(value) : null;
        },
        renderHTML: (attrs) => (attrs.lectureId ? { 'data-lecture-id': attrs.lectureId } : {}),
      },
      contentType: {
        default: 'blocks',
        parseHTML: (el) => el.getAttribute('data-content-type') || 'blocks',
        renderHTML: (attrs) => ({ 'data-content-type': attrs.contentType }),
      },
      editUrl: {
        default: null,
        parseHTML: (el) => el.getAttribute('data-edit-url'),
        renderHTML: (attrs) => (attrs.editUrl ? { 'data-edit-url': attrs.editUrl } : {}),
      },
    };
  },

  parseHTML() {
    return [{ tag: 'div[data-type="lesson-item"]' }];
  },

  renderHTML({ HTMLAttributes }) {
    return ['div', mergeAttributes(HTMLAttributes, { 'data-type': 'lesson-item' }), 0];
  },

  addNodeView() {
    return ({ node, editor }) => {
      let currentNode = node;

      const dom = document.createElement('div');
      dom.setAttribute('data-type', 'lesson-item');
      dom.className = 'group relative flex items-start gap-2 rounded-[6px] py-1 pl-1 hover:bg-orange-50/40';

      const handle = document.createElement('span');
      handle.contentEditable = 'false';
      handle.draggable = true;
      handle.className = 'mt-1 inline-flex shrink-0 cursor-grab select-none items-center rounded px-1 py-1 text-gray-300 hover:bg-gray-100 hover:text-gray-500 active:cursor-grabbing';
      handle.appendChild(createHandleIcon());
      handle.title = 'Glisser pour réordonner dans le chapitre';
      handle.addEventListener('dragstart', (event) => {
        draggingLessonClientKey = currentNode.attrs.clientKey;
        event.dataTransfer.effectAllowed = 'move';
        event.dataTransfer.setData('text/plain', currentNode.attrs.clientKey);
      });
      handle.addEventListener('dragend', () => {
        draggingLessonClientKey = null;
        setHighlightedRow(null);
      });
      dom.appendChild(handle);

      dom.addEventListener('dragover', (event) => {
        if (!draggingLessonClientKey) return;
        event.preventDefault();
        event.dataTransfer.dropEffect = 'move';
        setHighlightedRow(dom);
      });
      dom.addEventListener('drop', (event) => {
        if (!draggingLessonClientKey) return;
        event.preventDefault();
        setHighlightedRow(null);

        const draggedClientKey = draggingLessonClientKey;
        draggingLessonClientKey = null;
        if (draggedClientKey === currentNode.attrs.clientKey) return;

        const handled = moveLessonByDrag(editor, draggedClientKey, currentNode.attrs.clientKey);
        if (handled) {
          window.dispatchEvent(new CustomEvent('outline:request-move', {
            detail: { clientKey: draggedClientKey, type: 'lessonItem' },
          }));
        }
      });

      const titleWrapper = document.createElement('div');
      titleWrapper.className = 'flex-1 py-1';
      dom.appendChild(titleWrapper);

      const contentDOM = document.createElement('div');
      contentDOM.className = 'text-base text-gray-800 outline-none';
      titleWrapper.appendChild(contentDOM);

      const subLabel = document.createElement('div');
      subLabel.contentEditable = 'false';
      subLabel.className = 'select-none text-xs text-gray-400';
      subLabel.textContent = 'Leçon';
      titleWrapper.appendChild(subLabel);

      const action = document.createElement('a');
      action.contentEditable = 'false';

      const menuWrapper = document.createElement('span');
      menuWrapper.contentEditable = 'false';
      menuWrapper.className = 'relative mt-0.5 shrink-0';

      const menuButton = document.createElement('button');
      menuButton.type = 'button';
      menuButton.className = 'flex h-7 w-7 items-center justify-center rounded-full text-gray-400 hover:bg-gray-100 hover:text-gray-600';
      menuButton.title = 'Options de la leçon';
      menuButton.appendChild(createDotsIcon());

      const menuPanel = document.createElement('div');
      menuPanel.className = 'absolute right-0 top-8 z-10 hidden min-w-[140px] rounded-lg border border-gray-200 bg-white py-1 shadow-lg';

      const duplicateMenuItem = createMenuItem({
        icon: createDuplicateIcon(),
        label: 'Dupliquer',
        className: 'text-gray-600 hover:bg-gray-50',
        onClick: (event) => {
          event.preventDefault();
          closeOpenMenu();
          if (!currentNode.attrs.lectureId) return; // not saved yet, nothing to duplicate
          window.dispatchEvent(new CustomEvent('outline:request-duplicate', {
            detail: { clientKey: currentNode.attrs.clientKey, lectureId: currentNode.attrs.lectureId },
          }));
        },
      });
      menuPanel.appendChild(duplicateMenuItem);

      const deleteMenuItem = createMenuItem({
        icon: createTrashIcon(),
        label: 'Supprimer',
        className: 'text-red-500 hover:bg-red-50',
        onClick: (event) => {
          event.preventDefault();
          closeOpenMenu();
          window.dispatchEvent(new CustomEvent('outline:request-delete', {
            detail: { type: 'lecture', id: currentNode.attrs.lectureId, clientKey: currentNode.attrs.clientKey },
          }));
        },
      });
      menuPanel.appendChild(deleteMenuItem);

      menuButton.addEventListener('mousedown', (event) => {
        event.preventDefault();
        event.stopPropagation();
        if (isMenuOpen(menuPanel)) {
          closeOpenMenu();
          return;
        }
        openMenu(menuPanel, dom);
      });

      menuWrapper.appendChild(menuButton);
      menuWrapper.appendChild(menuPanel);

      const renderAction = () => {
        const isLocked = currentNode.attrs.contentType !== 'blocks';
        action.textContent = isLocked ? '🔒 Verrouillée' : 'Ouvrir';
        action.title = isLocked
          ? 'Contenu importé depuis le catalogue, non modifiable ici'
          : 'Ouvrir la leçon pour en éditer le contenu';
        action.className = isLocked
          ? 'mt-0.5 inline-flex shrink-0 items-center rounded-full bg-gray-100 px-3 py-1 text-xs font-semibold text-gray-400'
          : 'mt-0.5 inline-flex shrink-0 items-center rounded-full border-2 border-bleuone bg-bleuone px-3 py-1 text-xs font-semibold text-white transition-colors hover:bg-white hover:text-bleuone';

        if (currentNode.attrs.editUrl) {
          action.href = currentNode.attrs.editUrl;
          action.removeAttribute('aria-disabled');
        } else {
          action.removeAttribute('href');
          action.setAttribute('aria-disabled', 'true');
        }
      };

      renderAction();
      dom.appendChild(action);
      dom.appendChild(menuWrapper);

      return {
        dom,
        contentDOM,
        update(updatedNode) {
          if (updatedNode.type.name !== 'lessonItem') return false;
          currentNode = updatedNode;
          renderAction();

          return true;
        },
        // The drag handle, "Ouvrir" action and "..." menu live inside this node view's
        // dom but outside contentDOM. ProseMirror's DOM observer otherwise reverts any
        // mutation in that region (e.g. toggling the menu's "hidden" class) since it
        // doesn't come from a document transaction.
        ignoreMutation(mutation) {
          return !contentDOM.contains(mutation.target);
        },
      };
    };
  },
});
