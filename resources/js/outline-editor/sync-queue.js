function dispatchStatus(status) {
  window.dispatchEvent(new CustomEvent('outline:sync-status', { detail: { status } }));
}

export function createSyncQueue() {
  let chain = Promise.resolve();
  const timers = new Map();

  function enqueue(task) {
    dispatchStatus('saving');

    chain = chain.then(task).then(() => {
      dispatchStatus('saved');
    }).catch((error) => {
      // eslint-disable-next-line no-console
      console.error('[outline-editor] sync failed', error);
      dispatchStatus('error');
    });

    return chain;
  }

  function debounce(key, task, delay = 800) {
    if (timers.has(key)) clearTimeout(timers.get(key));

    const id = setTimeout(() => {
      timers.delete(key);
      enqueue(task);
    }, delay);

    timers.set(key, id);
  }

  return { enqueue, debounce };
}
