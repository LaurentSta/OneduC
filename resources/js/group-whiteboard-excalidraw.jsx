import React, { useState, useEffect, useRef, useCallback } from 'react';
import { createRoot } from 'react-dom/client';
import { Excalidraw } from '@excalidraw/excalidraw';
import '@excalidraw/excalidraw/index.css';

function ExcalidrawWhiteboard({ config }) {
    const [excalidrawAPI, setExcalidrawAPI] = useState(null);
    const versionRef = useRef(config.snapshot?.version ?? 0);
    const isSaving = useRef(false);
    const saveTimer = useRef(null);
    const canEdit = config.permissions?.can_edit ?? false;
    const canClear = config.permissions?.can_clear ?? false;

    const initialData = {
        elements: config.snapshot?.excalidraw_data?.elements ?? [],
        appState: {
            viewBackgroundColor: config.snapshot?.excalidraw_data?.app_state?.viewBackgroundColor ?? '#ffffff',
            collaborators: new Map(),
        },
        scrollToContent: true,
    };

    const persistSave = useCallback(async (elements, appState) => {
        if (!config.routes?.save || !canEdit) return;
        if (isSaving.current) return;
        isSaving.current = true;

        try {
            const res = await fetch(config.routes.save, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: JSON.stringify({
                    elements,
                    app_state: {
                        viewBackgroundColor: appState.viewBackgroundColor ?? '#ffffff',
                    },
                }),
            });

            if (res.ok) {
                const data = await res.json();
                versionRef.current = data.version ?? versionRef.current;
            }
        } finally {
            isSaving.current = false;
        }
    }, [config.routes?.save, canEdit]);

    const onChange = useCallback((elements, appState) => {
        if (!canEdit) return;
        clearTimeout(saveTimer.current);
        saveTimer.current = setTimeout(() => persistSave(elements, appState), 1200);
    }, [canEdit, persistSave]);

    useEffect(() => {
        if (!config.routes?.snapshot || !excalidrawAPI) return;

        const poll = async () => {
            if (isSaving.current) return;
            try {
                const res = await fetch(config.routes.snapshot, {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                });
                if (!res.ok) return;
                const data = await res.json();

                if (data.version > versionRef.current && data.excalidraw_data?.elements) {
                    versionRef.current = data.version;
                    excalidrawAPI.updateScene({
                        elements: data.excalidraw_data.elements,
                    });
                }
            } catch {}
        };

        const id = setInterval(poll, config.poll_interval_ms ?? 2500);
        return () => clearInterval(id);
    }, [config.routes?.snapshot, config.poll_interval_ms, excalidrawAPI]);

    return (
        <div style={{ height: '82vh', borderRadius: '20px', overflow: 'hidden' }}>
            <Excalidraw
                excalidrawAPI={(api) => setExcalidrawAPI(api)}
                initialData={initialData}
                onChange={onChange}
                viewModeEnabled={!canEdit}
                langCode="fr"
                UIOptions={{
                    canvasActions: {
                        clearCanvas: canClear,
                        export: { saveFileToDisk: true },
                        toggleTheme: true,
                        changeViewBackgroundColor: true,
                    },
                }}
            />
        </div>
    );
}

export function mountGroupWhiteboardExcalidraw() {
    const root = document.querySelector('[data-whiteboard-app]');
    const configNode = document.querySelector('[data-whiteboard-config]');
    if (!root || !configNode) return;

    const config = JSON.parse(configNode.textContent || '{}');
    createRoot(root).render(<ExcalidrawWhiteboard config={config} />);
}
