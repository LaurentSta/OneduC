function escapeHtml(value) {
  return String(value ?? '')
    .replaceAll('&', '&amp;')
    .replaceAll('<', '&lt;')
    .replaceAll('>', '&gt;')
    .replaceAll('"', '&quot;')
    .replaceAll("'", '&#039;');
}

function clamp(value, min, max) {
  return Math.min(max, Math.max(min, value));
}

function uid() {
  if (window.crypto?.randomUUID) {
    return window.crypto.randomUUID();
  }

  return `wb-${Date.now()}-${Math.random().toString(16).slice(2)}`;
}

function debounce(fn, delay = 350) {
  let timer = null;

  return function debounced(...args) {
    window.clearTimeout(timer);
    timer = window.setTimeout(() => fn.apply(this, args), delay);
  };
}

function getRelativePoint(event, element) {
  const rect = element.getBoundingClientRect();

  return {
    x: event.clientX - rect.left + element.scrollLeft,
    y: event.clientY - rect.top + element.scrollTop,
  };
}

function normalizeItem(raw) {
  return {
    id: raw.id ?? null,
    client_uuid: raw.client_uuid,
    type: raw.type,
    x: Number(raw.x ?? 0),
    y: Number(raw.y ?? 0),
    width: Number(raw.width ?? 240),
    height: Number(raw.height ?? 160),
    rotation: Number(raw.rotation ?? 0),
    z_index: Number(raw.z_index ?? 0),
    payload: raw.payload ?? {},
    style: raw.style ?? {},
  };
}

function sortItems(items) {
  return [...items].sort((a, b) => {
    if (a.z_index !== b.z_index) return a.z_index - b.z_index;
    return String(a.client_uuid).localeCompare(String(b.client_uuid));
  });
}

function pointsToPath(points) {
  if (!Array.isArray(points) || points.length < 2) {
    return '';
  }

  let path = `M ${points[0]} ${points[1]}`;
  for (let i = 2; i < points.length; i += 2) {
    path += ` L ${points[i]} ${points[i + 1]}`;
  }

  return path;
}

function createDefaultItem(tool, point, zIndex) {
  const base = {
    id: null,
    client_uuid: uid(),
    x: point.x,
    y: point.y,
    width: 240,
    height: 160,
    rotation: 0,
    z_index: zIndex,
    payload: {},
    style: {},
  };

  if (tool === 'note') {
    return {
      ...base,
      type: 'note',
      width: 260,
      height: 180,
      payload: {
        content: 'Nouvelle note',
      },
      style: {
        fill: '#FDE68A',
        textColor: '#172033',
      },
    };
  }

  if (tool === 'text') {
    return {
      ...base,
      type: 'text',
      width: 320,
      height: 120,
      payload: {
        content: 'Nouveau texte',
      },
      style: {
        textColor: '#0F172A',
      },
    };
  }

  if (tool === 'rectangle') {
    return {
      ...base,
      type: 'rectangle',
      width: 280,
      height: 160,
      style: {
        fill: '#DBEAFE',
        stroke: '#1D4ED8',
      },
    };
  }

  return {
    ...base,
    type: 'circle',
    width: 190,
    height: 190,
    style: {
      fill: '#DCFCE7',
      stroke: '#15803D',
    },
  };
}

class GroupWhiteboardApp {
  constructor(root, config) {
    this.root = root;
    this.config = config;
    this.snapshot = config.snapshot ?? { items: [], settings: {} };
    this.permissions = config.permissions ?? {};
    this.routes = config.routes ?? {};
    this.stageWidth = Number(this.snapshot.settings?.stage?.width ?? 1600);
    this.stageHeight = Number(this.snapshot.settings?.stage?.height ?? 900);
    this.pollInterval = Number(config.poll_interval_ms ?? 2000);
    this.items = sortItems((this.snapshot.items ?? []).map(normalizeItem));
    this.boardVersion = Number(this.snapshot.version ?? 0);
    this.selectedUuid = this.items[0]?.client_uuid ?? null;
    this.activeTool = 'select';
    this.isDragging = false;
    this.dragState = null;
    this.isDrawing = false;
    this.draftStroke = null;
    this.isSaving = false;
    this.syncPaused = false;

    this.debouncedSaveSelected = debounce(() => {
      const item = this.getSelectedItem();
      if (item) this.saveItem(item);
    }, 450);
  }

