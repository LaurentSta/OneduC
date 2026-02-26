import React, { useCallback, useEffect, useMemo, useRef, useState } from 'react';
import { createRoot } from 'react-dom/client';
import { Background, Controls, MarkerType, Position, ReactFlow } from '@xyflow/react';
import '@xyflow/react/dist/style.css';

function toPositiveInt(value) {
  const parsed = Number.parseInt(value, 10);
  return Number.isFinite(parsed) && parsed > 0 ? parsed : 0;
}

function parseJsonDataset(el, key, fallback) {
  const raw = el?.dataset?.[key];
  if (!raw) return fallback;

  try {
    const parsed = JSON.parse(raw);
    return parsed ?? fallback;
  } catch {
    return fallback;
  }
}

function normalizeAvailableModules(rawModules) {
  const out = [];
  const seen = new Set();

  if (!Array.isArray(rawModules)) return out;

  rawModules.forEach((raw) => {
    const id = toPositiveInt(raw?.id);
    if (!id || seen.has(id)) return;

    seen.add(id);
    const title = String(raw?.title ?? raw?.module_title ?? '').trim() || `Module #${id}`;
    out.push({ id, title });
  });

  return out;
}

function normalizeSelectedModules(rawModules, moduleMap) {
  const out = [];
  const seen = new Set();

  if (!Array.isArray(rawModules)) return out;

  rawModules.forEach((raw, index) => {
    const id = toPositiveInt(raw?.id);
    if (!id || seen.has(id)) return;

    seen.add(id);

    const titleFromMap = moduleMap.get(id)?.title;
    const fallbackTitle = String(raw?.title ?? raw?.module_title ?? '').trim();

    out.push({
      id,
      title: titleFromMap || fallbackTitle || `Module #${id}`,
      position: toPositiveInt(raw?.position) || index + 1,
      persisted: raw?.persisted !== false,
      manage_url: String(raw?.manage_url ?? '').trim(),
    });
  });

  out.sort((a, b) => a.position - b.position || a.id - b.id);

  return out.map((item, index) => ({
    ...item,
    position: index + 1,
  }));
}

function renumberModules(modules) {
  return modules.map((module, index) => ({
    ...module,
    position: index + 1,
  }));
}

function nodePosition(index) {
  const columns = 3;
  const col = index % columns;
  const row = Math.floor(index / columns);

  return {
    x: col * 240,
    y: row * 140 + 40,
  };
}

