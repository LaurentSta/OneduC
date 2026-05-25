<script>
(function () {
  var sizes = { normal: '100%', large: '112.5%', xlarge: '125%' };
  function applyTextSize(size) {
    document.documentElement.style.fontSize = sizes[size] || '100%';
    document.querySelectorAll('[data-text-size]').forEach(function (btn) {
      var active = btn.dataset.textSize === size;
      btn.classList.toggle('bg-bleuone',     active);
      btn.classList.toggle('text-white',     active);
      btn.classList.toggle('border-bleuone', active);
    });
  }
  window.setTextSize = function (size) {
    try { localStorage.setItem('a11y-text-size', size); } catch (_) {}
    applyTextSize(size);
  };
  document.addEventListener('DOMContentLoaded', function () {
    var saved = 'normal';
    try { saved = localStorage.getItem('a11y-text-size') || 'normal'; } catch (_) {}
    applyTextSize(saved);
  });
})();

(function () {
  function applyDyslexicFont(enabled) {
    document.documentElement.classList.toggle('a11y-dyslexic-font', enabled);
    document.querySelectorAll('[data-dyslexic-toggle]').forEach(function (btn) {
      btn.setAttribute('aria-pressed', enabled ? 'true' : 'false');
      btn.classList.toggle('border-bleuone', enabled);
      btn.classList.toggle('bg-bleuone/5',   enabled);
    });
    document.querySelectorAll('[data-dyslexic-status]').forEach(function (s) {
      s.textContent = enabled ? 'On' : 'Off';
      s.classList.toggle('bg-bleuone',    enabled);
      s.classList.toggle('text-white',    enabled);
      s.classList.toggle('bg-slate-100',  !enabled);
      s.classList.toggle('text-slate-500', !enabled);
    });
  }
  window.toggleDyslexicFont = function () {
    var enabled = !document.documentElement.classList.contains('a11y-dyslexic-font');
    try { localStorage.setItem('a11y-dyslexic-font', enabled ? '1' : '0'); } catch (_) {}
    applyDyslexicFont(enabled);
  };
  document.addEventListener('DOMContentLoaded', function () {
    var enabled = false;
    try { enabled = localStorage.getItem('a11y-dyslexic-font') === '1'; } catch (_) {}
    applyDyslexicFont(enabled);
  });
})();

(function () {
  var classes = ['a11y-line-height-large', 'a11y-line-height-xlarge'];
  function applyLineHeight(size) {
    document.documentElement.classList.remove.apply(document.documentElement.classList, classes);
    if (size !== 'normal') document.documentElement.classList.add('a11y-line-height-' + size);
    document.querySelectorAll('[data-line-height]').forEach(function (btn) {
      var active = btn.dataset.lineHeight === size;
      btn.classList.toggle('bg-bleuone',     active);
      btn.classList.toggle('text-white',     active);
      btn.classList.toggle('border-bleuone', active);
    });
  }
  window.setLineHeight = function (size) {
    try { localStorage.setItem('a11y-line-height', size); } catch (_) {}
    applyLineHeight(size);
  };
  document.addEventListener('DOMContentLoaded', function () {
    var saved = 'normal';
    try { saved = localStorage.getItem('a11y-line-height') || 'normal'; } catch (_) {}
    applyLineHeight(saved);
  });
})();

(function () {
  var classes = ['a11y-letter-spacing-large', 'a11y-letter-spacing-xlarge'];
  function applyLetterSpacing(size) {
    document.documentElement.classList.remove.apply(document.documentElement.classList, classes);
    if (size !== 'normal') document.documentElement.classList.add('a11y-letter-spacing-' + size);
    document.querySelectorAll('[data-letter-spacing]').forEach(function (btn) {
      var active = btn.dataset.letterSpacing === size;
      btn.classList.toggle('bg-bleuone',     active);
      btn.classList.toggle('text-white',     active);
      btn.classList.toggle('border-bleuone', active);
    });
  }
  window.setLetterSpacing = function (size) {
    try { localStorage.setItem('a11y-letter-spacing', size); } catch (_) {}
    applyLetterSpacing(size);
  };
  document.addEventListener('DOMContentLoaded', function () {
    var saved = 'normal';
    try { saved = localStorage.getItem('a11y-letter-spacing') || 'normal'; } catch (_) {}
    applyLetterSpacing(saved);
  });
})();

(function () {
  var classes = ['a11y-contrast-dark', 'a11y-contrast-sepia', 'a11y-contrast-high'];
  function applyContrast(mode) {
    document.documentElement.classList.remove.apply(document.documentElement.classList, classes);
    if (mode !== 'normal') document.documentElement.classList.add('a11y-contrast-' + mode);
    document.querySelectorAll('[data-contrast]').forEach(function (btn) {
      var active = btn.dataset.contrast === mode;
      btn.classList.toggle('bg-bleuone',     active);
      btn.classList.toggle('text-white',     active);
      btn.classList.toggle('border-bleuone', active);
    });
  }
  window.setContrast = function (mode) {
    try { localStorage.setItem('a11y-contrast', mode); } catch (_) {}
    applyContrast(mode);
  };
  document.addEventListener('DOMContentLoaded', function () {
    var saved = 'normal';
    try { saved = localStorage.getItem('a11y-contrast') || 'normal'; } catch (_) {}
    applyContrast(saved);
  });
})();

window.resetA11y = function () {
  ['a11y-text-size', 'a11y-dyslexic-font', 'a11y-line-height', 'a11y-letter-spacing', 'a11y-contrast'].forEach(function (k) {
    try { localStorage.removeItem(k); } catch (_) {}
  });
  window.setTextSize('normal');
  document.documentElement.classList.remove('a11y-dyslexic-font');
  document.querySelectorAll('[data-dyslexic-toggle]').forEach(function (btn) { btn.setAttribute('aria-pressed', 'false'); btn.classList.remove('border-bleuone', 'bg-bleuone/5'); });
  document.querySelectorAll('[data-dyslexic-status]').forEach(function (s) { s.textContent = 'Off'; s.className = 'rounded-full bg-slate-100 px-2 py-0.5 text-xs font-bold text-slate-500'; });
  window.setLineHeight('normal');
  window.setLetterSpacing('normal');
  window.setContrast('normal');
};
</script>