  init() {
    this.render();
    this.bindEvents();
    this.startPolling();
  }

  bindEvents() {
    this.root.addEventListener('click', (event) => this.handleClick(event));
    this.root.addEventListener('input', (event) => this.handleInput(event));
    this.root.addEventListener('change', (event) => this.handleChange(event));
    this.root.addEventListener('pointerdown', (event) => this.handlePointerDown(event));

    window.addEventListener('pointermove', (event) => this.handlePointerMove(event));
    window.addEventListener('pointerup', () => this.handlePointerUp());
  }

  startPolling() {
    if (!this.routes.snapshot) return;

    window.setInterval(() => {
      if (this.syncPaused || this.isDragging || this.isDrawing || this.isSaving) return;
      this.refreshSnapshot();
    }, this.pollInterval);
  }

  async refreshSnapshot() {
    const response = await fetch(this.routes.snapshot, {
      headers: {
        Accept: 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
      },
    });

    if (!response.ok) {
      return;
    }

    const data = await response.json();
    const nextVersion = Number(data.version ?? 0);

    if (nextVersion === this.boardVersion) {
      this.setStatus('Synchronise');
      return;
    }

    this.boardVersion = nextVersion;
    this.items = sortItems((data.items ?? []).map(normalizeItem));

    if (!this.items.some((item) => item.client_uuid === this.selectedUuid)) {
      this.selectedUuid = null;
    }

    this.render();
    this.setStatus('Mise a jour recue');
  }

  getSelectedItem() {
    return this.items.find((item) => item.client_uuid === this.selectedUuid) ?? null;
  }

  handleClick(event) {
    const toolButton = event.target.closest('[data-whiteboard-tool]');
    if (toolButton) {
      this.activeTool = toolButton.getAttribute('data-whiteboard-tool');
      this.render();
      return;
    }

    const selectAction = event.target.closest('[data-whiteboard-action]');
    if (selectAction) {
      const action = selectAction.getAttribute('data-whiteboard-action');

      if (action === 'delete-selected') {
        this.deleteSelected();
      }

      if (action === 'bring-front') {
        this.bringSelectedToFront();
      }

      if (action === 'clear-board') {
        this.clearBoard();
      }

      return;
    }

    const elementNode = event.target.closest('[data-whiteboard-item]');
    if (elementNode) {
      this.selectedUuid = elementNode.getAttribute('data-whiteboard-item');
      this.render();
      return;
    }

    const stageNode = event.target.closest('[data-whiteboard-stage]');
    if (!stageNode) return;

    if (this.activeTool === 'select') {
      this.selectedUuid = null;
      this.render();
      return;
    }

    if (this.activeTool === 'pen') return;

    const point = getRelativePoint(event, stageNode);
    const nextItem = createDefaultItem(this.activeTool, point, this.nextZIndex());
    nextItem.x = clamp(nextItem.x - nextItem.width / 2, 0, this.stageWidth - nextItem.width);
    nextItem.y = clamp(nextItem.y - nextItem.height / 2, 0, this.stageHeight - nextItem.height);

    this.items = sortItems([...this.items, nextItem]);
    this.selectedUuid = nextItem.client_uuid;
    this.render();
    this.saveItem(nextItem);

    if (this.activeTool !== 'select') {
      this.activeTool = 'select';
    }
  }

  handleInput(event) {
    const field = event.target.closest('[data-whiteboard-field]');
    if (!field) return;

    const item = this.getSelectedItem();
    if (!item) return;

    const key = field.getAttribute('data-whiteboard-field');
    const value = field.value;

    if (key === 'content') {
      item.payload = {
        ...item.payload,
        content: value,
      };
    }

    if (key === 'fill') {
      item.style = {
        ...item.style,
        fill: value,
      };
    }

    if (key === 'stroke') {
      item.style = {
        ...item.style,
        stroke: value,
      };
    }

    if (key === 'textColor') {
      item.style = {
        ...item.style,
        textColor: value,
      };
    }

    this.render();
    this.debouncedSaveSelected();
  }

