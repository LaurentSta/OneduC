import '../css/app.css'; // ✅ Ajoute Tailwind

import './bootstrap';
import Alpine from 'alpinejs';
import collapse from '@alpinejs/collapse';

window.Alpine = Alpine;
Alpine.plugin(collapse);
Alpine.start();

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

function loadParcoursBuilderWhenNeeded() {
  if (!document.querySelector('[data-parcours-builder]')) return;
  import('./formateur-parcours-builder.jsx');
}

function loadGroupWhiteboardWhenNeeded() {
  if (!document.querySelector('[data-whiteboard-app]')) return;
  import('./group-whiteboard-excalidraw.jsx').then(({ mountGroupWhiteboardExcalidraw }) => mountGroupWhiteboardExcalidraw());
}

function ensureModuleBuilderEditorsLoaded() {
  return import('./formateur-module-builder-editor.jsx').then(({ mountModuleBuilderEditors }) => {
    mountModuleBuilderEditors();
  });
}
window.oneducMountBlockEditors = ensureModuleBuilderEditorsLoaded;

function loadModuleBuilderEditorsWhenNeeded() {
  if (!document.querySelector('[data-block-editor]')) return;
  ensureModuleBuilderEditorsLoaded();
}

function loadOutlineEditorWhenNeeded() {
  if (!document.querySelector('[data-outline-editor]')) return;
  import('./outline-editor/OutlineEditor.jsx').then(({ mountOutlineEditor }) => mountOutlineEditor());
}

function loadAkeneHeroWhenNeeded() {
  if (!document.querySelector('[data-akene-hero]')) return;
  import('./frontend/akene-hero.js').then(({ initAkeneHero }) => initAkeneHero());
}

if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', () => {
    loadGroupModuleFlowWhenNeeded();
    loadStagiaireModuleFlowWhenNeeded();
    loadGroupLessonFlowWhenNeeded();
    loadParcoursBuilderWhenNeeded();
    loadGroupWhiteboardWhenNeeded();
    loadModuleBuilderEditorsWhenNeeded();
    loadOutlineEditorWhenNeeded();
    loadAkeneHeroWhenNeeded();
  }, { once: true });
} else {
  loadGroupModuleFlowWhenNeeded();
  loadStagiaireModuleFlowWhenNeeded();
  loadGroupLessonFlowWhenNeeded();
  loadParcoursBuilderWhenNeeded();
  loadGroupWhiteboardWhenNeeded();
  loadModuleBuilderEditorsWhenNeeded();
  loadOutlineEditorWhenNeeded();
  loadAkeneHeroWhenNeeded();
}

/*
  Add custom scripts here
*/
import.meta.glob([
  '../assets/img/**',
  // '../assets/json/**',
  '../assets/vendor/fonts/**/*.{eot,svg,ttf,woff,woff2}'
]);
