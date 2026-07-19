import React, { useCallback, useEffect, useMemo, useRef, useState } from 'react';
import { createRoot } from 'react-dom/client';
import { Background, Controls, MarkerType, Position, ReactFlow } from '@xyflow/react';
import '@xyflow/react/dist/style.css';
import { CloudIcon, PollIcon, pollTitle, pollChoices, WordCloudForm, PollForm } from './shared/tool-forms.jsx';
import {
  GenericToolForm,
  GenericToolStatusBadge,
  genericToolPayload,
  genericToolTitle,
  normalizeGenericTool,
} from './shared/generic-parcours-tool.jsx';

const FLOW_COLUMNS = 3;
const FLOW_HORIZONTAL_GAP = 340;
const FLOW_VERTICAL_GAP = 200;

// ─── Helpers ─────────────────────────────────────────────────────────────────

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
  try { return JSON.parse(raw) ?? fallback; } catch { return fallback; }
}

function renumber(items) {
  return items.map((item, index) => ({ ...item, position: index + 1 }));
}

function nodePosition(index) {
  const col = index % FLOW_COLUMNS;
  const row = Math.floor(index / FLOW_COLUMNS);
  return { x: col * FLOW_HORIZONTAL_GAP, y: row * FLOW_VERTICAL_GAP + 40 };
}

function normalizeForMatch(value) {
  return String(value ?? '')
    .normalize('NFD')
    .replace(/[\u0300-\u036f]/g, '')
    .toLowerCase()
    .replace(/[^a-z0-9]+/g, ' ')
    .trim();
}

function moduleTitleMatches(item, expectedTitle) {
  if (item?.type !== 'module') return false;

  const title = normalizeForMatch(item.title);
  const expected = normalizeForMatch(expectedTitle);

  return title === expected || title.includes(expected);
}

function validateSimulationPathItems(items) {
  if (items.length !== 5) {
    return 'Le parcours doit contenir exactement 5 étapes : un nuage de mots, trois formations, puis un sondage.';
  }

  if (items[0]?.type !== 'wordcloud') {
    return 'La première étape doit être un nuage de mots.';
  }

  if (!moduleTitleMatches(items[1], 'Securite alimentaire 2026')) {
    return 'La deuxième étape doit être la formation Sécurité alimentaire 2026.';
  }

  if (!moduleTitleMatches(items[2], 'Hygiene en cuisine professionnelle')) {
    return 'La troisième étape doit être la formation Hygiène en cuisine professionnelle.';
  }

  if (!moduleTitleMatches(items[3], 'Nettoyage et desinfection des espaces')) {
    return 'La quatrième étape doit être la formation Nettoyage et désinfection des espaces.';
  }

  if (items[4]?.type !== 'poll') {
    return 'La cinquième étape doit être un sondage.';
  }

  return '';
}

// ─── Normalize incoming data ──────────────────────────────────────────────────

function normalizeAvailableModules(rawModules) {
  const out = [];
  const seen = new Set();
  if (!Array.isArray(rawModules)) return out;
  rawModules.forEach((raw) => {
    const id = toPositiveInt(raw?.id);
    if (!id || seen.has(id)) return;
    seen.add(id);
    out.push({
      id,
      title: String(raw?.title ?? raw?.module_title ?? '').trim() || `Formation #${id}`,
      lesson_count:   Math.max(0, Number(raw?.lesson_count ?? 0) || 0),
      question_count: Math.max(0, Number(raw?.question_count ?? 0) || 0),
      duration_label: String(raw?.duration_label ?? '').trim() || 'Rythme libre',
      category_id:    raw?.category_id != null ? String(raw.category_id) : 'uncategorized',
      category_name:  String(raw?.category_name ?? '').trim() || 'Non catégorisées',
    });
  });
  return out;
}

function buildCategories(modules) {
  const map = new Map();
  modules.forEach((m) => {
    if (!map.has(m.category_id)) map.set(m.category_id, { id: m.category_id, name: m.category_name, count: 0 });
    map.get(m.category_id).count += 1;
  });
  return Array.from(map.values()).sort((a, b) => a.name.localeCompare(b.name, 'fr'));
}

let _wcCounter = 0;
function newWcId()   { return `wc-new-${++_wcCounter}`; }
function newPollId() { return `poll-new-${++_wcCounter}`; }
function newGenericToolId(outil) { return `outil-new-${outil}-${++_wcCounter}`; }

function normalizeToolTemplates(rawTemplates) {
  if (!Array.isArray(rawTemplates)) return [];
  return rawTemplates
    .map((raw, index) => {
      const id = String(raw?.id ?? '').trim();
      if (!id) return null;
      if (raw?.type === 'outil') {
        const outil = normalizeGenericTool(raw, index);
        return outil ? { ...outil, id } : null;
      }
      if (raw?.type === 'wordcloud') {
        return {
          type: 'wordcloud',
          id,
          wc_title: String(raw?.wc_title ?? '').trim(),
          wc_questions: Array.isArray(raw?.wc_questions)
            ? raw.wc_questions.map((q) => String(q).trim()).filter(Boolean)
            : [],
          wc_duration: raw?.wc_duration ? Number(raw.wc_duration) : null,
        };
      }
      if (raw?.type === 'poll') {
        return {
          type: 'poll',
          id,
          poll_questions: Array.isArray(raw?.poll_questions)
            ? raw.poll_questions.map((pq) => ({
                question: String(pq?.question ?? '').trim(),
                choices: Array.isArray(pq?.choices) ? pq.choices.map(String) : [],
              })).filter((pq) => pq.question)
            : [],
          poll_duration: raw?.poll_duration ? Number(raw.poll_duration) : null,
        };
      }
      return null;
    })
    .filter(Boolean);
}

function normalizeSelectedItems(rawItems, moduleMap) {
  if (!Array.isArray(rawItems)) return [];
  return rawItems
    .map((raw, index) => {
      if (raw?.type === 'outil') {
        return normalizeGenericTool(raw, index);
      }
      if (raw?.type === 'wordcloud') {
        return {
          type:         'wordcloud',
          id:           String(raw?.id ?? newWcId()),
          position:     toPositiveInt(raw?.position) || index + 1,
          wc_title:     String(raw?.wc_title ?? '').trim(),
          wc_questions: Array.isArray(raw?.wc_questions)
            ? raw.wc_questions.map(q => String(q).trim()).filter(Boolean)
            : [],
          wc_duration:  raw?.wc_duration ? Number(raw.wc_duration) : null,
        };
      }
      if (raw?.type === 'poll') {
        return {
          type:           'poll',
          id:             String(raw?.id ?? newPollId()),
          position:       toPositiveInt(raw?.position) || index + 1,
          poll_questions: Array.isArray(raw?.poll_questions)
            ? raw.poll_questions.map(pq => ({
                question: String(pq?.question ?? '').trim(),
                choices:  Array.isArray(pq?.choices) ? pq.choices.map(String) : [],
              })).filter(pq => pq.question)
            : [],
          poll_duration: raw?.poll_duration ? Number(raw.poll_duration) : null,
        };
      }
      const moduleId = toPositiveInt(raw?.id ?? raw?.module_id);
      if (!moduleId) return null;
      const mod = moduleMap.get(moduleId);
      return {
        type:           'module',
        id:             moduleId,
        position:       toPositiveInt(raw?.position) || index + 1,
        title:          mod?.title || String(raw?.title ?? '').trim() || `Formation #${moduleId}`,
        lesson_count:   mod?.lesson_count   ?? Number(raw?.lesson_count ?? 0),
        question_count: mod?.question_count ?? Number(raw?.question_count ?? 0),
        duration_label: (mod?.duration_label ?? String(raw?.duration_label ?? '').trim()) || 'Rythme libre',
      };
    })
    .filter(Boolean)
    .sort((a, b) => a.position - b.position)
    .map((item, index) => ({ ...item, position: index + 1 }));
}