  handleChange(event) {
    const field = event.target.closest('[data-whiteboard-field]');
    if (!field) return;

    const item = this.getSelectedItem();
    if (item) {
      this.saveItem(item);
    }
  }

  handlePointerDown(event) {
    const stageNode = this.root.querySelector('[data-whiteboard-stage]');
    if (!stageNode) return;

    const itemNode = event.target.closest('[data-whiteboard-item]');
    if (itemNode && this.activeTool === 'select') {
      const uuid = itemNode.getAttribute('data-whiteboard-item');
      const item = this.items.find((candidate) => candidate.client_uuid === uuid);
      if (!item) return;

      this.selectedUuid = uuid;
      const point = getRelativePoint(event, stageNode);
      this.isDragging = true;
      this.dragState = {
        uuid,
        offsetX: point.x - item.x,
        offsetY: point.y - item.y,
      };
      this.syncPaused = true;
      this.render();
      return;
    }

    if (this.activeTool !== 'pen') return;
    if (itemNode) return;

    const point = getRelativePoint(event, stageNode);
    this.isDrawing = true;
    this.syncPaused = true;
    this.draftStroke = {
      points: [point.x, point.y],
    };
    this.render();
  }

  handlePointerMove(event) {
    const stageNode = this.root.querySelector('[data-whiteboard-stage]');
    if (!stageNode) return;

    if (this.isDragging && this.dragState) {
      const point = getRelativePoint(event, stageNode);
      const item = this.items.find((candidate) => candidate.client_uuid === this.dragState.uuid);
      if (!item) return;

      item.x = clamp(point.x - this.dragState.offsetX, 0, this.stageWidth - item.width);
      item.y = clamp(point.y - this.dragState.offsetY, 0, this.stageHeight - item.height);
      this.render();
      return;
    }

    if (this.isDrawing && this.draftStroke) {
      const point = getRelativePoint(event, stageNode);
      this.draftStroke.points.push(point.x, point.y);
      this.render();
    }
  }

  handlePointerUp() {
    if (this.isDragging && this.dragState) {
      const item = this.items.find((candidate) => candidate.client_uuid === this.dragState.uuid);
      this.isDragging = false;
      this.dragState = null;
      this.syncPaused = false;

      if (item) {
        this.saveItem(item);
      }

      return;
    }

    if (!this.isDrawing || !this.draftStroke) return;

    this.isDrawing = false;
    this.syncPaused = false;

    if (this.draftStroke.points.length < 4) {
      this.draftStroke = null;
      this.render();
      return;
    }

    const xs = [];
    const ys = [];
    for (let i = 0; i < this.draftStroke.points.length; i += 2) {
      xs.push(this.draftStroke.points[i]);
      ys.push(this.draftStroke.points[i + 1]);
    }

    const minX = Math.min(...xs);
    const minY = Math.min(...ys);
    const maxX = Math.max(...xs);
    const maxY = Math.max(...ys);

    const item = {
      id: null,
      client_uuid: uid(),
      type: 'stroke',
      x: minX,
      y: minY,
      width: Math.max(4, maxX - minX),
      height: Math.max(4, maxY - minY),
      rotation: 0,
      z_index: this.nextZIndex(),
      payload: {
        points: this.draftStroke.points.map((value, index) => {
          if (index % 2 === 0) return value - minX;
          return value - minY;
        }),
      },
      style: {
        stroke: '#0F172A',
      },
    };

    this.draftStroke = null;
    this.items = sortItems([...this.items, item]);
    this.selectedUuid = item.client_uuid;
    this.render();
    this.saveItem(item);
  }

  nextZIndex() {
    return this.items.reduce((max, item) => Math.max(max, Number(item.z_index ?? 0)), 0) + 1;
  }

  bringSelectedToFront() {
    const item = this.getSelectedItem();
    if (!item) return;

    item.z_index = this.nextZIndex();
    this.items = sortItems(this.items);
    this.render();
    this.saveItem(item);
  }

  async saveItem(item) {
    if (!this.routes.upsert) return;

    this.isSaving = true;
    this.setStatus('Sauvegarde...');

    try {
      const response = await fetch(this.routes.upsert, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          Accept: 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? '',
          'X-Requested-With': 'XMLHttpRequest',
        },
        body: JSON.stringify({
          client_uuid: item.client_uuid,
          type: item.type,
          x: item.x,
          y: item.y,
          width: item.width,
          height: item.height,
          rotation: item.rotation,
          z_index: item.z_index,
          payload: item.payload ?? {},
          style: item.style ?? {},
        }),
      });

