import Document from '@tiptap/extension-document';

// A flat sequence: the first node is always a chapter heading, everything
// after can be any mix of chapter headings and lesson items. Document order
// *is* the sections/lectures order — no nested tree needed.
export const OutlineDocument = Document.extend({
  content: 'chapterHeading (chapterHeading|lessonItem)*',
});
