import React, { useCallback, useEffect, useMemo, useRef, useState } from 'react';
import { createRoot } from 'react-dom/client';
import { Background, Controls, MarkerType, Position, ReactFlow } from '@xyflow/react';
import '@xyflow/react/dist/style.css';

const FLOW_COLUMNS = 3;
const FLOW_HORIZONTAL_GAP = 340;
const FLOW_VERTICAL_GAP = 190;
const BANNER_OFFSET = 160;

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

function nodePosition(index, hasBanner = false) {
  const col = index % FLOW_COLUMNS;
  const row = Math.floor(index / FLOW_COLUMNS);

  return {
    x: col * FLOW_HORIZONTAL_GAP,
    y: row * FLOW_VERTICAL_GAP + 40 + (hasBanner ? BANNER_OFFSET : 0),
  };
}

function ParcoursHeaderLabel({ parcours }) {
  const chip = (label, bg, text) => (
    <span key={label} style={{ background: bg, color: text }}
      className="inline-flex items-center rounded-full px-2 py-0.5 text-[10px] font-semibold">
      {label}
    </span>
  );
  return (
    <div style={{ minWidth: 0 }}>
      <div className="text-[10px] font-bold uppercase tracking-wider mb-1" style={{ color: 'rgba(255,255,255,0.65)' }}>
        Parcours de formation
      </div>
      <div className="text-sm font-bold leading-snug mb-2.5" style={{ color: '#fff' }}>
        {parcours.title}
      </div>
      <div className="flex flex-wrap gap-1.5">
        {chip(`${parcours.module_count} module${parcours.module_count > 1 ? 's' : ''}`, 'rgba(255,255,255,0.18)', '#fff')}
        {chip(`${parcours.lesson_count} leçon${parcours.lesson_count > 1 ? 's' : ''}`, '#e94d2a', '#fff')}
        {chip(`${parcours.question_count} question${parcours.question_count > 1 ? 's' : ''}`, '#16a34a', '#fff')}
        {parcours.wc_count > 0 && chip(`${parcours.wc_count} nuage${parcours.wc_count > 1 ? 's' : ''} de mots`, '#d97706', '#fff')}
        {parcours.poll_count > 0 && chip(`${parcours.poll_count} sondage${parcours.poll_count > 1 ? 's' : ''}`, '#0d9488', '#fff')}
      </div>
    </div>
  );
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
  availableParcours = [],
  initialModules = [],
  initialParcoursId = null,
  manageLessonsLabel = 'Gérer les leçons',
  mountNode = null,
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

  const [pickerTab, setPickerTab] = useState('modules');
  const [loadedParcours, setLoadedParcours] = useState(() => {
    if (!initialParcoursId) return null;
    return availableParcours.find((p) => p.id === initialParcoursId) ?? null;
  });

  const loadParcours = (parcours) => {
    const rawModules = Array.isArray(parcours.modules) ? parcours.modules : [];
    if (!rawModules.length) return;

    setLoadedParcours(parcours);
    setSelectedModules((current) => {
      const existingIds = new Set(current.map((m) => m.id));
      const newModules = rawModules
        .map((m) => ({
          id: toPositiveInt(m?.id),
          title: String(m?.title ?? '').trim() || `Module #${m?.id}`,
          position: 0,
          persisted: false,
          manage_url: '',
          lesson_count: Math.max(0, Number(m?.lesson_count ?? 0) || 0),
          question_count: Math.max(0, Number(m?.question_count ?? 0) || 0),
          duration_label: String(m?.duration_label ?? '').trim() || 'Rythme libre',
        }))
        .filter((m) => m.id > 0 && !existingIds.has(m.id));

      return renumberModules([...current, ...newModules]);
    });
  };

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

  const flowNodes = useMemo(() => {
    const hasBanner = loadedParcours !== null;
    const nodes = [];

    if (hasBanner) {
      const colCount = Math.max(1, Math.min(selectedModules.length, FLOW_COLUMNS));
      const bannerWidth = (colCount - 1) * FLOW_HORIZONTAL_GAP + 260;
      nodes.push({
        id: 'parcours-banner',
        data: { label: <ParcoursHeaderLabel parcours={loadedParcours} /> },
        position: { x: 0, y: 0 },
        sourcePosition: Position.Bottom,
        draggable: false,
        selectable: false,
        style: {
          border: '2px solid #004461',
          borderRadius: '12px',
          width: bannerWidth,
          padding: '12px 16px',
          background: '#004461',
        },
      });
    }

    selectedModules.forEach((module, index) => {
      nodes.push({
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
        position: nodePosition(index, hasBanner),
        sourcePosition: isWrapToNextRow(index, selectedModules.length) ? Position.Bottom : Position.Right,
        targetPosition: isWrapFromPreviousRow(index) ? Position.Top : (hasBanner && index === 0 ? Position.Top : Position.Left),
        draggable: false,
        selectable: false,
        style: {
          border: '2px solid #004461',
          borderRadius: '8px',
          width: 260,
          padding: '12px 14px',
          background: '#fff',
        },
      });
    });

    return nodes;
  }, [selectedModules, loadedParcours]);

  const flowEdges = useMemo(() => {
    const edges = [];

    if (loadedParcours && selectedModules.length > 0) {
      edges.push({
        id: 'edge-banner-first',
        source: 'parcours-banner',
        target: String(selectedModules[0].id),
        type: 'smoothstep',
        markerEnd: { type: MarkerType.ArrowClosed, color: '#e94d2a' },
        style: { stroke: '#e94d2a', strokeWidth: 3 },
      });
    }

    selectedModules.slice(1).forEach((module, index) => {
      edges.push({
        id: `edge-${selectedModules[index].id}-${module.id}`,
        source: String(selectedModules[index].id),
        target: String(module.id),
        type: 'smoothstep',
        pathOptions: { borderRadius: 22, offset: 40 },
        markerEnd: { type: MarkerType.ArrowClosed, color: '#e94d2a' },
        style: { stroke: '#e94d2a', strokeWidth: 3 },
      });
    });

    return edges;
  }, [selectedModules, loadedParcours]);

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

    setLoadedParcours(null);
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

    setLoadedParcours(null);
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
        {availableParcours.length > 0 && (
          <div className="flex gap-1 border-b border-gray-200 mb-1">
            <button
              type="button"
              onClick={() => setPickerTab('modules')}
              className={`px-4 py-2 text-sm font-semibold border-b-2 -mb-px transition ${pickerTab === 'modules' ? 'border-[#004461] text-[#004461]' : 'border-transparent text-gray-500 hover:text-gray-700'}`}
            >
              Modules individuels
            </button>
            <button
              type="button"
              onClick={() => setPickerTab('parcours')}
              className={`px-4 py-2 text-sm font-semibold border-b-2 -mb-px transition ${pickerTab === 'parcours' ? 'border-[#004461] text-[#004461]' : 'border-transparent text-gray-500 hover:text-gray-700'}`}
            >
              Mes parcours ({availableParcours.length})
            </button>
          </div>
        )}

        {pickerTab === 'parcours' ? (
          <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3 pb-1">
            {availableParcours.map((parcours) => (
              <div key={`parcours-card-${parcours.id}`} className="rounded-[12px] border border-gray-200 bg-white shadow-sm overflow-hidden flex flex-col">
                <div className="bg-[#004461] px-4 py-3">
                  <h4 className="text-sm font-bold text-white leading-snug">{parcours.title}</h4>
                </div>
                <div className="flex flex-col flex-1 px-4 py-3 gap-3">
                  {parcours.description && (
                    <p className="text-xs text-gray-500 line-clamp-2">{parcours.description}</p>
                  )}
                  <div className="grid grid-cols-3 gap-2">
                    <div className="rounded-lg bg-[#004461]/5 px-2 py-2 text-center">
                      <div className="text-[10px] font-semibold text-[#004461] uppercase tracking-wide">Modules</div>
                      <div className="text-sm font-bold text-[#004461]">{parcours.module_count}</div>
                    </div>
                    <div className="rounded-lg bg-orange-500/10 px-2 py-2 text-center">
                      <div className="text-[10px] font-semibold text-orange-600 uppercase tracking-wide">Leçons</div>
                      <div className="text-sm font-bold text-orange-600">{parcours.lesson_count}</div>
                    </div>
                    <div className="rounded-lg bg-green-500/10 px-2 py-2 text-center">
                      <div className="text-[10px] font-semibold text-green-600 uppercase tracking-wide">Questions</div>
                      <div className="text-sm font-bold text-green-600">{parcours.question_count}</div>
                    </div>
                  </div>
                  {parcours.module_count > 0 ? (
                    <button
                      type="button"
                      onClick={() => loadParcours(parcours)}
                      className="mt-auto w-full inline-flex items-center justify-center gap-2 rounded-[8px] px-3 py-2 text-xs font-bold text-white bg-[#004461] hover:opacity-90 transition"
                    >
                      <svg xmlns="http://www.w3.org/2000/svg" className="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M12 4v16m8-8H4" />
                      </svg>
                      Ajouter ce parcours
                    </button>
                  ) : (
                    <p className="mt-auto text-center text-[10px] text-gray-400 italic">
                      Ce parcours ne contient pas de modules — ajoutez des étapes de type module dans le builder pour l'utiliser ici.
                    </p>
                  )}
                </div>
              </div>
            ))}
          </div>
        ) : (
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
        )}
      </div>

      {/* Hidden input liant le groupe au parcours */}
      <input type="hidden" name="formateur_parcours_id" value={loadedParcours?.id ?? ''} />

      {/* Bandeau parcours chargé + bouton retrait */}
      {loadedParcours && (
        <div className="flex items-center gap-3 rounded-[10px] border border-[#004461]/25 bg-[#004461]/8 px-4 py-2.5 mb-2">
          <svg xmlns="http://www.w3.org/2000/svg" className="h-4 w-4 shrink-0 text-[#004461]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
          </svg>
          <span className="text-sm font-semibold text-[#004461] flex-1 truncate">
            Parcours lié : {loadedParcours.title}
          </span>
          <button
            type="button"
            onClick={() => {
              setLoadedParcours(null);
              setSelectedModules([]);
            }}
            className="inline-flex items-center gap-1.5 rounded-[6px] border border-red-300 bg-white px-2.5 py-1 text-xs font-semibold text-red-600 hover:bg-red-50 transition"
          >
            <svg xmlns="http://www.w3.org/2000/svg" className="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
            Retirer le parcours
          </button>
        </div>
      )}

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
    const availableParcours = parseJsonDataset(mount, 'availableParcours', []);
    const selectedModules = parseJsonDataset(mount, 'selectedModules', []);
    const manageLessonsLabel = String(mount.dataset.manageLessonsLabel || 'Gérer les leçons');
    const initialParcoursId = mount.dataset.initialParcoursId
      ? parseInt(mount.dataset.initialParcoursId, 10) || null
      : null;

    const root = createRoot(mount);
    root.render(
      <GroupModuleFlow
        availableModules={availableModules}
        availableParcours={availableParcours}
        initialModules={selectedModules}
        initialParcoursId={initialParcoursId}
        manageLessonsLabel={manageLessonsLabel}
        mountNode={mount}
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
