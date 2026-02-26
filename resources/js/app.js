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

if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', () => {
    loadGroupModuleFlowWhenNeeded();
    loadStagiaireModuleFlowWhenNeeded();
  }, { once: true });
} else {
  loadGroupModuleFlowWhenNeeded();
  loadStagiaireModuleFlowWhenNeeded();
}

/*
  Add custom scripts here
*/
import.meta.glob([
  '../assets/img/**',
  // '../assets/json/**',
  '../assets/vendor/fonts/**/*.{eot,svg,ttf,woff,woff2}'
]);
