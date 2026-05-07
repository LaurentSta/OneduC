import paths from './akene-paths.json';

const svgNamespace = 'http://www.w3.org/2000/svg';

function createSvgElement(name) {
  return document.createElementNS(svgNamespace, name);
}

function createPath(d) {
  const path = createSvgElement('path');
  path.setAttribute('d', d);
  return path;
}

function renderStaticLogo(root) {
  const svg = createSvgElement('svg');
  const group = createSvgElement('g');

  svg.setAttribute('viewBox', '0 0 262.96 262.96');
  svg.setAttribute('aria-hidden', 'true');
  svg.classList.add('oneduc-akene-svg');
  group.classList.add('oneduc-akene-live');

  paths.forEach(d => group.appendChild(createPath(d)));
  svg.appendChild(group);
  root.replaceChildren(svg);
}

function animateLogo(root) {
  const svg = createSvgElement('svg');
  const ghostGroup = createSvgElement('g');
  const liveGroup = createSvgElement('g');

  svg.setAttribute('viewBox', '0 0 262.96 262.96');
  svg.setAttribute('aria-hidden', 'true');
  svg.classList.add('oneduc-akene-svg');
  ghostGroup.classList.add('oneduc-akene-ghost');
  liveGroup.classList.add('oneduc-akene-live');

  paths.forEach(d => {
    ghostGroup.appendChild(createPath(d));

    const path = createPath(d);
    path.style.opacity = '0';
    path.style.transformBox = 'fill-box';
    path.style.transformOrigin = 'center';
    liveGroup.appendChild(path);
  });

  svg.append(ghostGroup, liveGroup);
  root.replaceChildren(svg);

  const livePaths = Array.from(liveGroup.querySelectorAll('path'));
  const duration = 6200;
  const overlap = 105;
  const startedAt = performance.now();

  function frame(now) {
    const elapsed = now - startedAt;
    let finished = true;

    livePaths.forEach((path, index) => {
      const localStart = index * overlap;
      const localDuration = (duration / livePaths.length) * 3.5;
      const rawProgress = (elapsed - localStart) / localDuration;
      const progress = Math.max(0, Math.min(1, rawProgress));
      const eased = 1 - Math.pow(1 - progress, 2.25);

      path.style.opacity = String(eased);
      path.style.transform = `scale(${0.94 + eased * 0.06})`;

      if (progress < 1) {
        finished = false;
      }
    });

    if (!finished) {
      window.requestAnimationFrame(frame);
      return;
    }

    livePaths.forEach((path, index) => {
      path.animate(
        [
          { opacity: 1, transform: 'scale(1)' },
          { opacity: 1, transform: 'scale(1.012)' },
          { opacity: 1, transform: 'scale(1)' },
        ],
        {
          duration: 1800,
          delay: index * 22,
          easing: 'cubic-bezier(0.16, 1, 0.3, 1)',
          fill: 'forwards',
        }
      );
    });

    svg.classList.add('oneduc-akene-svg--settled');
  }

  window.requestAnimationFrame(frame);
}

export function initAkeneHero() {
  const roots = document.querySelectorAll('[data-akene-hero]');
  const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  roots.forEach(root => {
    if (root.dataset.akeneMounted === '1') return;
    root.dataset.akeneMounted = '1';

    if (reduceMotion) {
      renderStaticLogo(root);
      return;
    }

    animateLogo(root);
  });
}
