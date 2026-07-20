import React, { useMemo, useState } from 'react';
import { VraiFauxToolForm } from './outils/vrai-faux-form';

export function genericToolTitle(item) {
  return String(item?.configuration?.titre ?? item?.title ?? item?.outil_label ?? item?.outil ?? 'Activité').trim();
}

export function normalizeGenericTool(raw, index = 0) {
  if (raw?.type !== 'outil') return null;

  const outil = String(raw?.outil ?? '').trim();
  if (!outil) return null;

  const configuration = raw?.configuration && typeof raw.configuration === 'object' && !Array.isArray(raw.configuration)
    ? raw.configuration
    : {};

  return {
    type: 'outil',
    id: String(raw?.id ?? `outil-${outil}-${index + 1}`),
    position: Number.parseInt(raw?.position, 10) || index + 1,
    outil,
    outil_label: String(raw?.outil_label ?? outil).trim(),
    configuration,
    execution_disponible: Boolean(raw?.execution_disponible),
  };
}

export function genericToolPayload(item) {
  return {
    type: 'outil',
    outil: item.outil,
    configuration: item.configuration ?? {},
    position: item.position,
  };
}

export function GenericToolStatusBadge({ executionDisponible = false }) {
  if (executionDisponible) {
    return (
      <span className="inline-flex items-center rounded-full bg-emerald-100 px-2 py-0.5 text-[10px] font-semibold text-emerald-700">
        Prêt à être lancé pour un groupe
      </span>
    );
  }

  return (
    <span className="inline-flex items-center rounded-full bg-slate-100 px-2 py-0.5 text-[10px] font-semibold text-slate-600">
      Configuration — session non lancée
    </span>
  );
}

export function GenericToolForm({ item, onSave, onCancel }) {
  if (item?.outil === 'vrai-faux') {
    return <VraiFauxToolForm item={item} onSave={onSave} onCancel={onCancel} />;
  }

  return <RawConfigurationForm item={item} onSave={onSave} onCancel={onCancel} />;
}

function RawConfigurationForm({ item, onSave, onCancel }) {
  const configurationInitiale = useMemo(
    () => JSON.stringify(item?.configuration ?? {}, null, 2),
    [item],
  );
  const [configuration, setConfiguration] = useState(configurationInitiale);
  const [erreur, setErreur] = useState('');

  const enregistrer = (event) => {
    event.preventDefault();

    try {
      const decodee = JSON.parse(configuration);
      if (!decodee || typeof decodee !== 'object' || Array.isArray(decodee)) {
        setErreur('La configuration doit être un objet JSON.');
        return;
      }

      setErreur('');
      onSave({ configuration: decodee });
    } catch {
      setErreur('La configuration JSON n’est pas valide.');
    }
  };

  return (
    <form onSubmit={enregistrer} className="rounded-[10px] border border-slate-200 bg-white p-3 shadow-sm">
      <label className="block text-xs font-semibold text-slate-700">
        Configuration de {item?.outil_label ?? item?.outil}
        <textarea
          value={configuration}
          onChange={(event) => setConfiguration(event.target.value)}
          rows={10}
          spellCheck="false"
          className="mt-1 w-full rounded-[8px] border border-slate-300 px-3 py-2 font-mono text-xs focus:border-[#004461] focus:outline-none focus:ring-2 focus:ring-[#004461]/20"
        />
      </label>

      <p className="mt-2 text-[11px] leading-4 text-slate-500">
        Cette étape conserve une configuration. Elle ne crée ni session, ni code d’accès, ni réponse.
      </p>
      {erreur && <p className="mt-2 text-xs font-semibold text-red-600">{erreur}</p>}

      <div className="mt-3 flex justify-end gap-2">
        <button type="button" onClick={onCancel} className="rounded-[8px] border border-slate-300 px-3 py-1.5 text-xs font-semibold text-slate-600 hover:bg-slate-50">
          Annuler
        </button>
        <button type="submit" className="rounded-[8px] bg-[#004461] px-3 py-1.5 text-xs font-semibold text-white hover:bg-[#E94D2A]">
          Enregistrer la configuration
        </button>
      </div>
    </form>
  );
}
