function csrfToken() {
  return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
}

function escapeHtml(value) {
  return String(value).replace(/[&<>"']/g, (ch) => ({
    '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;',
  }[ch]));
}

async function jsonRequest(url, method, body) {
  const response = await fetch(url, {
    method,
    headers: {
      'Content-Type': 'application/json',
      Accept: 'application/json',
      'X-CSRF-TOKEN': csrfToken(),
    },
    body: body !== undefined ? JSON.stringify(body) : undefined,
  });

  if (!response.ok) {
    throw new Error(`Request to ${url} failed with status ${response.status}`);
  }

  if (response.status === 204) return null;

  return response.json();
}

function urlFor(basePath, kind, ids = {}) {
  switch (kind) {
    case 'sections.store':
      return `${basePath}/${ids.moduleId}/sections`;
    case 'sections.update':
    case 'sections.destroy':
      return `${basePath}/sections/${ids.sectionId}`;
    case 'sections.reorder':
      return `${basePath}/${ids.moduleId}/sections/reorder`;
    case 'lectures.store':
      return `${basePath}/sections/${ids.sectionId}/lectures`;
    case 'lectures.update':
    case 'lectures.destroy':
      return `${basePath}/lectures/${ids.lectureId}`;
    case 'lectures.reorder':
      return `${basePath}/sections/${ids.sectionId}/lectures/reorder`;
    default:
      throw new Error(`Unknown url kind: ${kind}`);
  }
}

function showPanel(lectureId) {
  document.querySelectorAll('.lecture-editor-panel').forEach((panel) => {
    panel.style.display = String(panel.dataset.lectureId) === String(lectureId) ? '' : 'none';
  });
}

function insertBlocksLecturePanel({ id, title, updateUrl, uploadUrl }) {
  const container = document.getElementById('lecture-editor-panels');
  if (!container) return;

  container.insertAdjacentHTML('beforeend', `
    <div class="lecture-editor-panel bg-white rounded-[20px] shadow-md p-6" data-lecture-id="${id}" style="display: none;">
      <div data-block-editor
           data-lecture-id="${id}"
           data-update-url="${updateUrl}"
           data-upload-url="${uploadUrl}"
           data-initial-title="${escapeHtml(title)}"
           data-initial-blocks="[]"></div>
    </div>
  `);

  if (window.oneducMountBlockEditors) window.oneducMountBlockEditors();
}

