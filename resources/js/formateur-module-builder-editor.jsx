import React, { useEffect, useRef, useState } from 'react';
import { createRoot } from 'react-dom/client';
import { EditorContent, useEditor } from '@tiptap/react';
import StarterKit from '@tiptap/starter-kit';

const BLOCK_LABELS = {
  text: 'Texte',
  image: 'Image',
  list: 'Liste',
  quote: 'Citation',
  divider: 'Separateur',
};

let blockIdSeq = 0;
function nextClientId() {
  blockIdSeq += 1;
  return `block-${Date.now()}-${blockIdSeq}`;
}

function createBlock(type) {
  switch (type) {
    case 'text':
      return { clientId: nextClientId(), type, html: '' };
    case 'image':
      return { clientId: nextClientId(), type, path: '', url: '', caption: '' };
    case 'list':
      return { clientId: nextClientId(), type, style: 'bullet', items: [''] };
    case 'quote':
      return { clientId: nextClientId(), type, text: '', source: '' };
    case 'divider':
    default:
      return { clientId: nextClientId(), type: 'divider' };
  }
}

function csrfToken() {
  return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
}

function TextBlockEditor({ block, onChange }) {
  const editor = useEditor({
    extensions: [
      StarterKit.configure({
        bulletList: false,
        orderedList: false,
        listItem: false,
        blockquote: false,
      }),
    ],
    content: block.html,
    editorProps: {
      attributes: {
        class: 'min-h-[100px] rounded-b-[10px] border border-t-0 border-gray-300 px-3 py-2.5 text-sm focus:outline-none prose prose-sm max-w-none',
      },
    },
    onUpdate: ({ editor: currentEditor }) => {
      onChange({ ...block, html: currentEditor.getHTML() });
    },
  });

  const runCommand = (command) => () => {
    if (editor) command(editor.chain().focus());
  };

  return (
    <div>
      <div className="flex flex-wrap items-center gap-1 rounded-t-[10px] border border-gray-300 bg-gray-50 px-2 py-1.5">
        <button type="button" onClick={runCommand((chain) => chain.toggleBold().run())} className="rounded px-2 py-1 text-xs font-bold text-gray-600 hover:bg-gray-200">Gras</button>
        <button type="button" onClick={runCommand((chain) => chain.toggleItalic().run())} className="rounded px-2 py-1 text-xs italic text-gray-600 hover:bg-gray-200">Italique</button>
        <button type="button" onClick={runCommand((chain) => chain.toggleHeading({ level: 2 }).run())} className="rounded px-2 py-1 text-xs font-semibold text-gray-600 hover:bg-gray-200">Titre</button>
      </div>
      <EditorContent editor={editor} />
    </div>
  );
}

function ImageBlockEditor({ block, onChange, uploadUrl }) {
  const [uploading, setUploading] = useState(false);
  const [error, setError] = useState('');

  const handleFile = async (event) => {
    const file = event.target.files?.[0];
    if (!file) return;

    setUploading(true);
    setError('');

    const formData = new FormData();
    formData.append('image', file);

    try {
      const response = await fetch(uploadUrl, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': csrfToken(), Accept: 'application/json' },
        body: formData,
      });

      if (!response.ok) throw new Error('upload failed');

      const data = await response.json();
      onChange({ ...block, path: data.path, url: data.url });
    } catch (e) {
      setError("Echec de l'envoi de l'image.");
    } finally {
      setUploading(false);
    }
  };

  return (
    <div className="space-y-2">
      {block.url ? (
        <img src={block.url} alt={block.caption || ''} className="max-h-48 rounded-lg border border-gray-200 object-contain" />
      ) : (
        <div className="flex h-24 items-center justify-center rounded-lg border border-dashed border-gray-300 text-xs text-gray-400">
          Aucune image
        </div>
      )}

      <input type="file" accept="image/jpeg,image/png,image/webp,image/gif" onChange={handleFile} disabled={uploading} className="text-xs" />
      {uploading && <p className="text-xs text-gray-400">Envoi en cours...</p>}
      {error && <p className="text-xs text-red-500">{error}</p>}

      <input
        type="text"
        placeholder="Legende (optionnel)"
        value={block.caption || ''}
        onChange={(e) => onChange({ ...block, caption: e.target.value })}
        className="w-full rounded-[10px] border border-gray-300 px-3 py-2 text-sm focus:border-orangeone focus:outline-none"
      />
    </div>
  );
}