// ─── Icons ────────────────────────────────────────────────────────────────────

function OpenBookIcon({ className = 'h-4 w-4' }) {
  return (
    <svg xmlns="http://www.w3.org/2000/svg" className={className} fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
      <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="1.8"
        d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
    </svg>
  );
}

function GenericToolIcon({ className = 'h-4 w-4' }) {
  return (
    <svg xmlns="http://www.w3.org/2000/svg" className={className} fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
      <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="1.8" d="M12 3v3m0 12v3M3 12h3m12 0h3M5.64 5.64l2.12 2.12m8.48 8.48 2.12 2.12m0-12.72-2.12 2.12M7.76 16.24l-2.12 2.12" />
      <circle cx="12" cy="12" r="4" strokeWidth="1.8" />
    </svg>
  );
}

function ToolButton({ label, className, onClick, disabled = false }) {
  return (
    <button
      type="button"
      onClick={onClick}
      disabled={disabled}
      aria-disabled={disabled}
      className={`inline-flex w-full items-center justify-center rounded-[10px] px-4 py-2.5 text-sm font-bold text-white shadow-sm transition ${className} ${disabled ? 'cursor-not-allowed opacity-45 grayscale hover:opacity-45' : ''}`}
    >
      {label}
    </button>
  );
}

function SimulationFeedbackModal({ message, attemptsLeft = null, canStop = false, onClose, onRestart, onStop }) {
  if (!message) return null;

  return (
    <div
      className="fixed inset-0 z-[80] flex items-center justify-center bg-slate-950/45 px-4 py-6"
      role="dialog"
      aria-modal="true"
      aria-labelledby="simulation-feedback-title"
    >
      <div className="w-full max-w-xl rounded-[20px] border border-orange-100 bg-white p-6 shadow-2xl">
        <div className="flex items-start gap-4">
          <div className="mt-1 inline-flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-orange-100 text-orange-700">
            <svg xmlns="http://www.w3.org/2000/svg" className="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M12 9v4m0 4h.01M10.29 3.86l-7.5 13A1 1 0 003.66 18h16.68a1 1 0 00.87-1.5l-7.5-13a1 1 0 00-1.74 0z" />
            </svg>
          </div>

          <div className="min-w-0 flex-1">
            <h2 id="simulation-feedback-title" className="font-raleway text-2xl font-bold text-[#004461]">
              Ajustement nécessaire
            </h2>
            <p className="mt-3 text-base leading-7 text-slate-700">
              {message}
            </p>
            {attemptsLeft !== null && (
              <p className={`mt-4 inline-flex rounded-full px-3 py-1 text-sm font-bold ${canStop ? 'bg-red-50 text-red-700' : 'bg-orange-50 text-orange-700'}`}>
                {canStop ? '3 essais utilisés' : `${attemptsLeft} essai${attemptsLeft > 1 ? 's' : ''} restant${attemptsLeft > 1 ? 's' : ''}`}
              </p>
            )}
          </div>
        </div>

        <div className="mt-6 flex flex-col gap-3 sm:flex-row sm:justify-end">
          <button
            type="button"
            onClick={onRestart}
            className="inline-flex items-center justify-center rounded-full border border-orange-200 bg-white px-6 py-3 text-sm font-bold text-orange-700 shadow-sm transition hover:border-orange-400 hover:bg-orange-50"
          >
            Recommencer l'activité
          </button>

          {canStop && (
            <button
              type="button"
              onClick={onStop}
              className="inline-flex items-center justify-center rounded-full border border-slate-300 bg-slate-800 px-6 py-3 text-sm font-bold text-white shadow-sm transition hover:bg-slate-900"
            >
              Stopper l'activité
            </button>
          )}

          <button
            type="button"
            onClick={onClose}
            className="inline-flex items-center justify-center rounded-full bg-[#E94D2A] px-6 py-3 text-sm font-bold text-white shadow-sm transition hover:bg-[#cf4121]"
          >
            Corriger le parcours
          </button>
        </div>
      </div>
    </div>
  );
}

function Metric({ label, value }) {
  return (
    <div className="rounded-md border border-slate-200 bg-slate-50 px-2 py-1 text-center">
      <div className="text-[10px] font-semibold uppercase tracking-wide text-slate-500">{label}</div>
      <div className="text-[11px] font-bold text-slate-700">{value}</div>
    </div>
  );
}

function ChevronRightIcon({ className = 'h-5 w-5' }) {
  return (
    <svg xmlns="http://www.w3.org/2000/svg" className={className} fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
      <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2.5" d="M9 5l7 7-7 7" />
    </svg>
  );
}

// ─── Bloc "cube" du plan (glissable) ───────────────────────────────────────────

