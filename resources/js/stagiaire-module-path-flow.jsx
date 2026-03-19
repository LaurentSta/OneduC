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

function normalizeModules(rawModules) {
  if (!Array.isArray(rawModules)) return [];

  const out = [];
  const seen = new Set();

  rawModules.forEach((raw, index) => {
    const id = toPositiveInt(raw?.id);
    if (!id || seen.has(id)) return;

    seen.add(id);
    out.push({
      id,
      title: String(raw?.title ?? raw?.module_title ?? '').trim() || `Module #${id}`,
      order: toPositiveInt(raw?.order) || index + 1,
      status: String(raw?.status ?? 'not_started'),
      progress: Math.max(0, Math.min(100, Number(raw?.progress ?? 0) || 0)),
      estimatedDurationLabel: String(raw?.estimated_duration_label ?? '').trim(),
      detailUrl: String(raw?.detail_url ?? '').trim(),
    });
  });

  out.sort((a, b) => a.order - b.order || a.id - b.id);
  return out;
}

function paletteFromStatus(status) {
  if (status === 'completed') {
    return {
      border: '#01c69c',
      background: '#ecfffa',
      text: '#005f4b',
      edge: '#01c69c',
      badge: 'Terminé',
    };
  }

  if (status === 'in_progress') {
    return {
      border: '#E94D2A',
      background: '#fff2ec',
      text: '#7a2917',
      edge: '#E94D2A',
      badge: 'En cours',
    };
  }

  return {
    border: '#9ca3af',
    background: '#f9fafb',
    text: '#374151',
    edge: '#9ca3af',
    badge: 'À démarrer',
  };
}

function nodePosition(index) {
  const columns = 3;
  const col = index % columns;
  const row = Math.floor(index / columns);

  return {
    x: col * 300,
    y: row * 180 + 20,
  };
}

function StagiaireModulePathFlow({ modules = [] }) {
  const normalizedModules = useMemo(() => normalizeModules(modules), [modules]);

  const flowNodes = useMemo(
    () =>
      normalizedModules.map((module, index) => {
        const palette = paletteFromStatus(module.status);

        return {
          id: String(module.id),
          data: {
            label: `${module.order}. ${module.title}\n${palette.badge} - ${module.progress}%${module.estimatedDurationLabel ? `\nDuree estimee : ${module.estimatedDurationLabel}` : ''}`,
            url: module.detailUrl,
          },
          position: nodePosition(index),
          sourcePosition: Position.Right,
          targetPosition: Position.Left,
          draggable: false,
          selectable: true,
          style: {
            width: 250,
            border: `2px solid ${palette.border}`,
            borderRadius: '10px',
            background: palette.background,
            color: palette.text,
            fontSize: '13px',
            fontWeight: 600,
            lineHeight: '1.4',
            whiteSpace: 'pre-line',
            padding: '12px 14px',
          },
        };
      }),
    [normalizedModules],
  );

  const flowEdges = useMemo(
    () =>
      normalizedModules.slice(1).map((module, index) => {
        const prev = normalizedModules[index];
        const prevPalette = paletteFromStatus(prev.status);

        return {
          id: `edge-${prev.id}-${module.id}`,
          source: String(prev.id),
          target: String(module.id),
          markerEnd: {
            type: MarkerType.ArrowClosed,
            color: prevPalette.edge,
          },
          style: {
            stroke: prevPalette.edge,
            strokeWidth: 2,
          },
        };
      }),
    [normalizedModules],
  );

  const [instance, setInstance] = useState(null);
  const canvasRef = useRef(null);

  const runFitView = useCallback(() => {
    if (!instance || !canvasRef.current) return;
    if (canvasRef.current.clientWidth === 0 || canvasRef.current.clientHeight === 0) return;

    window.requestAnimationFrame(() => {
      window.requestAnimationFrame(() => {
        instance.fitView({ padding: 0.25, duration: 250 });
      });
    });
  }, [instance]);

  useEffect(() => {
    runFitView();
  }, [runFitView, flowNodes.length]);

  useEffect(() => {
    if (!instance || !canvasRef.current || typeof ResizeObserver === 'undefined') return;

    const observer = new ResizeObserver(() => runFitView());
    observer.observe(canvasRef.current);
    return () => observer.disconnect();
  }, [instance, runFitView]);

  if (normalizedModules.length === 0) return null;

  return (
    <section className="rounded-[20px] bg-white p-5 shadow-md" aria-labelledby="stagiaire-flow-title">
      <h3 id="stagiaire-flow-title" className="mb-1 text-lg font-bold text-bleuone">
        Parcours visuel
      </h3>
      <p className="mb-4 text-sm text-gray-600">Vue chronologique de vos modules de formation.</p>

      <div className="mb-4 flex flex-wrap items-center gap-3 text-xs">
        <span className="inline-flex items-center gap-2 rounded border border-gray-200 bg-gray-50 px-2.5 py-1.5 text-gray-700">
          <span className="h-2.5 w-2.5 rounded-full bg-vertone" />
          Terminé
        </span>
        <span className="inline-flex items-center gap-2 rounded border border-gray-200 bg-gray-50 px-2.5 py-1.5 text-gray-700">
          <span className="h-2.5 w-2.5 rounded-full bg-orangeone" />
          En cours
        </span>
        <span className="inline-flex items-center gap-2 rounded border border-gray-200 bg-gray-50 px-2.5 py-1.5 text-gray-700">
          <span className="h-2.5 w-2.5 rounded-full bg-gray-400" />À démarrer
        </span>
      </div>

      <div ref={canvasRef} className="h-[360px] w-full overflow-hidden rounded border border-gray-200">
        <ReactFlow
          nodes={flowNodes}
          edges={flowEdges}
          onInit={setInstance}
          onNodeClick={(_event, node) => {
            const url = node?.data?.url;
            if (typeof url === 'string' && url.length > 0) {
              window.location.href = url;
            }
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
  );
}

function mountStagiaireModulePathFlow() {
  const mounts = document.querySelectorAll('[data-stagiaire-module-flow]');

  mounts.forEach((mount) => {
    if (mount.dataset.flowMounted === '1') return;

    const modules = parseJsonDataset(mount, 'modules', []);

    createRoot(mount).render(<StagiaireModulePathFlow modules={modules} />);
    mount.dataset.flowMounted = '1';
  });
}

if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', mountStagiaireModulePathFlow);
} else {
  mountStagiaireModulePathFlow();
}
