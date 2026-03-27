import React, { useCallback, useEffect, useMemo, useRef, useState } from 'react';
import { createRoot } from 'react-dom/client';
import { Background, Controls, MarkerType, Position, ReactFlow } from '@xyflow/react';
import '@xyflow/react/dist/style.css';

const FLOW_COLUMNS = 3;
const FLOW_HORIZONTAL_GAP = 340;
const FLOW_VERTICAL_GAP = 190;

function isWrapToNextRow(index, total) {
  if (index < 0 || index >= total - 1) return false;

  return Math.floor(index / FLOW_COLUMNS) !== Math.floor((index + 1) / FLOW_COLUMNS);
}

function isWrapFromPreviousRow(index) {
  if (index <= 0) return false;

  return Math.floor(index / FLOW_COLUMNS) !== Math.floor((index - 1) / FLOW_COLUMNS);
}

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
    out.push({
      id,
      title,
      lesson_count: Math.max(0, Number(raw?.lesson_count ?? 0) || 0),
      question_count: Math.max(0, Number(raw?.question_count ?? 0) || 0),
      duration_label: String(raw?.duration_label ?? '').trim() || 'Rythme libre',
    });
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
      lesson_count: Math.max(0, Number(raw?.lesson_count ?? moduleMap.get(id)?.lesson_count ?? 0) || 0),
      question_count: Math.max(0, Number(raw?.question_count ?? moduleMap.get(id)?.question_count ?? 0) || 0),
      duration_label: String(raw?.duration_label ?? moduleMap.get(id)?.duration_label ?? '').trim() || 'Rythme libre',
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
  const col = index % FLOW_COLUMNS;
  const row = Math.floor(index / FLOW_COLUMNS);

  return {
    x: col * FLOW_HORIZONTAL_GAP,
    y: row * FLOW_VERTICAL_GAP + 40,
  };
}

function OpenBookIcon({ className = 'h-4 w-4' }) {
  return (
    <svg xmlns="http://www.w3.org/2000/svg" className={className} fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
      <path
        strokeLinecap="round"
        strokeLinejoin="round"
        strokeWidth="1.8"
        d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"
      />
    </svg>
  );
}

function ModuleMetric({ label, value }) {
  return (
    <div className="rounded-md border border-slate-200 bg-slate-50 px-2 py-1 text-center">
      <div className="text-[10px] font-semibold uppercase tracking-wide text-slate-500">{label}</div>
      <div className="text-[11px] font-bold text-slate-700">{value}</div>
    </div>
  );
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
        data: {
          label: (
            <div className="min-w-[220px] max-w-[220px]">
              <div className="flex items-start gap-2">
                <OpenBookIcon className="mt-0.5 h-4 w-4 shrink-0" />
                <span className="text-[12px] font-semibold leading-snug text-slate-900">
                  {`${module.position}. ${module.title}`}
                </span>
              </div>

              <div className="mt-3 grid grid-cols-3 gap-2">
                <ModuleMetric label="Leçons" value={module.lesson_count} />
                <ModuleMetric label="Questions" value={module.question_count} />
                <ModuleMetric label="Durée" value={module.duration_label} />
              </div>
            </div>
          ),
        },
        position: nodePosition(index),
        sourcePosition: isWrapToNextRow(index, selectedModules.length) ? Position.Bottom : Position.Right,
        targetPosition: isWrapFromPreviousRow(index) ? Position.Top : Position.Left,
        draggable: false,
        selectable: false,
        style: {
          border: '2px solid #004461',
          borderRadius: '8px',
          width: 260,
          padding: '12px 14px',
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
        type: 'smoothstep',
        pathOptions: {
          borderRadius: 22,
          offset: 40,
        },
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
          lesson_count: availableModule.lesson_count,
          question_count: availableModule.question_count,
          duration_label: availableModule.duration_label,
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
      <div className="space-y-3">
        <div className="flex flex-col gap-3 md:flex-row md:items-end">
          <div className="w-full">
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
            className="inline-flex items-center justify-center gap-2 rounded-lg border border-orangeone bg-orangeone px-4 py-2 text-sm font-bold text-white transition hover:opacity-90 disabled:cursor-not-allowed disabled:opacity-50 md:shrink-0"
          >
            <svg xmlns="http://www.w3.org/2000/svg" className="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M12 4v16m8-8H4" />
            </svg>
            Ajouter un module
          </button>
        </div>
      </div>

      <div className="overflow-x-auto rounded-[20px] border-2 border-bleuone/20 bg-white shadow-md">
        <table className="min-w-full bg-white text-left text-sm text-gray-800">
          <thead className="sticky top-0 z-10 bg-bleuone text-xs uppercase text-white">
            <tr>
              <th className="w-[100px] px-4 py-3 text-center">Ordre</th>
              <th className="px-4 py-3">Nom du module</th>
              <th className="px-4 py-3 text-right">Actions</th>
            </tr>
          </thead>

          <tbody>
            {selectedModules.length === 0 ? (
              <tr>
                <td colSpan={3} className="px-4 py-6 text-center text-gray-500">
                  Le parcours est vide.
                </td>
              </tr>
            ) : (
              selectedModules.map((module, index) => (
                <tr
                  key={`module-row-${module.id}`}
                  className={`${index % 2 === 0 ? 'bg-white' : 'bg-orangeone/8'} border-t hover:bg-orangeone/15 transition-colors`}
                >
                  <td className="px-4 py-3 text-center font-medium">
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
                        title={index === 0 ? 'Ce module est déjà en première position' : 'Monter ce module'}
                        aria-label={index === 0 ? 'Impossible de monter ce module' : 'Monter ce module'}
                        className="inline-flex h-8 w-8 items-center justify-center rounded border border-gray-300 text-bleuone hover:bg-gray-50 disabled:cursor-not-allowed disabled:opacity-30"
                      >
                        <svg
                          xmlns="http://www.w3.org/2000/svg"
                          viewBox="0 0 24 24"
                          fill="none"
                          stroke="currentColor"
                          strokeWidth="2"
                          strokeLinecap="round"
                          strokeLinejoin="round"
                          className="h-4 w-4"
                          aria-hidden="true"
                        >
                          <path d="m18 15-6-6-6 6" />
                        </svg>
                      </button>

                      <button
                        type="button"
                        onClick={() => moveModule(module.id, +1)}
                        disabled={index === selectedModules.length - 1}
                        title={index === selectedModules.length - 1 ? 'Ce module est déjà en dernière position' : 'Descendre ce module'}
                        aria-label={index === selectedModules.length - 1 ? 'Impossible de descendre ce module' : 'Descendre ce module'}
                        className="inline-flex h-8 w-8 items-center justify-center rounded border border-gray-300 text-bleuone hover:bg-gray-50 disabled:cursor-not-allowed disabled:opacity-30"
                      >
                        <svg
                          xmlns="http://www.w3.org/2000/svg"
                          viewBox="0 0 24 24"
                          fill="none"
                          stroke="currentColor"
                          strokeWidth="2"
                          strokeLinecap="round"
                          strokeLinejoin="round"
                          className="h-4 w-4"
                          aria-hidden="true"
                        >
                          <path d="m6 9 6 6 6-6" />
                        </svg>
                      </button>

                      {module.persisted && module.manage_url ? (
                        <a
                          href={module.manage_url}
                          className="rounded border border-bleuone bg-bleuone px-2 py-1 font-semibold text-white shadow-sm transition hover:opacity-90"
                        >
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