function CubeCard({ item, isEditing, onDragStart, onDragOver, onDragEnd, onRemove, onOpenEdit, onCloseEdit, onUpdate }) {
  const isWc = item.type === 'wordcloud';
  const isPoll = item.type === 'poll';
  const isGenericTool = item.type === 'outil';
  const borderColor = isWc ? 'border-amber-400' : isPoll ? 'border-teal-500' : isGenericTool ? 'border-slate-400' : 'border-[#004461]';
  const bg = isWc ? 'bg-amber-50' : isPoll ? 'bg-teal-50' : isGenericTool ? 'bg-slate-50' : 'bg-white';

  return (
    <div className="flex flex-col">
      <div
        draggable
        onDragStart={onDragStart}
        onDragOver={onDragOver}
        onDragEnd={onDragEnd}
        className={`group relative w-[240px] cursor-grab select-none rounded-[10px] border-2 ${borderColor} ${bg} px-3.5 py-3 shadow-sm transition active:cursor-grabbing`}
      >
        <div className="flex items-start justify-between gap-2">
          <div className="flex min-w-0 items-start gap-2">
            {isWc
              ? <CloudIcon className="mt-0.5 h-4 w-4 shrink-0 text-amber-600" />
              : isPoll
                ? <PollIcon className="mt-0.5 h-4 w-4 shrink-0 text-teal-600" />
                : isGenericTool
                  ? <GenericToolIcon className="mt-0.5 h-4 w-4 shrink-0 text-slate-600" />
                  : <OpenBookIcon className="mt-0.5 h-4 w-4 shrink-0 text-[#004461]" />}
            <span className="truncate text-[12px] font-semibold leading-snug text-slate-900">
              {`${item.position}. ${isWc ? (item.wc_title || 'Nuage de mots') : isPoll ? pollTitle(item) : isGenericTool ? genericToolTitle(item) : item.title}`}
            </span>
          </div>
          <button type="button" onClick={onRemove} aria-label="Retirer cette étape"
            className="shrink-0 rounded-full p-1 text-gray-400 opacity-0 transition group-hover:opacity-100 group-hover:text-gray-600 hover:!bg-red-50 hover:!text-red-600">
            <svg xmlns="http://www.w3.org/2000/svg" className="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth="2.5">
              <path strokeLinecap="round" strokeLinejoin="round" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>

        {isWc && item.wc_questions?.length > 0 && (
          <p className="mt-2 text-[11px] italic text-amber-700 line-clamp-2">
            « {item.wc_questions[0]} »
            {item.wc_questions.length > 1 && <span className="ml-1 font-semibold not-italic">+{item.wc_questions.length - 1}</span>}
          </p>
        )}

        {isPoll && pollChoices(item).length > 0 && (
          <p className="mt-2 truncate text-[11px] text-teal-700">{pollChoices(item).join(' / ')}</p>
        )}

        {isGenericTool && (
          <div className="mt-2.5">
            <GenericToolStatusBadge />
          </div>
        )}

        {!isWc && !isPoll && !isGenericTool && (
          <div className="mt-2.5 grid grid-cols-3 gap-1.5">
            <Metric label="Leçons" value={item.lesson_count} />
            <Metric label="Questions" value={item.question_count} />
            <Metric label="Durée" value={item.duration_label} />
          </div>
        )}

        {(isWc || isPoll || isGenericTool) && !isEditing && (
          <button type="button" onClick={onOpenEdit}
            className="mt-2 text-[11px] font-semibold text-[#004461] hover:underline">
            Modifier
          </button>
        )}
      </div>

      {isEditing && (
        <div className="mt-2 w-[300px]">
          {isWc
            ? <WordCloudForm initialValues={item} onAdd={onUpdate} onCancel={onCloseEdit} />
            : isPoll
              ? <PollForm initialValues={item} onAdd={onUpdate} onCancel={onCloseEdit} />
              : <GenericToolForm item={item} onSave={onUpdate} onCancel={onCloseEdit} />
          }
        </div>
      )}
    </div>
  );
}

// ─── Main component ───────────────────────────────────────────────────────────

