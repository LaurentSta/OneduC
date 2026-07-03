function flatten(doc) {
  const nodes = [];
  doc.forEach((node) => {
    nodes.push({
      clientKey: node.attrs.clientKey,
      type: node.type.name,
      sectionId: node.attrs.sectionId ?? null,
      lectureId: node.attrs.lectureId ?? null,
      contentType: node.attrs.contentType ?? 'blocks',
      title: node.textContent,
    });
  });

  return nodes;
}

// Reconciles a Tiptap outline document against the backend. Only handles the
// two *continuous* concerns (create-once-titled, rename-on-title-change) —
// discrete keyboard actions (promote, move/reorder) push their own sync jobs
// directly from the keymap, since they are single events, not a text stream.
export function createReconciler({ editor, api, queue, buildEditUrl }) {
  let previous = [];

  function seed(doc) {
    previous = flatten(doc);
  }

  function findPos(clientKey) {
    let found = null;
    editor.state.doc.forEach((node, offset) => {
      if (found === null && node.attrs.clientKey === clientKey) found = offset;
    });

    return found;
  }

  function applyAttrs(clientKey, attrs) {
    editor.commands.command(({ tr, dispatch }) => {
      const pos = findPos(clientKey);
      if (pos === null) return false;
      if (!dispatch) return true;

      const node = tr.doc.nodeAt(pos);
      tr.setNodeMarkup(pos, undefined, { ...node.attrs, ...attrs });
      dispatch(tr);

      return true;
    });
  }

  function currentSectionIdFor(clientKey) {
    const nodes = flatten(editor.state.doc);
    const index = nodes.findIndex((n) => n.clientKey === clientKey);

    for (let i = index; i >= 0; i -= 1) {
      if (nodes[i].type === 'chapterHeading') return nodes[i].sectionId;
    }

    return null;
  }

  function reconcile() {
    const next = flatten(editor.state.doc);
    const prevByKey = new Map(previous.map((n) => [n.clientKey, n]));

    next.forEach((node) => {
      const prev = prevByKey.get(node.clientKey);
      const titleChanged = !prev || prev.title !== node.title;
      if (!titleChanged) return;

      const hasRealId = node.type === 'chapterHeading' ? Boolean(node.sectionId) : Boolean(node.lectureId);

      if (!hasRealId) {
        if (!node.title.trim()) return; // still an empty placeholder line, nothing to create yet

        queue.debounce(node.clientKey, async () => {
          if (node.type === 'chapterHeading') {
            const section = await api.createSection(node.title);
            applyAttrs(node.clientKey, { sectionId: section.id });
          } else {
            const sectionId = currentSectionIdFor(node.clientKey);
            if (!sectionId) return; // parent chapter not resolved yet; will retry on the next keystroke
            const lecture = await api.createLecture(sectionId, node.title);
            applyAttrs(node.clientKey, {
              lectureId: lecture.id,
              contentType: lecture.content_type,
              editUrl: buildEditUrl(lecture.id),
            });
          }
        }, 400);
      } else {
        queue.debounce(node.clientKey, async () => {
          if (node.type === 'chapterHeading') {
            await api.renameSection(node.sectionId, node.title);
          } else {
            await api.renameLecture(node.lectureId, node.title);
          }
        }, 800);
      }
    });

    previous = next;
  }

  return {
    reconcile, seed, applyAttrs, currentSectionIdFor,
  };
}