function GroupModuleFlow({
  availableModules = [],
  initialModules = [],
  manageLessonsLabel = 'Gérer les leçons',
}) {
  const normalizedAvailableModules = useMemo(
    () => normalizeAvailableModules(availableModules),
    [availableModules],
  );

  const availableModuleMap = useMemo(
    () => new Map(normalizedAvailableModules.map((module) => [module.id, module])),
    [normalizedAvailableModules],
  );

  const [selectedModules, setSelectedModules] = useState(() =>
    normalizeSelectedModules(initialModules, availableModuleMap),
  );

  const [newModuleId, setNewModuleId] = useState('');
  const [addError, setAddError] = useState('');
  const [flowInstance, setFlowInstance] = useState(null);
  const canvasRef = useRef(null);

  const selectedIds = useMemo(
    () => new Set(selectedModules.map((module) => module.id)),
    [selectedModules],
  );

  const selectableModules = useMemo(
    () => normalizedAvailableModules.filter((module) => !selectedIds.has(module.id)),
    [normalizedAvailableModules, selectedIds],
  );

  const flowNodes = useMemo(
    () =>
      selectedModules.map((module, index) => ({
        id: String(module.id),
        data: { label: `${module.position}. ${module.title}` },
        position: nodePosition(index),
        sourcePosition: Position.Right,
        targetPosition: Position.Left,
        draggable: false,
        selectable: false,
        style: {
          border: '2px solid #004461',
          borderRadius: '8px',
          padding: '10px 12px',
          background: '#fff',
        },
      })),
    [selectedModules],
  );


  const flowEdges = useMemo(
    () =>
      selectedModules.slice(1).map((module, index) => ({
        id: `edge-${selectedModules[index].id}-${module.id}`,
        source: String(selectedModules[index].id),
        target: String(module.id),
        markerEnd: {
          type: MarkerType.ArrowClosed,
          color: '#e94d2a', // couleur de la pointe
        },
        style: {
          stroke: '#e94d2a', // couleur de la ligne
          strokeWidth: 3,
        },
      })),
    [selectedModules],
  );

  const runFitView = useCallback(() => {
    if (!flowInstance || !canvasRef.current) return;
    if (canvasRef.current.clientWidth === 0 || canvasRef.current.clientHeight === 0) return;

    // Wait one full paint cycle to avoid racing with layout/container updates.
    window.requestAnimationFrame(() => {
      window.requestAnimationFrame(() => {
        flowInstance.fitView({ padding: 0.3, duration: 250 });
      });
    });
  }, [flowInstance]);

  useEffect(() => {
    if (!flowInstance) return;
    runFitView();
  }, [flowInstance, runFitView, selectedModules.length]);

  useEffect(() => {
    if (!flowInstance || !canvasRef.current || typeof ResizeObserver === 'undefined') return;

    const observer = new ResizeObserver(() => {
      runFitView();
    });

    observer.observe(canvasRef.current);
    return () => observer.disconnect();
  }, [flowInstance, runFitView]);

  const addModule = () => {
    setAddError('');

    const moduleId = toPositiveInt(newModuleId);
    if (!moduleId) return;

    if (selectedIds.has(moduleId)) {
      setAddError('Ce module est déjà dans le parcours.');
      setNewModuleId('');
      return;
    }

    const availableModule = availableModuleMap.get(moduleId);
    if (!availableModule) {
      setAddError('Module introuvable.');
      return;
    }

    setSelectedModules((current) =>
      renumberModules([
        ...current,
        {
          id: availableModule.id,
          title: availableModule.title,
          position: current.length + 1,
          persisted: false,
          manage_url: '',
        },
      ]),
    );

    setNewModuleId('');
  };

  const removeModule = (moduleId) => {
    const id = toPositiveInt(moduleId);
    if (!id) return;

    setSelectedModules((current) => renumberModules(current.filter((module) => module.id !== id)));
  };

  const moveModule = (moduleId, delta) => {
    const id = toPositiveInt(moduleId);
    if (!id || !Number.isFinite(delta)) return;

    setSelectedModules((current) => {
      const index = current.findIndex((module) => module.id === id);
      if (index < 0) return current;

      const swapIndex = index + delta;
      if (swapIndex < 0 || swapIndex >= current.length) return current;

      const next = [...current];
      [next[index], next[swapIndex]] = [next[swapIndex], next[index]];
      return renumberModules(next);
    });
  };

  return (
    <div className="space-y-4">
      <div className="flex flex-col gap-2 md:flex-row md:items-end">
        <div className="w-full">
          <label className="block text-sm">Ajouter un module</label>
          <select
            value={newModuleId}
            onChange={(event) => {
              setAddError('');
              setNewModuleId(event.target.value);
            }}
            className="w-full rounded border border-gray-300 px-3 py-2"
          >
            <option value="">- Sélectionner un module -</option>
            {selectableModules.map((module) => (
              <option key={`module-option-${module.id}`} value={module.id}>
                {module.title}
              </option>
            ))}
          </select>
          {addError ? <p className="mt-1 text-sm text-red-600">{addError}</p> : null}
        </div>

        <button
          type="button"
          onClick={addModule}
          disabled={!newModuleId}
          className="rounded border border-gray-300 px-4 py-2 disabled:opacity-50"
        >
          Ajouter
        </button>
      </div>

      <div className="overflow-hidden rounded border border-gray-200">
        <table className="w-full text-left text-sm">
          <thead className="border-b border-gray-200 bg-gray-50">
            <tr>
              <th className="w-[100px] px-4 py-3 text-center">Ordre</th>
              <th className="px-4 py-3">Nom du module</th>
              <th className="px-4 py-3 text-right">Actions</th>
            </tr>
          </thead>

          <tbody className="divide-y divide-gray-100 bg-white">
            {selectedModules.length === 0 ? (
              <tr>
                <td colSpan={3} className="py-6 text-center text-gray-500">
                  Le parcours est vide.
                </td>
              </tr>
            ) : (
              selectedModules.map((module, index) => (
                <tr key={`module-row-${module.id}`}>
                  <td className="px-4 py-3 text-center">
                    {module.position}
                    <input type="hidden" name="modules[]" value={module.id} />
                    <input
                      type="hidden"
                      name={`module_positions[${module.id}]`}
                      value={module.position}
                    />
                  </td>

                  <td className="px-4 py-3">{module.title}</td>

                  <td className="px-4 py-3 text-right">
                    <div className="inline-flex gap-2">
                      <button
                        type="button"
                        onClick={() => moveModule(module.id, -1)}
                        disabled={index === 0}
                        className="rounded border border-gray-300 px-2 py-1 disabled:opacity-30"
                      >
                        Monter
                      </button>

                      <button
                        type="button"
                        onClick={() => moveModule(module.id, +1)}
                        disabled={index === selectedModules.length - 1}
                        className="rounded border border-gray-300 px-2 py-1 disabled:opacity-30"
                      >
                        Descendre
                      </button>

                      {module.persisted && module.manage_url ? (
                        <a href={module.manage_url} className="rounded border border-gray-300 px-2 py-1">
                          {manageLessonsLabel}
                        </a>
                      ) : null}

                      <button
                        type="button"
                        onClick={() => removeModule(module.id)}
                        className="rounded border border-gray-300 px-2 py-1"
                      >
                        Retirer
                      </button>
                    </div>
                  </td>
                </tr>
              ))
            )}
          </tbody>
        </table>
      </div>

      <div className="overflow-hidden rounded border border-gray-200">
        <div ref={canvasRef} className="h-[320px] w-full">
          <ReactFlow
            nodes={flowNodes}
            edges={flowEdges}
            onInit={(instance) => {
              setFlowInstance(instance);
            }}
            nodesDraggable={false}
            nodesConnectable={false}
            elementsSelectable={false}
            zoomOnDoubleClick={false}
            proOptions={{ hideAttribution: true }}
          >
            <Controls showInteractive={false} position="bottom-right" />
            <Background gap={18} size={1} color="#ddd" />
          </ReactFlow>
        </div>
      </div>
    </div>
  );
}

function mountGroupModuleFlow() {
  const mounts = document.querySelectorAll('[data-group-module-flow]');

  mounts.forEach((mount) => {
    if (mount.dataset.flowMounted === '1') return;

    const availableModules = parseJsonDataset(mount, 'availableModules', []);
    const selectedModules = parseJsonDataset(mount, 'selectedModules', []);
    const manageLessonsLabel = String(mount.dataset.manageLessonsLabel || 'Gérer les leçons');

    const root = createRoot(mount);
    root.render(
      <GroupModuleFlow
        availableModules={availableModules}
        initialModules={selectedModules}
        manageLessonsLabel={manageLessonsLabel}
      />,
    );

    mount.dataset.flowMounted = '1';
  });
}

if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', mountGroupModuleFlow);
} else {
  mountGroupModuleFlow();
}