function ParcoursBuilder({ availableModules = [], toolTemplates = [], wordcloudUrl = '', sondageUrl = '', initialItems = [], csrfToken, storeUrl, method, mode }) {
  const isPreview = mode === 'preview';
  const isSimulation = mode === 'simulation';

  const normalizedAvailableModules = useMemo(() => normalizeAvailableModules(availableModules), [availableModules]);
  const availableModuleMap = useMemo(
    () => new Map(normalizedAvailableModules.map((m) => [m.id, m])),
    [normalizedAvailableModules],
  );

  const normalizedToolTemplates = useMemo(() => normalizeToolTemplates(toolTemplates), [toolTemplates]);

  const [items, setItems] = useState(() => normalizeSelectedItems(initialItems, availableModuleMap));
  const [newModuleId, setNewModuleId]     = useState('');
  const [addError, setAddError]           = useState('');
  const [showWcForm, setShowWcForm]       = useState(false);
  const [showPollForm, setShowPollForm]   = useState(false);
  const [editingItemId, setEditingItemId] = useState(null);
  const [saveError, setSaveError]         = useState('');
  const [simulationFeedback, setSimulationFeedback] = useState('');
  const [simulationAttempts, setSimulationAttempts] = useState(0);
  const [saving, setSaving]               = useState(false);
  const [flowInstance, setFlowInstance]   = useState(null);
  const canvasRef = useRef(null);

  const [selectedCategoryId, setSelectedCategoryId] = useState('');
  const [dragSource, setDragSource]         = useState(null); // { kind: 'module'|'item'|'readytool', id }
  const [dropTargetIndex, setDropTargetIndex] = useState(null);

  const selectedModuleIds = useMemo(
    () => new Set(items.filter((i) => i.type === 'module').map((i) => i.id)),
    [items],
  );

  const selectableModules = useMemo(
    () => normalizedAvailableModules.filter((m) => !selectedModuleIds.has(m.id)),
    [normalizedAvailableModules, selectedModuleIds],
  );

  const catalogCategories = useMemo(() => buildCategories(selectableModules), [selectableModules]);

  const placedTemplateIds = useMemo(
    () => new Set(items.filter((i) => i.template_id).map((i) => i.template_id)),
    [items],
  );

  const readyTools = useMemo(
    () => normalizedToolTemplates.filter((t) => !placedTemplateIds.has(t.id)),
    [normalizedToolTemplates, placedTemplateIds],
  );

  const catalogModules = useMemo(
    () => selectedCategoryId ? selectableModules.filter((m) => m.category_id === selectedCategoryId) : [],
    [selectableModules, selectedCategoryId],
  );

  // ── React Flow nodes & edges ──────────────────────────────────────────────

  const flowNodes = useMemo(() =>
    items.map((item, index) => {
      const isWc   = item.type === 'wordcloud';
      const isPoll = item.type === 'poll';
      const isGenericTool = item.type === 'outil';

      let nodeLabel;
      if (isWc) {
        nodeLabel = (
          <div className="min-w-[220px] max-w-[220px]">
            <div className="flex items-start gap-2">
              <CloudIcon className="mt-0.5 h-4 w-4 shrink-0 text-amber-600" />
              <span className="text-[12px] font-semibold leading-snug text-amber-900">
                {`${item.position}. ${item.wc_title || 'Nuage de mots'}`}
              </span>
            </div>
            {item.wc_questions?.length > 0 && (
              <p className="mt-2 text-[11px] text-amber-700 italic line-clamp-2">
                « {item.wc_questions[0]} »
                {item.wc_questions.length > 1 && (
                  <span className="not-italic font-semibold ml-1">+{item.wc_questions.length - 1}</span>
                )}
              </p>
            )}
            {item.wc_duration && (
              <p className="mt-1 text-[11px] text-amber-600 flex items-center gap-1">
                <svg xmlns="http://www.w3.org/2000/svg" className="h-3 w-3 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                {item.wc_duration} min
              </p>
            )}
          </div>
        );
      } else if (isPoll) {
        nodeLabel = (
          <div className="min-w-[220px] max-w-[220px]">
            <div className="flex items-start gap-2">
              <PollIcon className="mt-0.5 h-4 w-4 shrink-0 text-teal-600" />
              <span className="text-[12px] font-semibold leading-snug text-teal-900">
                {`${item.position}. ${pollTitle(item)}`}
              </span>
            </div>
            {pollChoices(item).length > 0 && (
              <ul className="mt-2 space-y-0.5">
                {pollChoices(item).slice(0, 3).map((c, i) => (
                  <li key={i} className="text-[10px] text-teal-700 flex items-center gap-1">
                    <span className="inline-block h-1.5 w-1.5 rounded-full bg-teal-400 shrink-0" />
                    {c}
                  </li>
                ))}
                {pollChoices(item).length > 3 && (
                  <li className="text-[10px] text-teal-500 italic">+{pollChoices(item).length - 3} choix…</li>
                )}
              </ul>
            )}
          </div>
        );
      } else if (isGenericTool) {
        nodeLabel = (
          <div className="min-w-[220px] max-w-[220px]">
            <div className="flex items-start gap-2">
              <GenericToolIcon className="mt-0.5 h-4 w-4 shrink-0 text-slate-600" />
              <span className="text-[12px] font-semibold leading-snug text-slate-900">
                {`${item.position}. ${genericToolTitle(item)}`}
              </span>
            </div>
            <div className="mt-2">
              <GenericToolStatusBadge />
            </div>
          </div>
        );
      } else {
        nodeLabel = (
          <div className="min-w-[220px] max-w-[220px]">
            <div className="flex items-start gap-2">
              <OpenBookIcon className="mt-0.5 h-4 w-4 shrink-0" />
              <span className="text-[12px] font-semibold leading-snug text-slate-900">
                {`${item.position}. ${item.title}`}
              </span>
            </div>
            <div className="mt-3 grid grid-cols-3 gap-2">
              <Metric label="Leçons"    value={item.lesson_count} />
              <Metric label="Questions" value={item.question_count} />
              <Metric label="Durée"     value={item.duration_label} />
            </div>
          </div>
        );
      }

      return {
        id: String(item.id),
        data: { label: nodeLabel },
        position: nodePosition(index),
        sourcePosition: isWrapToNextRow(index, items.length) ? Position.Bottom : Position.Right,
        targetPosition: isWrapFromPreviousRow(index) ? Position.Top : Position.Left,
        draggable: false,
        selectable: false,
        style: isWc
          ? { border: '2px solid #f59e0b', borderRadius: '8px', width: 260, padding: '12px 14px', background: '#fffbeb' }
          : isPoll
            ? { border: '2px solid #0d9488', borderRadius: '8px', width: 260, padding: '12px 14px', background: '#f0fdfa' }
            : isGenericTool
              ? { border: '2px solid #94a3b8', borderRadius: '8px', width: 260, padding: '12px 14px', background: '#f8fafc' }
              : { border: '2px solid #004461', borderRadius: '8px', width: 260, padding: '12px 14px', background: '#fff' },
      };
    }),
    [items],
  );

  const flowEdges = useMemo(() =>
    items.slice(1).map((item, index) => ({
      id: `edge-${items[index].id}-${item.id}`,
      source: String(items[index].id),
      target: String(item.id),
      type: 'smoothstep',
      pathOptions: { borderRadius: 22, offset: 40 },
      markerEnd: { type: MarkerType.ArrowClosed, color: '#e94d2a' },
      style: { stroke: '#e94d2a', strokeWidth: 3 },
    })),
    [items],
  );

  // ── Fit view ──────────────────────────────────────────────────────────────

  const runFitView = useCallback(() => {
    if (!flowInstance || !canvasRef.current) return;
    if (canvasRef.current.clientWidth === 0 || canvasRef.current.clientHeight === 0) return;
    window.requestAnimationFrame(() => window.requestAnimationFrame(() => {
      flowInstance.fitView({ padding: 0.3, duration: 250 });
    }));
  }, [flowInstance]);

  useEffect(() => { if (flowInstance) runFitView(); }, [flowInstance, runFitView, items.length]);

  useEffect(() => {
    if (!flowInstance || !canvasRef.current || typeof ResizeObserver === 'undefined') return;
    const observer = new ResizeObserver(() => runFitView());
    observer.observe(canvasRef.current);
    return () => observer.disconnect();
  }, [flowInstance, runFitView]);

  // ── Mutations ─────────────────────────────────────────────────────────────

  const addModule = () => {
    setAddError('');
    const moduleId = toPositiveInt(newModuleId);
    if (!moduleId) return;
    if (selectedModuleIds.has(moduleId)) {
      setAddError('Cette formation est déjà dans le parcours.');
      setNewModuleId('');
      return;
    }
    const mod = availableModuleMap.get(moduleId);
    if (!mod) { setAddError('Formation introuvable.'); return; }
    setItems((current) => renumber([
      ...current,
      { type: 'module', id: mod.id, title: mod.title, position: current.length + 1,
        lesson_count: mod.lesson_count, question_count: mod.question_count, duration_label: mod.duration_label },
    ]));
    setNewModuleId('');
  };

  const addWordCloud = ({ wc_title, wc_questions, wc_duration }) => {
    setItems((current) => renumber([
      ...current,
      { type: 'wordcloud', id: newWcId(), position: current.length + 1, wc_title, wc_questions: wc_questions ?? [], wc_duration: wc_duration ?? null },
    ]));
    setShowWcForm(false);
  };

  const addPoll = ({ poll_question, poll_choices, poll_questions, poll_duration }) => {
    setItems((current) => renumber([
      ...current,
      {
        type: 'poll',
        id: newPollId(),
        position: current.length + 1,
        poll_question,
        poll_choices,
        poll_questions: poll_questions ?? [],
        poll_duration: poll_duration ?? null,
      },
    ]));
    setShowPollForm(false);
  };

  const updateItem = (itemId, patch) => {
    setItems((current) => current.map((i) => String(i.id) === String(itemId) ? { ...i, ...patch } : i));
    setEditingItemId(null);
  };

  const removeItem = (itemId) => {
    setItems((current) => renumber(current.filter((i) => String(i.id) !== String(itemId))));
  };

  const moveItem = (itemId, delta) => {
    setItems((current) => {
      const index = current.findIndex((i) => String(i.id) === String(itemId));
      if (index < 0) return current;
      const swapIndex = index + delta;
      if (swapIndex < 0 || swapIndex >= current.length) return current;
      const next = [...current];
      [next[index], next[swapIndex]] = [next[swapIndex], next[index]];
      return renumber(next);
    });
  };

  const insertModuleAt = (moduleId, targetIndex) => {
    setAddError('');
    if (selectedModuleIds.has(moduleId)) {
      setAddError('Cette formation est déjà dans le parcours.');
      return;
    }
    const mod = availableModuleMap.get(moduleId);
    if (!mod) { setAddError('Formation introuvable.'); return; }
    setItems((current) => {
      const next = [...current];
      const at = Math.max(0, Math.min(targetIndex, next.length));
      next.splice(at, 0, {
        type: 'module', id: mod.id, title: mod.title, position: 0,
        lesson_count: mod.lesson_count, question_count: mod.question_count, duration_label: mod.duration_label,
      });
      return renumber(next);
    });
  };

  const moveItemTo = (itemId, targetIndex) => {
    setItems((current) => {
      const fromIndex = current.findIndex((i) => String(i.id) === String(itemId));
      if (fromIndex < 0) return current;
      const next = [...current];
      const [moved] = next.splice(fromIndex, 1);
      let at = targetIndex;
      if (fromIndex < targetIndex) at -= 1;
      at = Math.max(0, Math.min(at, next.length));
      next.splice(at, 0, moved);
      return renumber(next);
    });
  };

  // ── Outils numériques : modèles construits en amont, placés dans le plan ───

  const placeReadyTool = (templateId, targetIndex) => {
    const tpl = normalizedToolTemplates.find((t) => String(t.id) === String(templateId));
    if (!tpl) return;
    let newItem;
    if (tpl.type === 'wordcloud') {
      newItem = { type: 'wordcloud', id: newWcId(), template_id: tpl.id, position: 0, wc_title: tpl.wc_title, wc_questions: tpl.wc_questions, wc_duration: tpl.wc_duration };
    } else if (tpl.type === 'poll') {
      newItem = { type: 'poll', id: newPollId(), template_id: tpl.id, position: 0, poll_questions: tpl.poll_questions, poll_duration: tpl.poll_duration };
    } else {
      newItem = {
        ...tpl,
        id: newGenericToolId(tpl.outil),
        template_id: tpl.id,
        position: 0,
        configuration: { ...(tpl.configuration ?? {}) },
        execution_disponible: false,
      };
    }
    setItems((current) => {
      const next = [...current];
      const at = Math.max(0, Math.min(targetIndex, next.length));
      next.splice(at, 0, newItem);
      return renumber(next);
    });
  };

  // ── Drag and drop (catalogue → plan, réordonnancement du plan) ─────────────

  const handleCatalogDragStart = (e, moduleId) => {
    e.dataTransfer.effectAllowed = 'copy';
    e.dataTransfer.setData('text/plain', `module:${moduleId}`);
    setDragSource({ kind: 'module', id: moduleId });
  };

  const handleReadyToolDragStart = (e, toolId) => {
    e.dataTransfer.effectAllowed = 'copy';
    e.dataTransfer.setData('text/plain', `readytool:${toolId}`);
    setDragSource({ kind: 'readytool', id: toolId });
  };

  const handleCubeDragStart = (e, itemId) => {
    e.dataTransfer.effectAllowed = 'move';
    e.dataTransfer.setData('text/plain', `item:${itemId}`);
    setDragSource({ kind: 'item', id: itemId });
  };

  const handleCubeDragOver = (e, index) => {
    e.preventDefault();
    e.stopPropagation();
    e.dataTransfer.dropEffect = dragSource?.kind === 'item' ? 'move' : 'copy';
    const rect = e.currentTarget.getBoundingClientRect();
    const isBefore = (e.clientX - rect.left) < rect.width / 2;
    setDropTargetIndex(isBefore ? index : index + 1);
  };

  const handlePlanDragOver = (e) => {
    e.preventDefault();
    e.dataTransfer.dropEffect = dragSource?.kind === 'item' ? 'move' : 'copy';
    if (dropTargetIndex === null) setDropTargetIndex(items.length);
  };

  const handleDragEnd = () => {
    setDragSource(null);
    setDropTargetIndex(null);
  };

  const handlePlanDrop = (e) => {
    e.preventDefault();
    const raw = e.dataTransfer.getData('text/plain');
    const [kind, id] = raw.split(':');
    const targetIndex = dropTargetIndex ?? items.length;

    if (kind === 'module') {
      insertModuleAt(toPositiveInt(id), targetIndex);
    } else if (kind === 'item') {
      moveItemTo(id, targetIndex);
    } else if (kind === 'readytool') {
      placeReadyTool(id, targetIndex);
    }

    handleDragEnd();
  };

  const resetSimulationActivity = () => {
    if (!isSimulation) return;
    setItems([]);
    setNewModuleId('');
    setAddError('');
    setSaveError('');
    setSimulationFeedback('');
    setSimulationAttempts(0);
    setShowWcForm(false);
    setShowPollForm(false);
    setEditingItemId(null);
    const titleInput = document.querySelector('[name="title"]');
    const descriptionInput = document.querySelector('[name="description"]');
    if (titleInput) titleInput.value = '';
    if (descriptionInput) descriptionInput.value = '';
    window.localStorage.removeItem('oneduc_training_path_creation');
    window.scrollTo({ top: 0, behavior: 'smooth' });
  };

  const simulationPayload = () => ({
    title:       document.querySelector('[name="title"]')?.value ?? '',
    description: document.querySelector('[name="description"]')?.value ?? '',
    items: items.map((item) => {
      if (item.type === 'module')    return { type: 'module',    module_id: item.id, position: item.position };
      if (item.type === 'wordcloud') return { type: 'wordcloud', wc_title: item.wc_title, wc_questions: item.wc_questions ?? [], wc_duration: item.wc_duration ?? null, position: item.position };
      if (item.type === 'poll')      return { type: 'poll', poll_questions: item.poll_questions ?? [], poll_duration: item.poll_duration ?? null, position: item.position };
      return genericToolPayload(item);
    }),
  });

  const stopSimulationActivity = () => {
    if (!isSimulation) return;
    const payload = {
      ...simulationPayload(),
      stopped: true,
      attempts: Math.max(simulationAttempts, 3),
    };
    window.localStorage.setItem('oneduc_training_path_creation', JSON.stringify(payload));
    if (storeUrl) {
      window.location.href = storeUrl;
    }
  };

  // ── Save ──────────────────────────────────────────────────────────────────

  const handleSave = async () => {
    setSaveError('');
    setSimulationFeedback('');
    setSaving(true);
    try {
      const payload = simulationPayload();

      if (isSimulation) {
        if (!payload.title.trim()) {
          setSimulationAttempts((current) => current + 1);
          setSimulationFeedback('Le titre du parcours est obligatoire avant de créer le parcours.');
          return;
        }

        const simulationError = validateSimulationPathItems(items);
        if (simulationError) {
          setSimulationAttempts((current) => current + 1);
          setSimulationFeedback(simulationError);
          return;
        }

        window.localStorage.setItem('oneduc_training_path_creation', JSON.stringify(payload));

        if (storeUrl) {
          window.location.href = storeUrl;
        }

        return;
      }

      const resp = await fetch(storeUrl, {
        method,
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
        body: JSON.stringify(payload),
      });
      const data = await resp.json();
      if (!resp.ok) {
        setSaveError(data.message ?? (data.errors ? Object.values(data.errors).flat().join(' — ') : 'Erreur lors de la sauvegarde.'));
      } else {
        window.location.href = data.redirect_url;
      }
    } catch {
      setSaveError('Erreur réseau. Veuillez réessayer.');
    } finally {
      setSaving(false);
    }
  };

  // ── Render ────────────────────────────────────────────────────────────────

  if (isSimulation && !isPreview) {
    return (
      <div className="grid gap-4 xl:grid-cols-[minmax(0,1fr)_18rem]">
        <SimulationFeedbackModal
          message={simulationFeedback}
          attemptsLeft={Math.max(0, 3 - simulationAttempts)}
          canStop={simulationAttempts >= 3}
          onClose={() => setSimulationFeedback('')}
          onRestart={resetSimulationActivity}
          onStop={stopSimulationActivity}
        />

        <div className="space-y-4">
          {showWcForm && (
            <WordCloudForm onAdd={addWordCloud} onCancel={() => setShowWcForm(false)} />
          )}
          {showPollForm && (
            <PollForm onAdd={addPoll} onCancel={() => setShowPollForm(false)} />
          )}

          <div className="bg-white rounded-[20px] shadow-md px-8 py-6 space-y-4">
            <h2 className="text-base font-semibold text-gray-800">Étapes du parcours</h2>

            <div className="flex flex-col gap-3 md:flex-row md:items-end">
              <div className="w-full">
                <select
                  value={newModuleId}
                  onChange={(e) => { setAddError(''); setNewModuleId(e.target.value); }}
                  className="w-full rounded-[10px] border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#E94D2A]"
                >
                  <option value="">— Sélectionner une formation à ajouter —</option>
                  {selectableModules.map((m) => (
                    <option key={`opt-${m.id}`} value={m.id}>{m.title}</option>
                  ))}
                </select>
                {addError && <p className="mt-1 text-sm text-red-600">{addError}</p>}
              </div>
              <button type="button" onClick={addModule} disabled={!newModuleId}
                className="inline-flex items-center gap-2 rounded-[10px] bg-[#004461] px-4 py-2 text-sm font-bold text-white hover:opacity-90 disabled:opacity-40 disabled:cursor-not-allowed transition md:shrink-0">
                <svg xmlns="http://www.w3.org/2000/svg" className="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M12 4v16m8-8H4" />
                </svg>
                Ajouter une formation
              </button>
            </div>
          </div>

          <div className="bg-white rounded-[20px] shadow-md overflow-hidden">
            {items.length === 0 ? (
              <div className="px-8 py-10 text-center text-gray-400">
                Ajoutez des formations ci-dessus.
              </div>
            ) : (
              <table className="min-w-full text-left text-sm text-gray-800">
                <thead className="bg-[#004461] text-xs uppercase text-white">
                  <tr>
                    <th className="w-[64px] px-4 py-3 text-center">#</th>
                    <th className="px-4 py-3">Étape</th>
                    <th className="px-4 py-3">Détail</th>
                    <th className="px-4 py-3 text-right">Actions</th>
                  </tr>
                </thead>
                <tbody>
                  {items.map((item, index) => {
                    const isWc = item.type === 'wordcloud';
                    const isPoll = item.type === 'poll';
                    const isGenericTool = item.type === 'outil';

                    return (
                    <tr key={item.id} className={`${index % 2 === 0 ? 'bg-white' : isWc ? 'bg-amber-50/40' : isPoll ? 'bg-teal-50/40' : 'bg-slate-50/40'} border-t transition-colors`}>
                      <td className="px-4 py-3 text-center font-bold text-[#004461]">{item.position}</td>
                      <td className="px-4 py-3">
                        <div className="flex items-center gap-2">
                          {isWc
                            ? <span className="inline-flex items-center gap-1 text-xs font-medium px-2 py-0.5 rounded-full bg-amber-100 text-amber-700"><CloudIcon className="h-3 w-3" />Nuage de mots</span>
                            : isPoll
                              ? <span className="inline-flex items-center gap-1 text-xs font-medium px-2 py-0.5 rounded-full bg-teal-100 text-teal-700"><PollIcon className="h-3 w-3" />Sondage</span>
                              : isGenericTool
                                ? <span className="inline-flex items-center gap-1 text-xs font-medium px-2 py-0.5 rounded-full bg-slate-100 text-slate-700"><GenericToolIcon className="h-3 w-3" />{item.outil_label || item.outil}</span>
                                : <span className="inline-flex items-center gap-1 text-xs font-medium px-2 py-0.5 rounded-full bg-blue-100 text-[#004461]"><OpenBookIcon className="h-3 w-3" />Formation</span>
                          }
                          <span className="font-medium">
                            {isWc ? item.wc_title : isPoll ? pollTitle(item) : isGenericTool ? genericToolTitle(item) : item.title}
                          </span>
                        </div>
                      </td>
                      <td className="px-4 py-3 text-gray-500 text-xs max-w-[260px] truncate">
                        {item.type === 'wordcloud'
                          ? item.wc_questions?.length > 0 ? `« ${item.wc_questions[0]} »${item.wc_questions.length > 1 ? ` +${item.wc_questions.length - 1}` : ''}` : '—'
                          : item.type === 'poll'
                            ? pollChoices(item).join(' / ')
                            : item.type === 'outil'
                              ? <GenericToolStatusBadge />
                              : `${item.lesson_count} leç. — ${item.duration_label}`
                        }
                      </td>
                      <td className="px-4 py-3 text-right">
                        <div className="inline-flex gap-2">
                          <button type="button" onClick={() => moveItem(item.id, -1)} disabled={index === 0}
                            aria-label="Monter"
                            className="inline-flex h-8 w-8 items-center justify-center rounded border border-gray-300 text-[#004461] hover:bg-gray-50 disabled:opacity-30 disabled:cursor-not-allowed">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" className="h-4 w-4">
                              <path d="m18 15-6-6-6 6" />
                            </svg>
                          </button>
                          <button type="button" onClick={() => moveItem(item.id, +1)} disabled={index === items.length - 1}
                            aria-label="Descendre"
                            className="inline-flex h-8 w-8 items-center justify-center rounded border border-gray-300 text-[#004461] hover:bg-gray-50 disabled:opacity-30 disabled:cursor-not-allowed">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" className="h-4 w-4">
                              <path d="m6 9 6 6 6-6" />
                            </svg>
                          </button>
                          <button type="button" onClick={() => removeItem(item.id)}
                            className="rounded border border-gray-300 px-2 py-1 text-xs text-gray-600 hover:border-red-300 hover:text-red-600 transition">
                            Retirer
                          </button>
                        </div>
                      </td>
                    </tr>
                    );
                  })}
                </tbody>
              </table>
            )}
          </div>

          <div className="overflow-hidden rounded-[20px] border border-gray-200 bg-white shadow-md">
            <div ref={canvasRef} className="h-[360px] w-full">
              <ReactFlow
                nodes={flowNodes}
                edges={flowEdges}
                onInit={(instance) => setFlowInstance(instance)}
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

          <div className="flex items-center justify-end gap-4 py-2">
            <button type="button" onClick={handleSave}
              disabled={saving || items.length === 0}
              className="inline-flex items-center gap-2 rounded-[10px] bg-[#E94D2A] px-6 py-3 text-sm font-bold text-white shadow hover:bg-[#cf4121] disabled:opacity-50 disabled:cursor-not-allowed transition">
              {saving ? (
                <svg className="h-4 w-4 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                  <circle className="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" strokeWidth="4" />
                  <path className="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z" />
                </svg>
              ) : (
                <svg xmlns="http://www.w3.org/2000/svg" className="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M5 13l4 4L19 7" />
                </svg>
              )}
              {saving ? 'Enregistrement…' : 'Créer le parcours'}
            </button>
          </div>
        </div>

        <aside className="space-y-3 self-start rounded-[20px] bg-white px-5 py-5 shadow-md xl:sticky xl:top-4">
          <h2 className="text-base font-semibold text-gray-800">Outils numériques</h2>
          <ToolButton label="Nuage de mots" className="bg-amber-500 hover:bg-amber-600" onClick={() => { setShowWcForm((v) => !v); setShowPollForm(false); }} />
          <ToolButton label="Sondage" className="bg-teal-600 hover:bg-teal-700" onClick={() => { setShowPollForm((v) => !v); setShowWcForm(false); }} />
          <ToolButton label="Roue aléatoire" className="bg-violet-600 hover:bg-violet-700" disabled />
          <ToolButton label="Échelle de positionnement" className="bg-indigo-600 hover:bg-indigo-700" disabled />
          <ToolButton label="Page collaborative" className="bg-cyan-600 hover:bg-cyan-700" disabled />
          <ToolButton label="Mur de question" className="bg-indigo-600 hover:bg-indigo-700" disabled />
        </aside>
      </div>
    );
  }

  if (isPreview) {
    return (
      <div className="space-y-4">
        <div className="bg-white rounded-[20px] shadow-md overflow-hidden">
          {items.length === 0 ? (
            <div className="px-8 py-10 text-center text-gray-400">Aucune étape dans ce parcours.</div>
          ) : (
            <table className="min-w-full text-left text-sm text-gray-800">
              <thead className="bg-[#004461] text-xs uppercase text-white">
                <tr>
                  <th className="w-[64px] px-4 py-3 text-center">#</th>
                  <th className="px-4 py-3">Étape</th>
                  <th className="px-4 py-3">Détail</th>
                </tr>
              </thead>
              <tbody>
                {items.map((item, index) => {
                  const isWc   = item.type === 'wordcloud';
                  const isPoll = item.type === 'poll';
                  const isGenericTool = item.type === 'outil';
                  const rowBg  = index % 2 === 0 ? 'bg-white' : isWc ? 'bg-amber-50/40' : isPoll ? 'bg-teal-50/40' : 'bg-slate-50/40';
                  return (
                    <tr key={item.id} className={`${rowBg} border-t transition-colors`}>
                      <td className="px-4 py-3 text-center font-bold text-[#004461]">{item.position}</td>
                      <td className="px-4 py-3">
                        <div className="flex items-center gap-2">
                          {isWc
                            ? <span className="inline-flex items-center gap-1 text-xs font-medium px-2 py-0.5 rounded-full bg-amber-100 text-amber-700"><CloudIcon className="h-3 w-3" />Nuage de mots</span>
                            : isPoll
                              ? <span className="inline-flex items-center gap-1 text-xs font-medium px-2 py-0.5 rounded-full bg-teal-100 text-teal-700"><PollIcon className="h-3 w-3" />Sondage</span>
                              : isGenericTool
                                ? <span className="inline-flex items-center gap-1 text-xs font-medium px-2 py-0.5 rounded-full bg-slate-100 text-slate-700"><GenericToolIcon className="h-3 w-3" />{item.outil_label || item.outil}</span>
                                : <span className="inline-flex items-center gap-1 text-xs font-medium px-2 py-0.5 rounded-full bg-blue-100 text-[#004461]"><OpenBookIcon className="h-3 w-3" />Formation</span>
                          }
                          <span className="font-medium">
                            {isWc ? item.wc_title : isPoll ? pollTitle(item) : isGenericTool ? genericToolTitle(item) : item.title}
                          </span>
                        </div>
                      </td>
                      <td className="px-4 py-3 text-gray-500 text-xs max-w-[260px] truncate">
                        {isWc
                          ? item.wc_questions?.length > 0 ? `« ${item.wc_questions[0]} »${item.wc_questions.length > 1 ? ` +${item.wc_questions.length - 1}` : ''}` : '—'
                          : isPoll
                            ? pollChoices(item).join(' / ')
                            : isGenericTool
                              ? <GenericToolStatusBadge />
                              : `${item.lesson_count} leç. — ${item.duration_label}`
                        }
                      </td>
                    </tr>
                  );
                })}
              </tbody>
            </table>
          )}
        </div>

        <div className="overflow-hidden rounded-[20px] border border-gray-200 bg-white shadow-md">
          <div ref={canvasRef} className="h-[360px] w-full">
            <ReactFlow
              nodes={flowNodes}
              edges={flowEdges}
              onInit={(instance) => setFlowInstance(instance)}
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

  return (
    <div className="grid gap-4 xl:grid-cols-[22rem_minmax(0,1fr)]">

      {/* Colonne gauche : catalogue de formations */}
      <div className="space-y-4 xl:sticky xl:top-4 xl:self-start">
        <div className="overflow-hidden rounded-[20px] bg-white shadow-md">
          {/* Bandeau : choix de catégorie */}
          <div className="border-b border-gray-100 px-6 py-4">
            <h2 className="text-base font-semibold text-gray-800 mb-2">Catalogue</h2>
            <label className="block text-xs font-medium text-gray-700 mb-1">Catégorie</label>
            <select
              value={selectedCategoryId}
              onChange={(e) => { setAddError(''); setSelectedCategoryId(e.target.value); }}
              className="w-full rounded-[10px] border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#E94D2A]"
            >
              <option value="">— Choisir une catégorie —</option>
              {catalogCategories.map((c) => (
                <option key={`cat-${c.id}`} value={c.id}>{c.name} ({c.count})</option>
              ))}
            </select>
            {addError && <p className="mt-1 text-sm text-red-600">{addError}</p>}
          </div>

          {/* Fenêtre : formations de la catégorie choisie */}
          <div className="max-h-[420px] space-y-2 overflow-y-auto bg-slate-50 px-4 py-4">
            {!selectedCategoryId ? (
              <p className="py-6 text-center text-sm italic text-gray-400">Sélectionnez une catégorie pour afficher les formations disponibles.</p>
            ) : catalogModules.length === 0 ? (
              <p className="py-6 text-center text-sm italic text-gray-400">Toutes les formations de cette catégorie sont déjà dans le parcours.</p>
            ) : (
              catalogModules.map((m) => (
                <div
                  key={m.id}
                  draggable
                  onDragStart={(e) => handleCatalogDragStart(e, m.id)}
                  onDragEnd={handleDragEnd}
                  onClick={() => insertModuleAt(m.id, items.length)}
                  role="button"
                  tabIndex={0}
                  onKeyDown={(e) => { if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); insertModuleAt(m.id, items.length); } }}
                  className="cursor-grab select-none rounded-[10px] border border-gray-200 bg-white px-3 py-2.5 text-left shadow-sm transition hover:border-[#E94D2A] hover:shadow-md active:cursor-grabbing"
                >
                  <div className="flex items-start gap-2">
                    <OpenBookIcon className="mt-0.5 h-4 w-4 shrink-0 text-[#004461]" />
                    <span className="text-sm font-semibold leading-snug text-slate-900">{m.title}</span>
                  </div>
                  <div className="mt-1.5 flex gap-3 text-[11px] text-gray-500">
                    <span>{m.lesson_count} leç.</span>
                    <span>{m.duration_label}</span>
                  </div>
                </div>
              ))
            )}
          </div>
        </div>

        <div className="space-y-3 rounded-[20px] bg-white px-5 py-5 shadow-md">
          <div className="flex items-center justify-between gap-2">
            <h2 className="text-base font-semibold text-gray-800">Outils numériques</h2>
            <div className="flex shrink-0 gap-2 text-xs font-semibold">
              <a href={wordcloudUrl} target="_blank" rel="noopener" className="text-amber-600 hover:underline">+ Nuage de mots</a>
              <a href={sondageUrl} target="_blank" rel="noopener" className="text-teal-600 hover:underline">+ Sondage</a>
            </div>
          </div>

          {readyTools.length === 0 ? (
            <p className="text-sm italic text-gray-400">
              Aucun outil réutilisable pour l'instant. Créez un
              {' '}<a href={wordcloudUrl} target="_blank" rel="noopener" className="text-orangeone hover:underline">nuage de mots</a>
              {' '}ou un <a href={sondageUrl} target="_blank" rel="noopener" className="text-orangeone hover:underline">sondage</a> sans
              choisir de groupe, puis revenez ici pour le glisser dans le plan.
            </p>
          ) : (
            <div className="space-y-2">
              {readyTools.map((t) => {
                const isWc = t.type === 'wordcloud';
                const isPoll = t.type === 'poll';
                const isGenericTool = t.type === 'outil';
                return (
                  <div
                    key={t.id}
                    draggable
                    onDragStart={(e) => handleReadyToolDragStart(e, t.id)}
                    onDragEnd={handleDragEnd}
                    onClick={() => placeReadyTool(t.id, items.length)}
                    role="button"
                    tabIndex={0}
                    onKeyDown={(e) => { if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); placeReadyTool(t.id, items.length); } }}
                    className={`cursor-grab select-none rounded-[10px] border px-3 py-2.5 text-left shadow-sm transition active:cursor-grabbing ${isWc ? 'border-amber-200 bg-amber-50 hover:border-amber-400' : isPoll ? 'border-teal-200 bg-teal-50 hover:border-teal-400' : 'border-slate-200 bg-slate-50 hover:border-slate-400'}`}
                  >
                    <div className="flex items-start gap-2">
                      {isWc
                        ? <CloudIcon className="mt-0.5 h-4 w-4 shrink-0 text-amber-600" />
                        : isPoll
                          ? <PollIcon className="mt-0.5 h-4 w-4 shrink-0 text-teal-600" />
                          : <GenericToolIcon className="mt-0.5 h-4 w-4 shrink-0 text-slate-600" />}
                      <span className="truncate text-sm font-semibold text-slate-900">
                        {isWc ? (t.wc_title || 'Nuage de mots') : isPoll ? pollTitle(t) : genericToolTitle(t)}
                      </span>
                    </div>
                    {isGenericTool ? (
                      <div className="mt-1.5"><GenericToolStatusBadge /></div>
                    ) : (
                      <p className="mt-1 text-[11px] text-gray-500">
                        {isWc ? `${t.wc_questions?.length ?? 0} question(s)` : `${t.poll_questions?.length ?? 0} question(s)`}
                      </p>
                    )}
                  </div>
                );
              })}
            </div>
          )}
        </div>
      </div>

      {/* Colonne droite : le plan du parcours */}
      <div className="space-y-4">
        <div className="bg-white rounded-[20px] shadow-md px-6 py-6 space-y-4">
          <div className="flex items-center justify-between">
            <h2 className="text-base font-semibold text-gray-800">Plan du parcours</h2>
            <span className="text-xs text-gray-400">Glissez une formation ou un outil ici</span>
          </div>

          <div
            onDragOver={handlePlanDragOver}
            onDrop={handlePlanDrop}
            onDragLeave={(e) => { if (e.currentTarget === e.target) setDropTargetIndex(null); }}
            className={`flex min-h-[180px] flex-wrap items-start gap-3 rounded-[14px] border-2 border-dashed p-4 transition-colors ${dragSource ? 'border-[#E94D2A] bg-orange-50/40' : 'border-gray-200'}`}
          >
            {items.length === 0 && (
              <div className="flex w-full items-center justify-center py-10 text-sm text-gray-400">
                Glissez ou cliquez une formation ou un outil numérique pour commencer.
              </div>
            )}
            {items.map((item, index) => (
              <React.Fragment key={item.id}>
                {dropTargetIndex === index && <div className="my-1 h-[100px] w-1 shrink-0 self-center rounded-full bg-[#E94D2A]" />}
                <CubeCard
                  item={item}
                  isEditing={editingItemId === item.id}
                  onDragStart={(e) => handleCubeDragStart(e, item.id)}
                  onDragOver={(e) => handleCubeDragOver(e, index)}
                  onDragEnd={handleDragEnd}
                  onRemove={() => removeItem(item.id)}
                  onOpenEdit={() => setEditingItemId(item.id)}
                  onCloseEdit={() => setEditingItemId(null)}
                  onUpdate={(data) => updateItem(item.id, data)}
                />
                {index < items.length - 1 && (
                  <ChevronRightIcon className="mt-8 h-5 w-5 shrink-0 text-[#E94D2A]" />
                )}
              </React.Fragment>
            ))}
            {dropTargetIndex === items.length && items.length > 0 && (
              <div className="my-1 h-[100px] w-1 shrink-0 self-center rounded-full bg-[#E94D2A]" />
            )}
          </div>
        </div>

        {items.length > 0 && !items.some(i => i.type === 'module') && (
          <div className="flex items-start gap-3 rounded-[10px] border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
            <svg xmlns="http://www.w3.org/2000/svg" className="h-5 w-5 shrink-0 mt-0.5 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <span>
              Ce parcours ne contient pas encore de formation. Vous pouvez l'enregistrer tel quel, mais il ne pourra pas être utilisé dans un groupe. Ajoutez au moins une étape de type <strong>formation</strong> pour débloquer cette fonctionnalité.
            </span>
          </div>
        )}

        <div className="flex items-center justify-end gap-4 py-2">
          {saveError && <p className="text-sm text-red-600">{saveError}</p>}
          <button type="button" onClick={handleSave}
            disabled={saving || items.length === 0}
            className="inline-flex items-center gap-2 rounded-[10px] bg-[#E94D2A] px-6 py-3 text-sm font-bold text-white shadow hover:bg-[#cf4121] disabled:opacity-50 disabled:cursor-not-allowed transition">
            {saving ? (
              <svg className="h-4 w-4 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle className="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" strokeWidth="4" />
                <path className="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z" />
              </svg>
            ) : (
              <svg xmlns="http://www.w3.org/2000/svg" className="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M5 13l4 4L19 7" />
              </svg>
            )}
            {saving ? 'Enregistrement…' : 'Enregistrer la formation'}
          </button>
        </div>
      </div>
    </div>
  );
}

