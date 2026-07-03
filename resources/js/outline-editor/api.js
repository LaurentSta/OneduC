function csrfToken() {
  return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
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

export function createOutlineApi(basePath, moduleId) {
  return {
    async createSection(title) {
      const data = await jsonRequest(`${basePath}/${moduleId}/sections`, 'POST', { section_title: title || 'Chapitre' });
      return data.section;
    },
    async renameSection(sectionId, title) {
      const data = await jsonRequest(`${basePath}/sections/${sectionId}`, 'PUT', { section_title: title || 'Chapitre' });
      return data.section;
    },
    async destroySection(sectionId) {
      await jsonRequest(`${basePath}/sections/${sectionId}`, 'DELETE');
    },
    async reorderSections(sectionIds) {
      await jsonRequest(`${basePath}/${moduleId}/sections/reorder`, 'POST', { section_ids: sectionIds });
    },
    async createLecture(sectionId, title) {
      const data = await jsonRequest(`${basePath}/sections/${sectionId}/lectures`, 'POST', {
        lecture_title: title || 'Leçon',
        content_blocks: '[]',
      });
      return data.lecture;
    },
    async renameLecture(lectureId, title) {
      const data = await jsonRequest(`${basePath}/lectures/${lectureId}`, 'PUT', {
        lecture_title: title || 'Leçon',
        content_blocks: '[]',
      });
      return data.lecture;
    },
    async destroyLecture(lectureId) {
      await jsonRequest(`${basePath}/lectures/${lectureId}`, 'DELETE');
    },
    async reorderLectures(sectionId, lectureIds) {
      await jsonRequest(`${basePath}/sections/${sectionId}/lectures/reorder`, 'POST', { lecture_ids: lectureIds });
    },
    async moveLecture(lectureId, sectionId, position) {
      const data = await jsonRequest(`${basePath}/lectures/${lectureId}/move`, 'POST', {
        section_id: sectionId,
        position,
      });
      return data.lecture;
    },
    async promoteLecture(lectureId) {
      const data = await jsonRequest(`${basePath}/lectures/${lectureId}/promote`, 'POST');
      return data.section;
    },
  };
}