      if (!response.ok) {
        throw new Error('save_failed');
      }

      const data = await response.json();
      const index = this.items.findIndex((candidate) => candidate.client_uuid === item.client_uuid);

      if (index !== -1) {
        this.items[index] = normalizeItem(data.item);
        this.items = sortItems(this.items);
      }

      this.boardVersion = Number(data.version ?? this.boardVersion);
      this.render();
      this.setStatus('Sauvegarde effectuee');
    } catch (error) {
      this.setStatus('Erreur de sauvegarde');
    } finally {
      this.isSaving = false;
    }
  }

  async deleteSelected() {
    const item = this.getSelectedItem();
    if (!item || !this.routes.destroy_item_pattern) return;

    if (!item.id) {
      this.items = this.items.filter((candidate) => candidate.client_uuid !== item.client_uuid);
      this.selectedUuid = null;
      this.render();
      return;
    }

    const url = this.routes.destroy_item_pattern.replace('__ITEM__', String(item.id));

    const response = await fetch(url, {
      method: 'DELETE',
      headers: {
        Accept: 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? '',
        'X-Requested-With': 'XMLHttpRequest',
      },
    });

    if (!response.ok) {
      this.setStatus('Suppression impossible');
      return;
    }

    const data = await response.json();
    this.boardVersion = Number(data.version ?? this.boardVersion);
    this.items = this.items.filter((candidate) => candidate.client_uuid !== item.client_uuid);
    this.selectedUuid = null;
    this.render();
    this.setStatus('Element supprime');
  }

  async clearBoard() {
    if (!this.permissions.can_clear || !this.routes.clear) return;
    if (!window.confirm('Effacer tout le tableau blanc ?')) return;

    const response = await fetch(this.routes.clear, {
      method: 'POST',
      headers: {
        Accept: 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? '',
        'X-Requested-With': 'XMLHttpRequest',
      },
    });

    if (!response.ok) {
      this.setStatus('Effacement impossible');
      return;
    }

    const data = await response.json();
    this.boardVersion = Number(data.version ?? this.boardVersion);
    this.items = [];
    this.selectedUuid = null;
    this.render();
    this.setStatus('Tableau reinitialise');
  }

  setStatus(message) {
    const node = this.root.querySelector('[data-whiteboard-status]');
    if (node) {
      node.textContent = message;
    }
  }

  renderStageItem(item) {
    const selected = item.client_uuid === this.selectedUuid;
    const outline = selected ? 'ring-2 ring-orange-400 ring-offset-2' : '';
    const commonStyle = `
      left:${item.x}px;
      top:${item.y}px;
      width:${item.width}px;
      height:${item.height}px;
      z-index:${item.z_index};
      transform:rotate(${item.rotation}deg);
    `;

    if (item.type === 'note') {
      return `
        <div
          data-whiteboard-item="${escapeHtml(item.client_uuid)}"
          class="absolute cursor-move rounded-[24px] border border-amber-200 px-5 py-4 shadow-sm ${outline}"
          style="${commonStyle} background:${escapeHtml(item.style.fill ?? '#FDE68A')}; color:${escapeHtml(item.style.textColor ?? '#172033')};"
        >
          <div class="whiteboard-item-content">${escapeHtml(item.payload.content ?? '')}</div>
        </div>
      `;
    }

    if (item.type === 'text') {
      return `
        <div
          data-whiteboard-item="${escapeHtml(item.client_uuid)}"
          class="absolute cursor-move rounded-[20px] border border-slate-200 bg-white/92 px-4 py-3 shadow-sm ${outline}"
          style="${commonStyle} color:${escapeHtml(item.style.textColor ?? '#0F172A')};"
        >
          <div class="whiteboard-item-content text-lg font-semibold">${escapeHtml(item.payload.content ?? '')}</div>
        </div>
      `;
    }

    if (item.type === 'rectangle') {
      return `
        <div
          data-whiteboard-item="${escapeHtml(item.client_uuid)}"
          class="absolute cursor-move rounded-[26px] border-2 shadow-sm ${outline}"
          style="${commonStyle} background:${escapeHtml(item.style.fill ?? '#DBEAFE')}; border-color:${escapeHtml(item.style.stroke ?? '#1D4ED8')};"
        ></div>
      `;
    }

    if (item.type === 'circle') {
      return `
        <div
          data-whiteboard-item="${escapeHtml(item.client_uuid)}"
          class="absolute cursor-move rounded-full border-2 shadow-sm ${outline}"
          style="${commonStyle} background:${escapeHtml(item.style.fill ?? '#DCFCE7')}; border-color:${escapeHtml(item.style.stroke ?? '#15803D')};"
        ></div>
      `;
    }

    const stroke = item.style.stroke ?? '#0F172A';
    const points = Array.isArray(item.payload.points) ? item.payload.points : [];

    return `
      <div
        data-whiteboard-item="${escapeHtml(item.client_uuid)}"
        class="absolute cursor-move ${outline}"
        style="${commonStyle}"
      >
        <svg width="${item.width}" height="${item.height}" viewBox="0 0 ${item.width} ${item.height}" class="overflow-visible">
          <path
            d="${escapeHtml(pointsToPath(points))}"
            fill="none"
            stroke="${escapeHtml(stroke)}"
            stroke-width="3"
            stroke-linecap="round"
            stroke-linejoin="round"
          ></path>
        </svg>
      </div>
    `;
  }

  renderDraftStroke() {
    if (!this.draftStroke?.points?.length) return '';

    return `
      <svg class="pointer-events-none absolute inset-0 z-[999] h-full w-full">
        <path
          d="${escapeHtml(pointsToPath(this.draftStroke.points))}"
          fill="none"
          stroke="#0F172A"
          stroke-width="3"
          stroke-linecap="round"
          stroke-linejoin="round"
        ></path>
      </svg>
    `;
  }

  renderInspector() {
    const item = this.getSelectedItem();

    if (!item) {
      return `
        <div class="rounded-[24px] border border-dashed border-slate-300 bg-white p-5 text-sm text-slate-500">
          Selectionnez un element du tableau pour modifier son contenu, sa couleur ou le supprimer.
        </div>
      `;
    }

    const supportsText = item.type === 'note' || item.type === 'text';
    const supportsFill = item.type === 'note' || item.type === 'rectangle' || item.type === 'circle';
    const supportsStroke = item.type === 'rectangle' || item.type === 'circle' || item.type === 'stroke';
    const supportsTextColor = item.type === 'note' || item.type === 'text';

    return `
      <div class="space-y-4 rounded-[24px] border border-slate-200 bg-white p-5 shadow-sm">
        <div>
          <p class="text-xs font-bold uppercase tracking-[0.18em] text-slate-400">Element selectionne</p>
          <p class="mt-1 text-lg font-semibold text-slate-900">${escapeHtml(item.type)}</p>
        </div>

        ${supportsText ? `
          <label class="block text-sm font-semibold text-slate-700">
            Contenu
            <textarea
              data-whiteboard-field="content"
              class="mt-2 min-h-28 w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-900 focus:border-bleuone focus:ring-0"
            >${escapeHtml(item.payload.content ?? '')}</textarea>
          </label>
        ` : ''}

        ${supportsFill ? `
          <label class="block text-sm font-semibold text-slate-700">
            Couleur de fond
            <input
              type="color"
              data-whiteboard-field="fill"
              value="${escapeHtml(item.style.fill ?? '#FDE68A')}"
              class="mt-2 h-11 w-full rounded-xl border border-slate-200 bg-white px-2"
            >
          </label>
        ` : ''}

        ${supportsStroke ? `
          <label class="block text-sm font-semibold text-slate-700">
            Couleur du trait
            <input
              type="color"
              data-whiteboard-field="stroke"
              value="${escapeHtml(item.style.stroke ?? '#0F172A')}"
              class="mt-2 h-11 w-full rounded-xl border border-slate-200 bg-white px-2"
            >
          </label>
        ` : ''}

        ${supportsTextColor ? `
          <label class="block text-sm font-semibold text-slate-700">
            Couleur du texte
            <input
              type="color"
              data-whiteboard-field="textColor"
              value="${escapeHtml(item.style.textColor ?? '#172033')}"
              class="mt-2 h-11 w-full rounded-xl border border-slate-200 bg-white px-2"
            >
          </label>
        ` : ''}

        <div class="flex flex-col gap-3 pt-2">
          <button
            type="button"
            data-whiteboard-action="bring-front"
            class="inline-flex items-center justify-center rounded-2xl border border-slate-200 px-4 py-3 text-sm font-semibold text-slate-700 transition hover:border-bleuone hover:text-bleuone"
          >
            Mettre au premier plan
          </button>
          <button
            type="button"
            data-whiteboard-action="delete-selected"
            class="inline-flex items-center justify-center rounded-2xl bg-red-50 px-4 py-3 text-sm font-semibold text-red-700 transition hover:bg-red-100"
          >
            Supprimer l'element
          </button>
        </div>
      </div>
    `;
  }

  render() {
    const tools = [
      ['select', 'Selection'],
      ['pen', 'Dessiner'],
      ['note', 'Note'],
      ['text', 'Texte'],
      ['rectangle', 'Rectangle'],
      ['circle', 'Cercle'],
    ];

    this.root.innerHTML = `
      <div class="grid gap-6 xl:grid-cols-[220px_minmax(0,1fr)_300px]">
        <aside class="space-y-4">
          <div class="rounded-[24px] border border-slate-200 bg-white p-4 shadow-sm">
            <p class="text-xs font-bold uppercase tracking-[0.18em] text-slate-400">Outils</p>
            <div class="mt-4 grid gap-2">
              ${tools.map(([tool, label]) => `
                <button
                  type="button"
                  data-whiteboard-tool="${tool}"
                  class="inline-flex items-center justify-center rounded-2xl px-4 py-3 text-sm font-semibold transition ${
                    this.activeTool === tool
                      ? 'bg-bleuone text-white'
                      : 'bg-slate-100 text-slate-700 hover:bg-slate-200'
                  }"
                >
                  ${label}
                </button>
              `).join('')}
            </div>
          </div>

          <div class="rounded-[24px] border border-slate-200 bg-white p-4 text-sm text-slate-600 shadow-sm">
            Cliquez sur le tableau pour ajouter une note, un texte ou une forme. Avec l'outil dessin, tracez puis relachez pour sauvegarder.
          </div>

          ${this.permissions.can_clear ? `
            <button
              type="button"
              data-whiteboard-action="clear-board"
              class="inline-flex w-full items-center justify-center rounded-2xl bg-red-50 px-4 py-3 text-sm font-semibold text-red-700 transition hover:bg-red-100"
            >
              Vider le tableau
            </button>
          ` : ''}
        </aside>

        <section class="overflow-hidden rounded-[28px] border border-slate-200 bg-white shadow-sm">
          <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-200 px-5 py-4">
            <div>
              <p class="text-xs font-bold uppercase tracking-[0.18em] text-slate-400">Tableau</p>
              <h2 class="text-lg font-semibold text-slate-900">${escapeHtml(this.snapshot.title ?? 'Tableau blanc')}</h2>
            </div>
            <div class="flex items-center gap-3 text-sm text-slate-500">
              <span>Version ${this.boardVersion}</span>
              <span class="rounded-full bg-slate-100 px-3 py-1 font-medium text-slate-700" data-whiteboard-status>Synchronise</span>
            </div>
          </div>

          <div class="overflow-auto bg-slate-100 p-4">
            <div
              data-whiteboard-stage
              class="whiteboard-grid relative mx-auto overflow-hidden rounded-[28px] border border-slate-200 bg-white"
              style="width:${this.stageWidth}px; height:${this.stageHeight}px;"
            >
              ${this.items.map((item) => this.renderStageItem(item)).join('')}
              ${this.renderDraftStroke()}
            </div>
          </div>
        </section>

        <aside class="space-y-4">
          ${this.renderInspector()}
        </aside>
      </div>
    `;
  }
}

export function mountGroupWhiteboard() {
  const root = document.querySelector('[data-whiteboard-app]');
  const configNode = document.querySelector('[data-whiteboard-config]');

  if (!root || !configNode) return;

  const config = JSON.parse(configNode.textContent || '{}');
  const app = new GroupWhiteboardApp(root, config);
  app.init();
}
