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

function normalizeSections(rawSections) {
  if (!Array.isArray(rawSections)) return [];

  return rawSections
    .map((rawSection, sectionIndex) => {
      const sectionId = toPositiveInt(rawSection?.id) || sectionIndex + 1;
      const title =
        String(rawSection?.title ?? rawSection?.section_title ?? '').trim() || `Section ${sectionIndex + 1}`;
      const description = String(rawSection?.description ?? '').trim();
      const rawLectures = Array.isArray(rawSection?.lectures) ? rawSection.lectures : [];

      const lectures = rawLectures
        .map((rawLecture, lectureIndex) => {
          const lectureId = toPositiveInt(rawLecture?.id);
          if (!lectureId) return null;

          return {
            id: lectureId,
            title:
              String(rawLecture?.title ?? rawLecture?.lecture_title ?? '').trim() ||
              `Leçon #${lectureId}`,
            position: toPositiveInt(rawLecture?.position) || lectureIndex + 1,
            isEnabled: rawLecture?.is_enabled !== false,
            slides: Math.max(0, Number(rawLecture?.slides ?? 0) || 0),
            questions: Math.max(0, Number(rawLecture?.questions ?? 0) || 0),
            toggleUrl: String(rawLecture?.toggle_url ?? '').trim(),
            moveUpUrl: String(rawLecture?.move_up_url ?? '').trim(),
            moveDownUrl: String(rawLecture?.move_down_url ?? '').trim(),
          };
        })
        .filter(Boolean)
        .sort((a, b) => a.position - b.position || a.id - b.id)
        .map((lecture, lectureIndex) => ({
          ...lecture,
          orderInSection: lectureIndex + 1,
        }));

      return {
        id: sectionId,
        title,
        description,
        order: sectionIndex + 1,
        lectures,
      };
    })
    .filter((section) => section.lectures.length > 0);
}

function paletteFromState(isEnabled) {
  if (isEnabled) {
    return {
      border: '#01c69c',
      background: '#ecfffa',
      text: '#005f4b',
      edge: '#01c69c',
      badge: 'Active',
    };
  }

  return {
    border: '#9ca3af',
    background: '#f9fafb',
    text: '#374151',
    edge: '#9ca3af',
    badge: 'Inactive',
  };
}

function toLocalActionPath(url) {
  const raw = String(url ?? '').trim();
  if (!raw) return '';

  try {
    const parsed = new URL(raw, window.location.origin);
    return `${parsed.pathname}${parsed.search}${parsed.hash}`;
  } catch {
    if (raw.startsWith('/')) return raw;
    return `/${raw}`;
  }
}