function ListBlockEditor({ block, onChange }) {
  const items = block.items && block.items.length ? block.items : [''];

  const updateItem = (index, value) => {
    const nextItems = [...items];
    nextItems[index] = value;
    onChange({ ...block, items: nextItems });
  };

  const addItem = () => onChange({ ...block, items: [...items, ''] });
  const removeItem = (index) => onChange({ ...block, items: items.filter((_, i) => i !== index) });

  return (
    <div className="space-y-2">
      <div className="flex gap-3 text-xs">
        <label className="flex items-center gap-1">
          <input type="radio" checked={block.style !== 'numbered'} onChange={() => onChange({ ...block, style: 'bullet' })} />
          A puces
        </label>
        <label className="flex items-center gap-1">
          <input type="radio" checked={block.style === 'numbered'} onChange={() => onChange({ ...block, style: 'numbered' })} />
          Numerotee
        </label>
      </div>

      {items.map((item, index) => (
        <div key={index} className="flex gap-2">
          <input
            type="text"
            value={item}
            onChange={(e) => updateItem(index, e.target.value)}
            className="flex-1 rounded-[10px] border border-gray-300 px-3 py-2 text-sm focus:border-orangeone focus:outline-none"
          />
          {items.length > 1 && (
            <button type="button" onClick={() => removeItem(index)} className="text-xs text-red-500">Retirer</button>
          )}
        </div>
      ))}

      <button type="button" onClick={addItem} className="text-xs font-semibold text-orangeone hover:underline">+ Ajouter un element</button>
    </div>
  );
}

function QuoteBlockEditor({ block, onChange }) {
  return (
    <div className="space-y-2">
      <textarea
        rows={3}
        placeholder="Texte de la citation"
        value={block.text || ''}
        onChange={(e) => onChange({ ...block, text: e.target.value })}
        className="w-full rounded-[10px] border border-gray-300 px-3 py-2 text-sm focus:border-orangeone focus:outline-none"
      />
      <input
        type="text"
        placeholder="Source / auteur (optionnel)"
        value={block.source || ''}
        onChange={(e) => onChange({ ...block, source: e.target.value })}
        className="w-full rounded-[10px] border border-gray-300 px-3 py-2 text-sm focus:border-orangeone focus:outline-none"
      />
    </div>
  );
}

function BlockRow({ block, index, onChange, onRemove, onDragStart, onDragOver, onDrop, uploadUrl }) {
  return (
    <div
      draggable
      onDragStart={() => onDragStart(index)}
      onDragOver={(e) => { e.preventDefault(); onDragOver(index); }}
      onDrop={() => onDrop(index)}
      className="rounded-[14px] border border-gray-200 bg-gray-50/60 p-3"
    >
      <div className="mb-2 flex items-center justify-between">
        <span className="cursor-move text-xs font-bold uppercase tracking-wide text-gray-400">
          ⠿ {BLOCK_LABELS[block.type] || block.type}
        </span>
        <button type="button" onClick={() => onRemove(index)} className="text-xs font-semibold text-red-500 hover:underline">
          Supprimer
        </button>
      </div>

      {block.type === 'text' && <TextBlockEditor block={block} onChange={onChange} />}
      {block.type === 'image' && <ImageBlockEditor block={block} onChange={onChange} uploadUrl={uploadUrl} />}
      {block.type === 'list' && <ListBlockEditor block={block} onChange={onChange} />}
      {block.type === 'quote' && <QuoteBlockEditor block={block} onChange={onChange} />}
      {block.type === 'divider' && <hr className="border-gray-300" />}
    </div>
  );
}

const SAVE_STATUS = { IDLE: 'idle', SAVING: 'saving', SAVED: 'saved', ERROR: 'error' };
const AUTOSAVE_DELAY_MS = 800;

function SaveStatus({ status, savedAt }) {
  if (status === SAVE_STATUS.SAVING) {
    return <span className="text-xs text-gray-400">Enregistrement…</span>;
  }
  if (status === SAVE_STATUS.ERROR) {
    return <span className="text-xs text-red-500">Échec de l'enregistrement</span>;
  }
  if (status === SAVE_STATUS.SAVED && savedAt) {
    return <span className="text-xs text-vertone">Enregistré à {savedAt}</span>;
  }
  return null;
}

