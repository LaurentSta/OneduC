let seq = 0;

export function nextClientKey(prefix = 'tmp') {
  seq += 1;
  return `${prefix}-${Date.now()}-${seq}`;
}
