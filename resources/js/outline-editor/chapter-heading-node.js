import { Node, mergeAttributes } from '@tiptap/core';
import { nextClientKey } from './client-key';
import {
  closeOpenMenu, openMenu, isMenuOpen, createDotsIcon, createTrashIcon, createMenuItem,
} from './row-controls';

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
      contentDOM.className = 'border-b-2 border-orangeone pb-2 text-2xl font-bold text-bleuone outline-none';
      dom.appendChild(contentDOM);

      const menuWrapper = document.createElement('span');
      menuWrapper.contentEditable = 'false';
      menuWrapper.className = 'absolute right-0 top-8 group-first:top-2';

      const menuButton = document.createElement('button');
      menuButton.type = 'button';
      menuButton.className = 'flex h-7 w-7 items-center justify-center rounded-full text-gray-400 hover:bg-gray-100 hover:text-gray-600';
      menuButton.title = 'Options du chapitre';
      menuButton.appendChild(createDotsIcon());

      const menuPanel = document.createElement('div');
      menuPanel.className = 'absolute right-0 top-8 z-10 hidden min-w-[140px] rounded-lg border border-gray-200 bg-white py-1 shadow-lg';

      const deleteMenuItem = createMenuItem({
        icon: createTrashIcon(),
        label: 'Supprimer',
        className: 'text-red-500 hover:bg-red-50',
        onClick: (event) => {
          event.preventDefault();
          closeOpenMenu();
          window.dispatchEvent(new CustomEvent('outline:request-delete', {
            detail: { type: 'section', id: currentNode.attrs.sectionId, clientKey: currentNode.attrs.clientKey },
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
      dom.appendChild(menuWrapper);

      return {
        dom,
        contentDOM,
        update(updatedNode) {
          if (updatedNode.type.name !== 'chapterHeading') return false;
          currentNode = updatedNode;

          return true;
        },
        // See lessonItem's node view for why this is needed: ProseMirror otherwise
        // reverts DOM mutations (like the menu's "hidden" class) made outside contentDOM.
        ignoreMutation(mutation) {
          return !contentDOM.contains(mutation.target);
        },
      };
    };
  },
});
