import '../css/app.css'; // ✅ Ajoute Tailwind

import './bootstrap';

function loadGroupModuleFlowWhenNeeded() {
  if (!document.querySelector('[data-group-module-flow]')) return;
  import('./formateur-group-module-flow.jsx');
}

function loadStagiaireModuleFlowWhenNeeded() {
  if (!document.querySelector('[data-stagiaire-module-flow]')) return;
  import('./stagiaire-module-path-flow.jsx');
}

function loadGroupLessonFlowWhenNeeded() {
  if (!document.querySelector('[data-group-lesson-flow]')) return;
  import('./formateur-group-lesson-flow.jsx');
}

function loadGroupWhiteboardWhenNeeded() {
  if (!document.querySelector('[data-whiteboard-app]')) return;
  import('./group-whiteboard').then(({ mountGroupWhiteboard }) => mountGroupWhiteboard());
}

if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', () => {
    loadGroupModuleFlowWhenNeeded();
    loadStagiaireModuleFlowWhenNeeded();
    loadGroupLessonFlowWhenNeeded();
    loadGroupWhiteboardWhenNeeded();
  }, { once: true });
} else {
  loadGroupModuleFlowWhenNeeded();
  loadStagiaireModuleFlowWhenNeeded();
  loadGroupLessonFlowWhenNeeded();
  loadGroupWhiteboardWhenNeeded();
}

/*
  Add custom scripts here
*/
import.meta.glob([
  '../assets/img/**',
  // '../assets/json/**',
  '../assets/vendor/fonts/**/*.{eot,svg,ttf,woff,woff2}'
]);