// ─── Mount ────────────────────────────────────────────────────────────────────

function mountParcoursBuilder() {
  document.querySelectorAll('[data-parcours-builder]').forEach((mount) => {
    if (mount.dataset.builderMounted === '1') return;
    let initialItems = parseJsonDataset(mount, 'selectedItems', []);

    if (initialItems.length === 0 && mount.dataset.localStorageKey) {
      try {
        const storedPayload = JSON.parse(window.localStorage.getItem(mount.dataset.localStorageKey) || '{}');
        if (Array.isArray(storedPayload.items)) {
          initialItems = storedPayload.items;
        }
      } catch {
        initialItems = [];
      }
    }

    createRoot(mount).render(
      <ParcoursBuilder
        availableModules={parseJsonDataset(mount, 'availableModules', [])}
        toolTemplates={parseJsonDataset(mount, 'toolTemplates', [])}
        wordcloudUrl={mount.dataset.wordcloudUrl ?? ''}
        sondageUrl={mount.dataset.sondageUrl ?? ''}
        initialItems={initialItems}
        csrfToken={mount.dataset.csrfToken ?? ''}
        storeUrl={mount.dataset.storeUrl ?? ''}
        method={mount.dataset.method ?? 'POST'}
        mode={mount.dataset.mode ?? 'edit'}
      />,
    );
    mount.dataset.builderMounted = '1';
  });
}

if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', mountParcoursBuilder);
} else {
  mountParcoursBuilder();
}
