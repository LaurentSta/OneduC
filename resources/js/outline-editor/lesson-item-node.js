import { Node, mergeAttributes } from '@tiptap/core';
import { nextClientKey } from './client-key';
import { createEditPencilIcon } from './edit-pencil-icon';

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

      const bullet = document.createElement('span');
      bullet.contentEditable = 'false';
      bullet.className = 'mt-[10px] h-1.5 w-1.5 shrink-0 rounded-full bg-gray-400';
      dom.appendChild(bullet);

      const contentDOM = document.createElement('div');
      contentDOM.className = 'flex-1 py-1 text-sm text-gray-800 outline-none';
      dom.appendChild(contentDOM);

      const editIcon = createEditPencilIcon('pointer-events-none mt-1 h-5 w-5 shrink-0 hidden text-gray-300 group-hover:inline-block');
      dom.appendChild(editIcon);

      const action = document.createElement('a');
      action.contentEditable = 'false';

      const deleteButton = document.createElement('button');
      deleteButton.type = 'button';
      deleteButton.contentEditable = 'false';
      deleteButton.className = 'mt-1 hidden shrink-0 text-xs text-red-400 hover:text-red-600 group-hover:inline-flex';
      deleteButton.textContent = '✕';
      deleteButton.title = 'Supprimer cette leçon';
      deleteButton.addEventListener('mousedown', (event) => {
        event.preventDefault();
        window.dispatchEvent(new CustomEvent('outline:request-delete', {
          detail: { type: 'lecture', id: currentNode.attrs.lectureId, clientKey: currentNode.attrs.clientKey },
        }));
      });

      const renderAction = () => {
        const isLocked = currentNode.attrs.contentType !== 'blocks';
        action.textContent = isLocked ? '🔒 Verrouillée' : 'Ouvrir';
        action.title = isLocked
          ? 'Contenu importé depuis le catalogue, non modifiable ici'
          : 'Ouvrir la leçon pour en éditer le contenu';
        action.className = isLocked
          ? 'mt-0.5 inline-flex shrink-0 items-center rounded-full bg-gray-100 px-3 py-1 text-xs font-semibold text-gray-400'
          : 'mt-0.5 inline-flex shrink-0 items-center rounded-full border-2 border-orangeone bg-orangeone px-3 py-1 text-xs font-semibold text-white transition-colors hover:bg-white hover:text-orangeone';

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
      dom.appendChild(deleteButton);

      return {
        dom,
        contentDOM,
        update(updatedNode) {
          if (updatedNode.type.name !== 'lessonItem') return false;
          currentNode = updatedNode;
          renderAction();

          return true;
        },
      };
    };
  },
});
