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

export function resolveOutlineEndpoint(template, fallback, params = {}) {
  let url = template || fallback;

  Object.entries(params).forEach(([name, value]) => {
    url = url.replaceAll(`__${name.toUpperCase()}__`, encodeURIComponent(String(value)));
  });

  return url;
}

export function createOutlineApi(basePath, moduleId, endpoints = {}) {
  return {
    async createSection(title) {
      const url = resolveOutlineEndpoint(
        endpoints.createSection,
        `${basePath}/${moduleId}/sections`,
        { module: moduleId },
      );
      const data = await jsonRequest(url, 'POST', { section_title: title || 'Chapitre' });
      return data.section;
    },
    async renameSection(sectionId, title) {
      const url = resolveOutlineEndpoint(
        endpoints.section,
        `${basePath}/sections/${sectionId}`,
        { section: sectionId },
      );
      const data = await jsonRequest(url, 'PUT', { section_title: title || 'Chapitre' });
      return data.section;
    },
    async destroySection(sectionId) {
      const url = resolveOutlineEndpoint(
        endpoints.section,
        `${basePath}/sections/${sectionId}`,
        { section: sectionId },
      );
      await jsonRequest(url, 'DELETE');
    },
    async reorderSections(sectionIds) {
      const url = resolveOutlineEndpoint(
        endpoints.reorderSections,
        `${basePath}/${moduleId}/sections/reorder`,
        { module: moduleId },
      );
      await jsonRequest(url, 'POST', { section_ids: sectionIds });
    },
    async createLecture(sectionId, title) {
      const url = resolveOutlineEndpoint(
        endpoints.createLecture,
        `${basePath}/sections/${sectionId}/lectures`,
        { section: sectionId },
      );
      const data = await jsonRequest(url, 'POST', {
        lecture_title: title || 'Leçon',
      });
      return data.lecture;
    },
    async renameLecture(lectureId, title) {
      const url = resolveOutlineEndpoint(
        endpoints.lecture,
        `${basePath}/lectures/${lectureId}`,
        { lecture: lectureId },
      );
      const data = await jsonRequest(url, 'PUT', {
        lecture_title: title || 'Leçon',
      });
      return data.lecture;
    },
    async destroyLecture(lectureId) {
      const url = resolveOutlineEndpoint(
        endpoints.lecture,
        `${basePath}/lectures/${lectureId}`,
        { lecture: lectureId },
      );
      await jsonRequest(url, 'DELETE');
    },
    async duplicateLecture(lectureId) {
      const url = resolveOutlineEndpoint(
        endpoints.duplicateLecture,
        `${basePath}/lectures/${lectureId}/duplicate`,
        { lecture: lectureId },
      );
      const data = await jsonRequest(url, 'POST');
      return data.lecture;
    },
    async reorderLectures(sectionId, lectureIds) {
      const url = resolveOutlineEndpoint(
        endpoints.reorderLectures,
        `${basePath}/sections/${sectionId}/lectures/reorder`,
        { section: sectionId },
      );
      await jsonRequest(url, 'POST', { lecture_ids: lectureIds });
    },
    async moveLecture(lectureId, sectionId, position) {
      const url = resolveOutlineEndpoint(
        endpoints.moveLecture,
        `${basePath}/lectures/${lectureId}/move`,
        { lecture: lectureId },
      );
      const data = await jsonRequest(url, 'POST', {
        section_id: sectionId,
        position,
      });
      return data.lecture;
    },
    async promoteLecture(lectureId) {
      const url = resolveOutlineEndpoint(
        endpoints.promoteLecture,
        `${basePath}/lectures/${lectureId}/promote`,
        { lecture: lectureId },
      );
      const data = await jsonRequest(url, 'POST');
      return data.section;
    },
  };
}