export function createModuleBuilderOutline({ moduleId, basePath, uploadUrl, sections }) {
  return {
    moduleId,
    basePath,
    uploadUrl,
    sections: sections || [],
    selectedLectureId: null,
    dragSectionIndex: null,
    dragLectureContext: null,
    newSectionTitle: '',
    newLectureTitleBySection: {},
    error: '',
    pendingDelete: { type: null, id: null, title: '', message: '' },

    init() {
      const firstLectureId = this.firstLectureId();
      if (firstLectureId) {
        this.selectLecture(firstLectureId);
      }
    },

    firstLectureId() {
      for (const section of this.sections) {
        if (section.lectures.length) return section.lectures[0].id;
      }
      return null;
    },

    selectLecture(lectureId) {
      this.selectedLectureId = lectureId;
      showPanel(lectureId);
    },

    onLectureSaved({ id, lecture_title: lectureTitle }) {
      for (const section of this.sections) {
        const lecture = section.lectures.find((l) => Number(l.id) === Number(id));
        if (lecture) {
          lecture.lecture_title = lectureTitle;
          return;
        }
      }
    },

    async addSection() {
      const title = this.newSectionTitle.trim();
      if (!title) return;

      try {
        const data = await jsonRequest(
          urlFor(this.basePath, 'sections.store', { moduleId: this.moduleId }),
          'POST',
          { section_title: title }
        );
        this.sections.push({ ...data.section, lectures: [] });
        this.newSectionTitle = '';
      } catch (e) {
        this.error = "Impossible d'ajouter le chapitre.";
      }
    },

    async renameSection(section) {
      const title = (section.section_title || '').trim();
      if (!title) return;

      try {
        await jsonRequest(
          urlFor(this.basePath, 'sections.update', { sectionId: section.id }),
          'PUT',
          { section_title: title }
        );
      } catch (e) {
        this.error = 'Impossible de renommer le chapitre.';
      }
    },

    async addLecture(section) {
      const title = (this.newLectureTitleBySection[section.id] || '').trim();
      if (!title) return;

      try {
        const data = await jsonRequest(
          urlFor(this.basePath, 'lectures.store', { sectionId: section.id }),
          'POST',
          { lecture_title: title, content_blocks: '[]' }
        );
        section.lectures.push(data.lecture);
        this.newLectureTitleBySection[section.id] = '';

        insertBlocksLecturePanel({
          id: data.lecture.id,
          title: data.lecture.lecture_title,
          updateUrl: urlFor(this.basePath, 'lectures.update', { lectureId: data.lecture.id }),
          uploadUrl: this.uploadUrl,
        });

        this.selectLecture(data.lecture.id);
      } catch (e) {
        this.error = "Impossible d'ajouter la leçon.";
      }
    },

    requestDeleteSection(section) {
      this.pendingDelete = {
        type: 'section',
        id: section.id,
        title: 'Supprimer ce chapitre ?',
        message: 'Toutes ses leçons seront également supprimées.',
      };
      this.$dispatch('open-modal', 'delete-confirm');
    },

    requestDeleteLecture(lecture) {
      this.pendingDelete = {
        type: 'lecture',
        id: lecture.id,
        title: 'Supprimer cette leçon ?',
        message: 'Cette action est irréversible.',
      };
      this.$dispatch('open-modal', 'delete-confirm');
    },

    async confirmPendingDelete() {
      const { type, id } = this.pendingDelete;
      if (!type || !id) return;

      try {
        if (type === 'section') {
          await jsonRequest(urlFor(this.basePath, 'sections.destroy', { sectionId: id }), 'DELETE');
          this.sections = this.sections.filter((s) => s.id !== id);
        } else {
          await jsonRequest(urlFor(this.basePath, 'lectures.destroy', { lectureId: id }), 'DELETE');
          for (const section of this.sections) {
            const index = section.lectures.findIndex((l) => l.id === id);
            if (index !== -1) {
              section.lectures.splice(index, 1);
              break;
            }
          }
          document.querySelector(`.lecture-editor-panel[data-lecture-id="${id}"]`)?.remove();
          if (this.selectedLectureId === id) {
            const nextId = this.firstLectureId();
            if (nextId) this.selectLecture(nextId);
            else this.selectedLectureId = null;
          }
        }

        this.$dispatch('close-modal', 'delete-confirm');
      } catch (e) {
        this.error = 'Suppression impossible.';
      }
    },

    dragSectionStart(index) {
      this.dragSectionIndex = index;
    },

    dragSectionOver(index) {
      // Native dragover default-prevented in the template to allow dropping.
    },

    async dropSection(index) {
      const from = this.dragSectionIndex;
      this.dragSectionIndex = null;
      if (from === null || from === index) return;

      const moved = this.sections.splice(from, 1)[0];
      this.sections.splice(index, 0, moved);

      try {
        await jsonRequest(
          urlFor(this.basePath, 'sections.reorder', { moduleId: this.moduleId }),
          'POST',
          { section_ids: this.sections.map((s) => s.id) }
        );
      } catch (e) {
        this.error = 'Impossible de réordonner les chapitres.';
      }
    },

    dragLectureStart(sectionIndex, lectureIndex) {
      this.dragLectureContext = { sectionIndex, lectureIndex };
    },

    async dropLecture(sectionIndex, lectureIndex) {
      const from = this.dragLectureContext;
      this.dragLectureContext = null;
      if (!from || from.sectionIndex !== sectionIndex || from.lectureIndex === lectureIndex) return;

      const section = this.sections[sectionIndex];
      const moved = section.lectures.splice(from.lectureIndex, 1)[0];
      section.lectures.splice(lectureIndex, 0, moved);

      try {
        await jsonRequest(
          urlFor(this.basePath, 'lectures.reorder', { sectionId: section.id }),
          'POST',
          { lecture_ids: section.lectures.map((l) => l.id) }
        );
      } catch (e) {
        this.error = 'Impossible de réordonner les leçons.';
      }
    },
  };
}