function GroupLessonFlow({ sections = [], csrfToken = '' }) {
  const normalizedSections = useMemo(() => normalizeSections(sections), [sections]);
  const [flowInstance, setFlowInstance] = useState(null);
  const [activeLectureId, setActiveLectureId] = useState(null);
  const canvasRef = useRef(null);

  const stats = useMemo(() => {
    const lectures = normalizedSections.flatMap((section) => section.lectures);
    const active = lectures.filter((lecture) => lecture.isEnabled).length;
    return {
      total: lectures.length,
      active,
      inactive: lectures.length - active,
    };
  }, [normalizedSections]);

  const flowSections = useMemo(
    () =>
      normalizedSections
        .map((section) => ({
          ...section,
          lectures: section.lectures.filter((lecture) => lecture.isEnabled),
        }))
        .filter((section) => section.lectures.length > 0),
    [normalizedSections],
  );

  const flowNodes = useMemo(() => {
    const sectionGapY = 200;
    const lectureGapX = 430;
    const questionOffsetX = 230;

    return flowSections.flatMap((section, sectionIndex) =>
      section.lectures.flatMap((lecture, lectureIndex) => {
        const palette = paletteFromState(lecture.isEnabled);
        const isSelected = lecture.id === activeLectureId;
        const questionCount = Math.max(0, Number(lecture.questions ?? 0) || 0);
        const lessonX = lectureIndex * lectureGapX;
        const lessonY = sectionIndex * sectionGapY + 20;

        return [
          {
            id: `lecture-${lecture.id}`,
            data: {
              lectureId: lecture.id,
              label: `${section.order}.${lecture.orderInSection} ${lecture.title}\n${palette.badge}`,
            },
            position: {
              x: lessonX,
              y: lessonY,
            },
            sourcePosition: Position.Right,
            targetPosition: Position.Left,
            draggable: false,
            selectable: true,
            style: {
              width: 250,
              border: `2px solid ${isSelected ? '#e94d2a' : palette.border}`,
              borderRadius: '10px',
              background: palette.background,
              color: palette.text,
              fontSize: '13px',
              fontWeight: 600,
              lineHeight: '1.4',
              whiteSpace: 'pre-line',
              padding: '10px 12px',
              boxShadow: isSelected ? '0 0 0 2px rgba(233,77,42,0.25)' : 'none',
            },
          },
          {
            id: `quiz-${lecture.id}`,
            data: {
              lectureId: lecture.id,
              label: String(questionCount),
            },
            position: {
              x: lessonX + questionOffsetX,
              y: lessonY + 12,
            },
            sourcePosition: Position.Right,
            targetPosition: Position.Left,
            draggable: false,
            selectable: true,
            style: {
              width: 52,
              height: 52,
              border: `2px solid ${isSelected ? '#e94d2a' : '#e94d2a'}`,
              borderRadius: '8px',
              background: '#fff7ed',
              color: '#9a3412',
              fontSize: '16px',
              fontWeight: 700,
              display: 'flex',
              alignItems: 'center',
              justifyContent: 'center',
              padding: 0,
              boxShadow: isSelected ? '0 0 0 2px rgba(233,77,42,0.25)' : 'none',
            },
          },
        ];
      }),
    );
  }, [flowSections, activeLectureId]);

  const flowEdges = useMemo(() => {
    const edges = [];

    flowSections.forEach((section, sectionIndex) => {
      section.lectures.forEach((lecture, lectureIndex) => {
        const palette = paletteFromState(lecture.isEnabled);
        const quizNodeId = `quiz-${lecture.id}`;

        edges.push({
          id: `edge-lecture-quiz-${lecture.id}`,
          source: `lecture-${lecture.id}`,
          target: quizNodeId,
          markerEnd: {
            type: MarkerType.ArrowClosed,
            color: '#e94d2a',
          },
          style: {
            stroke: '#e94d2a',
            strokeWidth: 2,
          },
        });

        if (lectureIndex === section.lectures.length - 1) return;

        const nextLecture = section.lectures[lectureIndex + 1];

        edges.push({
          id: `edge-quiz-next-${lecture.id}-${nextLecture.id}`,
          source: quizNodeId,
          target: `lecture-${nextLecture.id}`,
          markerEnd: {
            type: MarkerType.ArrowClosed,
            color: palette.edge,
          },
          style: {
            stroke: palette.edge,
            strokeWidth: 2.5,
          },
        });
      });

      const nextSection = flowSections[sectionIndex + 1];
      if (!nextSection || section.lectures.length === 0 || nextSection.lectures.length === 0) return;

      const sourceLecture = section.lectures[section.lectures.length - 1];
      const targetLecture = nextSection.lectures[0];

      edges.push({
        id: `edge-transition-${sourceLecture.id}-${targetLecture.id}`,
        source: `quiz-${sourceLecture.id}`,
        target: `lecture-${targetLecture.id}`,
        markerEnd: {
          type: MarkerType.ArrowClosed,
          color: '#e94d2a',
        },
        style: {
          stroke: '#e94d2a',
          strokeDasharray: '6 4',
          strokeWidth: 2,
        },
      });
    });

    return edges;
  }, [flowSections]);

  const submitAction = useCallback(
    (url) => {
      const action = toLocalActionPath(url);
      if (!action) return;

      const form = document.createElement('form');
      form.method = 'POST';
      form.action = action;

      const tokenField = document.createElement('input');
      tokenField.type = 'hidden';
      tokenField.name = '_token';
      tokenField.value = csrfToken;
      form.appendChild(tokenField);

      document.body.appendChild(form);
      form.submit();
    },
    [csrfToken],
  );

  const runFitView = useCallback(() => {
    if (!flowInstance || !canvasRef.current) return;
    if (canvasRef.current.clientWidth === 0 || canvasRef.current.clientHeight === 0) return;

    window.requestAnimationFrame(() => {
      window.requestAnimationFrame(() => {
        flowInstance.fitView({ padding: 0.25, duration: 250 });
      });
    });
  }, [flowInstance]);

  useEffect(() => {
    if (!flowInstance) return;
    runFitView();
  }, [flowInstance, runFitView, flowNodes.length]);

  useEffect(() => {
    if (!flowInstance || !canvasRef.current || typeof ResizeObserver === 'undefined') return;

    const observer = new ResizeObserver(() => runFitView());
    observer.observe(canvasRef.current);
    return () => observer.disconnect();
  }, [flowInstance, runFitView]);

  if (normalizedSections.length === 0) {
    return (
      <div className="rounded-[20px] border border-gray-100 bg-white p-6 shadow-md">
        <p className="text-gray-500">Aucune section trouvée pour ce module.</p>
      </div>
    );
  }

  return (
    <div className="space-y-6">
      {normalizedSections.map((section) => (
        <section key={`section-${section.id}`} className="rounded-[20px] border border-gray-100 bg-white p-6 shadow-md">
          <div className="mb-4 flex flex-wrap items-center justify-between gap-3">
            <div>
              <h4 className="font-raleway text-lg font-bold text-bleuone">{section.title}</h4>
              {section.description ? <p className="text-sm text-gray-600">{section.description}</p> : null}
            </div>
            <div className="flex items-center gap-3 text-sm text-gray-500">
              <span>{section.lectures.length} leçon(s)</span>
            </div>
          </div>

          <div className="overflow-x-auto rounded border border-gray-200">
            <table className="min-w-full text-left text-sm text-gray-800">
              <thead className="bg-gray-50 text-xs uppercase text-gray-600">
                <tr>
                  <th className="w-[90px] px-4 py-3 text-center">Ordre</th>
                  <th className="px-4 py-3">Leçon</th>
                  <th className="w-[120px] px-4 py-3 text-center">Diapositives</th>
                  <th className="w-[120px] px-4 py-3 text-center">Questions</th>
                  <th className="w-[120px] px-4 py-3 text-center">Etat</th>
                  <th className="px-4 py-3 text-right">Actions</th>
                </tr>
              </thead>

              <tbody className="divide-y divide-gray-100 bg-white">
                {section.lectures.length === 0 ? (
                  <tr>
                    <td colSpan={6} className="px-4 py-4 text-center text-gray-500">
                      Aucune leçon dans cette section.
                    </td>
                  </tr>
                ) : (
                  section.lectures.map((lecture, lectureIndex) => {
                    const isSelected = lecture.id === activeLectureId;
                    const palette = paletteFromState(lecture.isEnabled);
                    const canMoveUp = lectureIndex > 0;
                    const canMoveDown = lectureIndex < section.lectures.length - 1;

                    return (
                      <tr
                        key={`lecture-row-${lecture.id}`}
                        className={`${lecture.isEnabled ? '' : 'opacity-60'} ${isSelected ? 'bg-orange-50/40' : ''}`}
                      >
                        <td className="px-4 py-3 text-center font-semibold">{lecture.orderInSection}</td>

                        <td className="px-4 py-3">
                          <button
                            type="button"
                            className="text-left font-medium text-gray-900 hover:text-bleuone"
                            onClick={() => setActiveLectureId(lecture.id)}
                          >
                            {lecture.title}
                          </button>
                        </td>

                        <td className="px-4 py-3 text-center">{lecture.slides}</td>
                        <td className="px-4 py-3 text-center">{lecture.questions}</td>

                        <td className="px-4 py-3 text-center">
                          <span
                            className="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold"
                            style={{
                              backgroundColor: lecture.isEnabled ? '#d1fae5' : '#f3f4f6',
                              color: palette.text,
                            }}
                          >
                            {lecture.isEnabled ? 'Active' : 'Inactive'}
                          </span>
                        </td>

                        <td className="px-4 py-3 text-right">
                          <div className="inline-flex flex-wrap items-center justify-end gap-2">
                            <button
                              type="button"
                              onClick={() => submitAction(lecture.toggleUrl)}
                              className={`rounded px-3 py-1.5 text-xs font-semibold text-white ${
                                lecture.isEnabled ? 'bg-green-600 hover:bg-green-700' : 'bg-gray-500 hover:bg-gray-600'
                              }`}
                            >
                              {lecture.isEnabled ? 'Désactiver' : 'Activer'}
                            </button>

                            <button
                              type="button"
                              onClick={() => submitAction(lecture.moveUpUrl)}
                              disabled={!canMoveUp}
                              className="rounded border border-gray-300 px-3 py-1.5 text-xs text-bleuone disabled:cursor-not-allowed disabled:opacity-40"
                            >
                              Monter
                            </button>

                            <button
                              type="button"
                              onClick={() => submitAction(lecture.moveDownUrl)}
                              disabled={!canMoveDown}
                              className="rounded border border-gray-300 px-3 py-1.5 text-xs text-bleuone disabled:cursor-not-allowed disabled:opacity-40"
                            >
                              Descendre
                            </button>
                          </div>
                        </td>
                      </tr>
                    );
                  })
                )}
              </tbody>
            </table>
          </div>
        </section>
      ))}

      <section className="rounded-[20px] border border-gray-100 bg-white p-6 shadow-md">
        <div className="mb-4 flex flex-wrap items-center justify-between gap-3">
          <div>
            <h3 className="font-raleway text-xl font-bold text-bleuone">Parcours pédagogique visuel</h3>
            <p className="text-sm text-gray-600">
              Vue React Flow du cheminement des leçons par section. Le petit carré après chaque leçon indique le nombre de questions.
            </p>
          </div>

          <div className="flex flex-wrap items-center gap-2 text-xs">
            <span className="rounded border border-gray-200 bg-gray-50 px-2.5 py-1.5 text-gray-700">
              {stats.total} leçon(s)
            </span>
            <span className="rounded border border-green-200 bg-green-50 px-2.5 py-1.5 text-green-700">
              {stats.active} active(s)
            </span>
            <span className="rounded border border-gray-300 bg-gray-100 px-2.5 py-1.5 text-gray-700">
              {stats.inactive} inactive(s)
            </span>
          </div>
        </div>

        <div ref={canvasRef} className="h-[420px] w-full overflow-hidden rounded border border-gray-200">
          <ReactFlow
            nodes={flowNodes}
            edges={flowEdges}
            onInit={setFlowInstance}
            onNodeClick={(_event, node) => {
              const lectureId = toPositiveInt(node?.data?.lectureId);
              if (lectureId) setActiveLectureId(lectureId);
            }}
            nodesDraggable={false}
            nodesConnectable={false}
            elementsSelectable={true}
            zoomOnDoubleClick={false}
            proOptions={{ hideAttribution: true }}
          >
            <Controls showInteractive={false} position="bottom-right" />
            <Background gap={18} size={1} color="#e5e7eb" />
          </ReactFlow>
        </div>
      </section>
    </div>
  );
}

function mountGroupLessonFlow() {
  const mounts = document.querySelectorAll('[data-group-lesson-flow]');

  mounts.forEach((mount) => {
    if (mount.dataset.flowMounted === '1') return;

    const sections = parseJsonDataset(mount, 'sections', []);
    const csrfToken = String(mount.dataset.csrfToken || '');

    createRoot(mount).render(<GroupLessonFlow sections={sections} csrfToken={csrfToken} />);
    mount.dataset.flowMounted = '1';
  });
}

if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', mountGroupLessonFlow);
} else {
  mountGroupLessonFlow();
}
