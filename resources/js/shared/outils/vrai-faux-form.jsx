import React, { useState } from 'react';

const emptyAffirmation = () => ({ texte: '', reponse: true });

export function VraiFauxToolForm({ item, onSave, onCancel }) {
  const configuration = item?.configuration ?? {};
  const [titre, setTitre] = useState(configuration.titre ?? 'Vrai ou Faux');
  const [consigne, setConsigne] = useState(configuration.consigne ?? '');
  const [affirmations, setAffirmations] = useState(
    Array.isArray(configuration.affirmations) && configuration.affirmations.length
      ? configuration.affirmations.map((a) => ({ texte: String(a?.texte ?? ''), reponse: Boolean(a?.reponse) }))
      : [emptyAffirmation()]
  );

  const validAffirmations = affirmations.filter((a) => a.texte.trim());
  const canSave = titre.trim() && validAffirmations.length > 0;

  const updateAffirmation = (i, patch) =>
    setAffirmations((rows) => rows.map((row, idx) => (idx === i ? { ...row, ...patch } : row)));
  const addAffirmation = () => { if (affirmations.length < 50) setAffirmations((rows) => [...rows, emptyAffirmation()]); };
  const removeAffirmation = (i) => { if (affirmations.length > 1) setAffirmations((rows) => rows.filter((_, idx) => idx !== i)); };

  const enregistrer = (event) => {
    event.preventDefault();
    if (!canSave) return;

    onSave({
      configuration: {
        titre: titre.trim(),
        consigne: consigne.trim() || null,
        affirmations: validAffirmations.map((a) => ({ texte: a.texte.trim(), reponse: Boolean(a.reponse) })),
      },
    });
  };

  return (
    <form onSubmit={enregistrer} className="rounded-[12px] border border-slate-300 bg-slate-50 p-4 space-y-3">
      <p className="text-sm font-semibold text-slate-800">Vrai ou Faux</p>

      <div>
        <label className="block text-xs font-medium text-gray-700 mb-1">
          Titre <span className="text-red-500">*</span>
        </label>
        <input
          type="text"
          value={titre}
          onChange={(e) => setTitre(e.target.value)}
          maxLength={255}
          className="w-full rounded-[8px] border border-gray-300 px-3 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-slate-400"
        />
      </div>

      <div>
        <label className="block text-xs font-medium text-gray-700 mb-1">
          Consigne <span className="font-normal text-gray-400">(optionnel)</span>
        </label>
        <input
          type="text"
          value={consigne}
          onChange={(e) => setConsigne(e.target.value)}
          maxLength={2000}
          placeholder="Ex : Indiquez si chaque affirmation est vraie ou fausse"
          className="w-full rounded-[8px] border border-gray-300 px-3 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-slate-400"
        />
      </div>

      <div>
        <div className="flex items-center justify-between mb-1">
          <label className="text-xs font-medium text-gray-700">
            Affirmations <span className="text-red-500">*</span>
          </label>
          {affirmations.length < 50 && (
            <button type="button" onClick={addAffirmation}
              className="text-[11px] font-semibold text-slate-700 hover:text-slate-900 flex items-center gap-0.5 transition">
              <svg xmlns="http://www.w3.org/2000/svg" className="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M12 4v16m8-8H4"/>
              </svg>
              Ajouter une affirmation
            </button>
          )}
        </div>
        <div className="space-y-2">
          {affirmations.map((a, i) => (
            <div key={i} className="rounded-[8px] border border-gray-300 bg-white p-2.5 space-y-2">
              <div className="flex items-center gap-2">
                <span className="text-[11px] font-bold text-slate-500 w-5 shrink-0 text-right">{i + 1}.</span>
                <input
                  type="text"
                  value={a.texte}
                  onChange={(e) => updateAffirmation(i, { texte: e.target.value })}
                  maxLength={1000}
                  placeholder="Ex : Paris est la capitale de la France"
                  className="flex-1 rounded-[8px] border border-gray-300 px-3 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-slate-400"
                />
                {affirmations.length > 1 && (
                  <button type="button" onClick={() => removeAffirmation(i)}
                    className="text-gray-400 hover:text-red-500 transition shrink-0">
                    <svg xmlns="http://www.w3.org/2000/svg" className="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                  </button>
                )}
              </div>
              <div className="flex items-center gap-3 pl-7 text-xs">
                <label className="flex items-center gap-1.5 cursor-pointer">
                  <input type="radio" checked={a.reponse === true} onChange={() => updateAffirmation(i, { reponse: true })} />
                  Vrai
                </label>
                <label className="flex items-center gap-1.5 cursor-pointer">
                  <input type="radio" checked={a.reponse === false} onChange={() => updateAffirmation(i, { reponse: false })} />
                  Faux
                </label>
              </div>
            </div>
          ))}
        </div>
      </div>

      <div className="flex gap-2 justify-end">
        <button type="button" onClick={onCancel}
          className="px-3 py-1.5 rounded-[8px] border border-gray-300 text-sm text-gray-600 hover:bg-gray-50 transition">
          Annuler
        </button>
        <button type="submit" disabled={!canSave}
          className="px-3 py-1.5 rounded-[8px] bg-[#004461] text-white text-sm font-medium hover:bg-[#E94D2A] disabled:opacity-40 disabled:cursor-not-allowed transition">
          Enregistrer
        </button>
      </div>
    </form>
  );
}
