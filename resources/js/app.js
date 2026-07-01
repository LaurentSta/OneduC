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

function loadModuleBuilderEditorsWhenNeeded() {
  if (!document.querySelector('[data-block-editor]')) return;
  import('./formateur-module-builder-editor.jsx').then(({ mountModuleBuilderEditors }) => mountModuleBuilderEditors());
}

function loadAkeneHeroWhenNeeded() {
  if (!document.querySelector('[data-akene-hero]')) return;
  import('./frontend/akene-hero.js').then(({ initAkeneHero }) => initAkeneHero());
}

function initPublicPageTransitions() {
  const body = document.body;

  if (!body || !body.classList.contains('oneduc-public')) return;

  const reducedMotionQuery = window.matchMedia('(prefers-reduced-motion: reduce)');
  let isLeaving = false;

  const clearLeavingState = () => {
    isLeaving = false;
    body.classList.remove('page-is-leaving');
  };

  window.addEventListener('pageshow', clearLeavingState);

  document.addEventListener('click', event => {
    const link = event.target.closest('a[href]');

    if (!link || event.defaultPrevented || isLeaving) return;
    if (link.target && link.target !== '_self') return;
    if (link.hasAttribute('download')) return;
    if (event.metaKey || event.ctrlKey || event.shiftKey || event.altKey || event.button !== 0) return;

    const href = link.getAttribute('href');

    if (!href || href.startsWith('#') || href.startsWith('mailto:') || href.startsWith('tel:') || href.startsWith('javascript:')) {
      return;
    }

    const destination = new URL(link.href, window.location.href);

    if (destination.origin !== window.location.origin) return;

    const currentPath = `${window.location.pathname}${window.location.search}`;
    const nextPath = `${destination.pathname}${destination.search}`;

    if (currentPath === nextPath && destination.hash) return;

    if (reducedMotionQuery.matches) return;

    event.preventDefault();
    isLeaving = true;
    body.classList.add('page-is-leaving');

    window.setTimeout(() => {
      window.location.assign(destination.href);
    }, 180);
  });
}

if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', () => {
    loadGroupModuleFlowWhenNeeded();
    loadStagiaireModuleFlowWhenNeeded();
    loadGroupLessonFlowWhenNeeded();
    loadParcoursBuilderWhenNeeded();
    loadGroupWhiteboardWhenNeeded();
    loadModuleBuilderEditorsWhenNeeded();
    loadAkeneHeroWhenNeeded();
    initPublicPageTransitions();
  }, { once: true });
} else {
  loadGroupModuleFlowWhenNeeded();
  loadStagiaireModuleFlowWhenNeeded();
  loadGroupLessonFlowWhenNeeded();
  loadParcoursBuilderWhenNeeded();
  loadGroupWhiteboardWhenNeeded();
  loadModuleBuilderEditorsWhenNeeded();
  loadAkeneHeroWhenNeeded();
  initPublicPageTransitions();
}

/*
  Add custom scripts here
*/
import.meta.glob([
  '../assets/img/**',
  // '../assets/json/**',
  '../assets/vendor/fonts/**/*.{eot,svg,ttf,woff,woff2}'
]);
