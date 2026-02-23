import '../css/app.css'; // ✅ Ajoute Tailwind

import './bootstrap';

/*
  Add custom scripts here
*/
import.meta.glob([
  '../assets/img/**',
  // '../assets/json/**',
  '../assets/vendor/fonts/**/*.{eot,svg,ttf,woff,woff2}'
]);
