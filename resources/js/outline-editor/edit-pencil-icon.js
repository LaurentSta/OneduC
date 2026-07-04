export function createEditPencilIcon(className) {
  const svg = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
  svg.setAttribute('viewBox', '0 0 24 24');
  svg.setAttribute('fill', 'none');
  svg.setAttribute('aria-hidden', 'true');
  svg.setAttribute('class', className);
  svg.innerHTML = '<g stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2">'
    + '<path d="M7 17v-4l10 -10l4 4l-10 10h-4"/>'
    + '<path d="M3 21h18"/>'
    + '<path d="M14 6l4 4"/>'
    + '</g>';

  return svg;
}
