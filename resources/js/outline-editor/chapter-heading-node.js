import { Node, mergeAttributes } from '@tiptap/core';
import { nextClientKey } from './client-key';
import { createEditPencilIcon } from './edit-pencil-icon';

export const ChapterHeading = Node.create({
  name: 'chapterHeading',
  group: 'block',
  content: 'text*',
  marks: '',
  defining: true,

  addAttributes() {
    return {
      clientKey: {
        default: null,
        parseHTML: (el) => el.getAttribute('data-client-key') || nextClientKey('section'),
        renderHTML: (attrs) => ({ 'data-client-key': attrs.clientKey }),
      },
      sectionId: {
        default: null,
        parseHTML: (el) => {
          const value = el.getAttribute('data-section-id');

          return value ? Number(value) : null;
        },
        renderHTML: (attrs) => (attrs.sectionId ? { 'data-section-id': attrs.sectionId } : {}),
      },
    };
  },

  parseHTML() {
    return [{ tag: 'div[data-type="chapter-heading"]' }];
  },

  renderHTML({ HTMLAttributes }) {
    return ['div', mergeAttributes(HTMLAttributes, { 'data-type': 'chapter-heading' }), 0];
  },

  addNodeView() {
    return ({ node }) => {
      let currentNode = node;

      const dom = document.createElement('div');
      dom.setAttribute('data-type', 'chapter-heading');
      dom.className = 'group relative pt-8 pb-1 first:pt-2';

      const label = document.createElement('div');
      label.contentEditable = 'false';
      label.className = 'mb-1 select-none text-xs font-bold uppercase tracking-widest text-gray-400';
      label.textContent = 'Chapitre';
      dom.appendChild(label);

      const contentDOM = document.createElement('div');
      contentDOM.className = 'border-b-2 border-orangeone pb-2 text-lg font-bold text-bleuone outline-none';
      dom.appendChild(contentDOM);

      const editIcon = createEditPencilIcon('pointer-events-none absolute right-6 top-8 h-6 w-6 hidden text-gray-300 group-hover:inline-block group-first:top-2');
      dom.appendChild(editIcon);

      const deleteButton = document.createElement('button');
      deleteButton.type = 'button';
      deleteButton.contentEditable = 'false';
      deleteButton.className = 'absolute right-0 top-8 hidden text-xs text-red-400 hover:text-red-600 group-hover:inline-flex group-first:top-2';
      deleteButton.textContent = '✕';
      deleteButton.title = 'Supprimer ce chapitre';
      deleteButton.addEventListener('mousedown', (event) => {
        event.preventDefault();
        window.dispatchEvent(new CustomEvent('outline:request-delete', {
          detail: { type: 'section', id: currentNode.attrs.sectionId, clientKey: currentNode.attrs.clientKey },
        }));
      });
      dom.appendChild(deleteButton);

      return {
        dom,
        contentDOM,
        update(updatedNode) {
          if (updatedNode.type.name !== 'chapterHeading') return false;
          currentNode = updatedNode;

          return true;
        },
      };
    };
  },
});