function LectureEditor({ lectureId, initialTitle, initialBlocks, updateUrl, uploadUrl }) {
  const [title, setTitle] = useState(initialTitle || '');
  const [blocks, setBlocks] = useState(() =>
    (initialBlocks || []).map((block) => ({ ...block, clientId: nextClientId() }))
  );
  const [status, setStatus] = useState(SAVE_STATUS.IDLE);
  const [savedAt, setSavedAt] = useState('');
  const dragIndexRef = useRef(null);
  const saveTimeoutRef = useRef(null);
  const skipNextSaveRef = useRef(true);

  const save = async () => {
    setStatus(SAVE_STATUS.SAVING);
    try {
      const response = await fetch(updateUrl, {
        method: 'PUT',
        headers: {
          'Content-Type': 'application/json',
          Accept: 'application/json',
          'X-CSRF-TOKEN': csrfToken(),
        },
        body: JSON.stringify({
          lecture_title: title,
          content_blocks: JSON.stringify(blocks.map(({ clientId, ...rest }) => rest)),
        }),
      });

      if (!response.ok) throw new Error('save failed');

      setStatus(SAVE_STATUS.SAVED);
      setSavedAt(new Date().toLocaleTimeString('fr-FR', { hour: '2-digit', minute: '2-digit' }));
      window.dispatchEvent(new CustomEvent('module-builder:lecture-saved', {
        detail: { id: lectureId, lecture_title: title },
      }));
    } catch (e) {
      setStatus(SAVE_STATUS.ERROR);
    }
  };

  useEffect(() => {
    if (skipNextSaveRef.current) {
      skipNextSaveRef.current = false;
      return undefined;
    }

    if (saveTimeoutRef.current) clearTimeout(saveTimeoutRef.current);
    saveTimeoutRef.current = setTimeout(save, AUTOSAVE_DELAY_MS);

    return () => { if (saveTimeoutRef.current) clearTimeout(saveTimeoutRef.current); };
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [title, blocks]);

  const addBlock = (type) => setBlocks((prev) => [...prev, createBlock(type)]);
  const updateBlock = (index, updated) => setBlocks((prev) => prev.map((b, i) => (i === index ? updated : b)));
  const removeBlock = (index) => setBlocks((prev) => prev.filter((_, i) => i !== index));

  const handleDragStart = (index) => { dragIndexRef.current = index; };
  const handleDragOver = () => {};
  const handleDrop = (dropIndex) => {
    const dragIndex = dragIndexRef.current;
    dragIndexRef.current = null;
    if (dragIndex === null || dragIndex === dropIndex) return;

    setBlocks((prev) => {
      const next = [...prev];
      const [moved] = next.splice(dragIndex, 1);
      next.splice(dropIndex, 0, moved);
      return next;
    });
  };

  return (
    <div className="space-y-4">
      <div className="flex items-center justify-between gap-3">
        <input
          type="text"
          value={title}
          onChange={(e) => setTitle(e.target.value)}
          maxLength={255}
          placeholder="Titre de la leçon"
          className="flex-1 rounded-[10px] border border-gray-300 px-3 py-2 text-sm font-semibold focus:border-orangeone focus:outline-none"
        />
        <SaveStatus status={status} savedAt={savedAt} />
      </div>

      <div className="flex flex-wrap gap-2">
        {Object.entries(BLOCK_LABELS).map(([type, label]) => (
          <button
            key={type}
            type="button"
            onClick={() => addBlock(type)}
            className="rounded-[8px] border border-gray-300 bg-white px-3 py-1.5 text-xs font-bold text-gray-600 hover:bg-gray-50"
          >
            + {label}
          </button>
        ))}
      </div>

      <div className="space-y-3">
        {blocks.map((block, index) => (
          <BlockRow
            key={block.clientId}
            block={block}
            index={index}
            onChange={(updated) => updateBlock(index, updated)}
            onRemove={removeBlock}
            onDragStart={handleDragStart}
            onDragOver={handleDragOver}
            onDrop={handleDrop}
            uploadUrl={uploadUrl}
          />
        ))}

        {blocks.length === 0 && (
          <p className="text-xs text-gray-400">Aucun bloc pour le moment. Ajoutez-en un ci-dessus.</p>
        )}
      </div>
    </div>
  );
}

export function mountModuleBuilderEditors() {
  document.querySelectorAll('[data-block-editor]').forEach((container) => {
    if (container.dataset.blockEditorMounted === '1') return;
    container.dataset.blockEditorMounted = '1';

    const lectureId = container.dataset.lectureId || '';
    const updateUrl = container.dataset.updateUrl || '';
    const uploadUrl = container.dataset.uploadUrl || '';
    const initialTitle = container.dataset.initialTitle || '';

    let initialBlocks = [];
    try {
      initialBlocks = JSON.parse(container.dataset.initialBlocks || '[]');
      if (!Array.isArray(initialBlocks)) initialBlocks = [];
    } catch (e) {
      initialBlocks = [];
    }

    createRoot(container).render(
      <LectureEditor
        lectureId={lectureId}
        initialTitle={initialTitle}
        initialBlocks={initialBlocks}
        updateUrl={updateUrl}
        uploadUrl={uploadUrl}
      />
    );
  });
}
