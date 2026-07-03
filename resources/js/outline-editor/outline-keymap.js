import { Extension } from '@tiptap/core';
import { TextSelection } from '@tiptap/pm/state';
import { nextClientKey } from './client-key';

function topLevelRanges(doc) {
  const ranges = [];
  doc.forEach((node, offset) => {
    ranges.push({ node, from: offset, to: offset + node.nodeSize });
  });

  return ranges;
}

function moveNode(editor, direction, onMove) {
  let moved = null;

  const handled = editor.commands.command(({ tr, state, dispatch }) => {
    const { $from } = state.selection;
    const doc = state.doc;
    const ranges = topLevelRanges(doc);
    const index = $from.index(0);
    const targetIndex = index + direction;

    if (index === 0) return false; // the very first chapter never moves
    if (targetIndex < 0 || targetIndex >= ranges.length) return false;
    // The document's first slot must always stay a chapterHeading.
    if (targetIndex === 0 && ranges[index].node.type.name !== 'chapterHeading') return false;

    if (!dispatch) return true;

    const lowIndex = Math.min(index, targetIndex);
    const highIndex = Math.max(index, targetIndex);
    const low = ranges[lowIndex];
    const high = ranges[highIndex];

    const highSlice = doc.slice(high.from, high.to);
    const lowSlice = doc.slice(low.from, low.to);

    tr.delete(high.from, high.to).delete(low.from, low.to);
    tr.insert(low.from, highSlice.content).insert(low.from + highSlice.content.size, lowSlice.content);

    const newPos = direction < 0 ? low.from + 1 : low.from + highSlice.content.size + 1;
    tr.setSelection(TextSelection.near(tr.doc.resolve(Math.min(newPos, tr.doc.content.size - 1))));
    dispatch(tr);

    moved = { clientKey: ranges[index].node.attrs.clientKey, type: ranges[index].node.type.name };

    return true;
  });

  if (handled && moved) onMove(moved);

  return handled;
}

export const OutlineKeymap = Extension.create({
  name: 'outlineKeymap',

  addOptions() {
    return {
      onPromote: () => {},
      onMove: () => {},
    };
  },

  addKeyboardShortcuts() {
    return {
      Enter: () => this.editor.commands.command(({ tr, state, dispatch }) => {
        const { $from } = state.selection;
        if (!['chapterHeading', 'lessonItem'].includes($from.parent.type.name)) return false;
        if (!dispatch) return true;

        if (!state.selection.empty) tr.deleteSelection();

        const pos = tr.selection.from;
        const lessonItemType = state.schema.nodes.lessonItem;
        tr.split(pos, 1, [{ type: lessonItemType, attrs: { clientKey: nextClientKey('lecture') } }]);
        dispatch(tr);

        return true;
      }),

      'Shift-Enter': () => {
        let promoted = null;

        const handled = this.editor.commands.command(({ tr, state, dispatch }) => {
          const { $from } = state.selection;
          if ($from.parent.type.name !== 'lessonItem') return true; // no-op elsewhere
          if (!dispatch) return true;

          const nodePos = $from.before($from.depth);
          const currentNode = $from.parent;
          const chapterHeadingType = state.schema.nodes.chapterHeading;
          const lessonItemType = state.schema.nodes.lessonItem;

          promoted = {
            clientKey: currentNode.attrs.clientKey,
            lectureId: currentNode.attrs.lectureId,
            title: currentNode.textContent,
          };

          // Convert this line in place into a chapter heading, keeping its clientKey
          // so the reconciler recognizes it as "the same line, promoted".
          tr.setNodeMarkup(nodePos, chapterHeadingType, { clientKey: currentNode.attrs.clientKey });

          const insertPos = nodePos + currentNode.nodeSize;
          const newLesson = lessonItemType.create({ clientKey: nextClientKey('lecture') });
          tr.insert(insertPos, newLesson);
          tr.setSelection(TextSelection.near(tr.doc.resolve(insertPos + 1)));
          dispatch(tr);

          return true;
        });

        if (handled && promoted) this.options.onPromote(promoted);

        return handled;
      },

      Backspace: () => this.editor.commands.command(({ tr, state, dispatch }) => {
        const { $from, empty } = state.selection;
        if (!empty || $from.parentOffset !== 0) return false;

        const nodeType = $from.parent.type.name;
        if (!['chapterHeading', 'lessonItem'].includes(nodeType)) return false;

        const index = $from.index(0);
        if (index === 0) return true; // nothing before the very first node
        if (nodeType === 'chapterHeading') return true; // chapters are only deleted via the × button

        const ranges = topLevelRanges(state.doc);
        const previous = ranges[index - 1];
        if (previous.node.type.name === 'chapterHeading') return true; // never merge into a chapter title

        if (!dispatch) return true;

        tr.join($from.before($from.depth));
        dispatch(tr);

        return true;
      }),

      'Alt-ArrowUp': () => moveNode(this.editor, -1, this.options.onMove),
      'Alt-ArrowDown': () => moveNode(this.editor, 1, this.options.onMove),
    };
  },
});
