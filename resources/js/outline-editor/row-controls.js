// Shared UI bits used by both chapterHeading and lessonItem node views: the drag
// handle icon, the "..." options menu icon, and the "only one thing open/highlighted
// at a time" trackers (a single instance shared across every row on the page).

let openMenuPanel = null;
let openMenuRow = null;

export function closeOpenMenu() {
  if (openMenuPanel) {
    openMenuPanel.classList.add('hidden');
    openMenuPanel = null;
  }
  if (openMenuRow) {
    openMenuRow.classList.remove('z-20');
    openMenuRow = null;
  }
}

document.addEventListener('mousedown', (event) => {
  if (openMenuPanel && !openMenuPanel.contains(event.target)) closeOpenMenu();
});

export function openMenu(panel, row) {
  closeOpenMenu();
  panel.classList.remove('hidden');
  row.classList.add('z-20');
  openMenuPanel = panel;
  openMenuRow = row;
}

export function isMenuOpen(panel) {
  return openMenuPanel === panel;
}

let highlightedRow = null;

export function setHighlightedRow(row) {
  if (highlightedRow === row) return;
  if (highlightedRow) highlightedRow.classList.remove('border-t-2', 'border-orangeone', 'rounded-t-none');
  highlightedRow = row;
  // rounded-t-none squares off the row's own top corners so the indicator reads as a
  // clean straight line instead of curving in at both ends.
  if (highlightedRow) highlightedRow.classList.add('border-t-2', 'border-orangeone', 'rounded-t-none');
}

export function createHandleIcon() {
  const svg = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
  svg.setAttribute('viewBox', '0 0 20 20');
  svg.setAttribute('fill', 'none');
  svg.setAttribute('aria-hidden', 'true');
  svg.setAttribute('class', 'h-4 w-4');
  svg.innerHTML = '<g stroke="currentColor" stroke-width="1.6" stroke-linecap="round">'
    + '<line x1="4" y1="6" x2="16" y2="6"/>'
    + '<line x1="4" y1="10" x2="16" y2="10"/>'
    + '<line x1="4" y1="14" x2="16" y2="14"/>'
    + '</g>';

  return svg;
}

export function createDotsIcon() {
  const svg = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
  svg.setAttribute('viewBox', '0 0 20 20');
  svg.setAttribute('fill', 'currentColor');
  svg.setAttribute('aria-hidden', 'true');
  svg.setAttribute('class', 'h-4 w-4');
  svg.innerHTML = '<circle cx="4" cy="10" r="1.5"/><circle cx="10" cy="10" r="1.5"/><circle cx="16" cy="10" r="1.5"/>';

  return svg;
}

export function createDuplicateIcon() {
  const svg = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
  svg.setAttribute('viewBox', '0 0 20 20');
  svg.setAttribute('fill', 'none');
  svg.setAttribute('aria-hidden', 'true');
  svg.setAttribute('class', 'h-4 w-4');
  svg.innerHTML = '<g stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">'
    + '<rect x="7" y="7" width="9" height="9" rx="1.5"/>'
    + '<path d="M13 7V5.5A1.5 1.5 0 0 0 11.5 4H5.5A1.5 1.5 0 0 0 4 5.5v6A1.5 1.5 0 0 0 5.5 13H7"/>'
    + '</g>';

  return svg;
}

export function createMenuItem({ icon, label, className, onClick }) {
  const button = document.createElement('button');
  button.type = 'button';
  button.className = `flex w-full items-center gap-2 px-3 py-2 text-left text-xs font-semibold ${className}`;

  const iconWrapper = document.createElement('span');
  iconWrapper.className = 'shrink-0';
  iconWrapper.appendChild(icon);
  button.appendChild(iconWrapper);

  const text = document.createElement('span');
  text.textContent = label;
  button.appendChild(text);

  button.addEventListener('mousedown', onClick);

  return button;
}

export function createTrashIcon() {
  const svg = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
  svg.setAttribute('viewBox', '0 0 20 20');
  svg.setAttribute('fill', 'none');
  svg.setAttribute('aria-hidden', 'true');
  svg.setAttribute('class', 'h-4 w-4');
  svg.innerHTML = '<g stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">'
    + '<path d="M4.5 6h11"/>'
    + '<path d="M8.5 6V4.5h3V6"/>'
    + '<path d="M6 6l.6 9a1.5 1.5 0 0 0 1.5 1.4h3.8a1.5 1.5 0 0 0 1.5-1.4l.6-9"/>'
    + '<path d="M8.5 9v4.5"/>'
    + '<path d="M11.5 9v4.5"/>'
    + '</g>';

  return svg;
}
