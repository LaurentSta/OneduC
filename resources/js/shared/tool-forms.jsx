import React, { useState } from 'react';

// ─── Icons ────────────────────────────────────────────────────────────────────

export function CloudIcon({ className = 'h-4 w-4' }) {
  return (
    <svg xmlns="http://www.w3.org/2000/svg" className={className} fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
      <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="1.8"
        d="M3 15a4 4 0 004 4h9a5 5 0 10-.1-9.999 5.002 5.002 0 10-9.78 2.096A4.001 4.001 0 003 15z" />
    </svg>
  );
}

export function PollIcon({ className = 'h-4 w-4' }) {
  return (
    <svg xmlns="http://www.w3.org/2000/svg" className={className} fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
      <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="1.8"
        d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
    </svg>
  );
}

export function pollTitle(item) {
  return item.poll_questions?.[0]?.question || item.poll_question || 'Sondage';
}

export function pollChoices(item) {
  return item.poll_questions?.[0]?.choices || item.poll_choices || [];
}

// ─── Nuage de mots ──────────────────────────────────────────────────────────────

export function WordCloudForm({ onAdd, onCancel, initialValues = null }) {
  const [title, setTitle]       = useState(initialValues?.wc_title ?? '');
  const [questions, setQuestions] = useState(
    initialValues?.wc_questions?.length ? initialValues.wc_questions : ['']
  );
  const [duration, setDuration] = useState(
    initialValues?.wc_duration ? String(initialValues.wc_duration) : ''
  );
  const isEdit = initialValues !== null;

  const validQuestions = questions.filter(q => q.trim());
  const canAdd = title.trim() && validQuestions.length > 0;

  const updateQuestion = (i, val) => setQuestions(qs => qs.map((q, idx) => idx === i ? val : q));
  const addQuestion    = () => { if (questions.length < 10) setQuestions(qs => [...qs, '']); };
  const removeQuestion = (i) => { if (questions.length > 1) setQuestions(qs => qs.filter((_, idx) => idx !== i)); };

  return (
    <div className="rounded-[12px] border border-amber-200 bg-amber-50 p-4 space-y-3">
      <p className="text-sm font-semibold text-amber-800 flex items-center gap-2">
        <CloudIcon className="h-4 w-4" />
        {isEdit ? 'Modifier le nuage de mots' : 'Nouveau nuage de mots'}
      </p>

      <div>
        <label className="block text-xs font-medium text-gray-700 mb-1">
          Titre <span className="text-red-500">*</span>
        </label>
        <input
          type="text"
          value={title}
          onChange={(e) => setTitle(e.target.value)}
          maxLength={255}
          placeholder="Ex : Bilan de la formation"
          className="w-full rounded-[8px] border border-gray-300 px-3 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400"
        />
      </div>

      <div>
        <div className="flex items-center justify-between mb-1">
          <label className="text-xs font-medium text-gray-700">
            Questions posées aux stagiaires <span className="text-red-500">*</span>
          </label>
          {questions.length < 10 && (
            <button type="button" onClick={addQuestion}
              className="text-[11px] font-semibold text-amber-700 hover:text-amber-900 flex items-center gap-0.5 transition">
              <svg xmlns="http://www.w3.org/2000/svg" className="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M12 4v16m8-8H4"/>
              </svg>
              Ajouter une question
            </button>
          )}
        </div>
        <div className="space-y-2">
          {questions.map((q, i) => (
            <div key={i} className="flex items-center gap-2">
              <span className="text-[11px] font-bold text-amber-600 w-5 shrink-0 text-right">{i + 1}.</span>
              <input
                type="text"
                value={q}
                onChange={(e) => updateQuestion(i, e.target.value)}
                maxLength={500}
                placeholder="Ex : En un mot, comment vous sentez-vous ?"
                className="flex-1 rounded-[8px] border border-gray-300 px-3 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400"
              />
              {questions.length > 1 && (
                <button type="button" onClick={() => removeQuestion(i)}
                  className="text-gray-400 hover:text-red-500 transition shrink-0">
                  <svg xmlns="http://www.w3.org/2000/svg" className="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M6 18L18 6M6 6l12 12"/>
                  </svg>
                </button>
              )}
            </div>
          ))}
        </div>
      </div>

      <div>
        <label className="block text-xs font-medium text-gray-700 mb-1">
          Durée totale pour l'ensemble des questions
          <span className="ml-1 font-normal text-gray-400">(optionnel)</span>
        </label>
        <div className="flex items-center gap-2">
          <input
            type="number"
            min="1"
            max="120"
            value={duration}
            onChange={(e) => setDuration(e.target.value)}
            placeholder="—"
            className="w-24 rounded-[8px] border border-gray-300 px-3 py-1.5 text-sm text-center focus:outline-none focus:ring-2 focus:ring-amber-400"
          />
          <span className="text-sm text-gray-500">minutes</span>
        </div>
      </div>

      <div className="flex gap-2 justify-end">
        <button type="button" onClick={onCancel}
          className="px-3 py-1.5 rounded-[8px] border border-gray-300 text-sm text-gray-600 hover:bg-gray-50 transition">
          Annuler
        </button>
        <button type="button"
          onClick={() => {
            if (!canAdd) return;
            onAdd({ wc_title: title.trim(), wc_questions: validQuestions, wc_duration: duration ? Number(duration) : null });
          }}
          disabled={!canAdd}
          className="px-3 py-1.5 rounded-[8px] bg-amber-500 text-white text-sm font-medium hover:bg-amber-600 disabled:opacity-40 disabled:cursor-not-allowed transition">
          {isEdit ? 'Enregistrer' : 'Ajouter'}
        </button>
      </div>
    </div>
  );
}

// ─── Sondage ──────────────────────────────────────────────────────────────────

const emptyPollQuestion = () => ({ question: '', choices: ['', ''] });

function PollQuestionBlock({ pq, index, total, onChange, onRemove }) {
  const setChoice = (ci, val) => onChange({ ...pq, choices: pq.choices.map((c, i) => i === ci ? val : c) });
  const addChoice = () => { if (pq.choices.length < 5) onChange({ ...pq, choices: [...pq.choices, ''] }); };
  const removeChoice = (ci) => { if (pq.choices.length > 2) onChange({ ...pq, choices: pq.choices.filter((_, i) => i !== ci) }); };

  return (
    <div className="rounded-[10px] border border-teal-200 bg-white p-3 space-y-2">
      <div className="flex items-center justify-between gap-2">
        <span className="text-xs font-bold text-teal-700">Question {index + 1}</span>
        {total > 1 && (
          <button type="button" onClick={onRemove} className="text-gray-400 hover:text-red-500 transition">
            <svg xmlns="http://www.w3.org/2000/svg" className="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
          </button>
        )}
      </div>
      <input type="text" value={pq.question}
        onChange={(e) => onChange({ ...pq, question: e.target.value })}
        maxLength={500} placeholder="Ex : Comment vous sentez-vous après cette formation ?"
        className="w-full rounded-[8px] border border-gray-300 px-3 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-teal-400"
      />
      <div className="space-y-1.5 pl-2 border-l-2 border-teal-100">
        {pq.choices.map((c, ci) => (
          <div key={ci} className="flex items-center gap-2">
            <span className="text-[11px] font-bold text-teal-500 w-4 shrink-0">{ci + 1}.</span>
            <input type="text" value={c}
              onChange={(e) => setChoice(ci, e.target.value)}
              maxLength={200} placeholder={`Choix ${ci + 1}`}
              className="flex-1 rounded-[8px] border border-gray-300 px-3 py-1 text-sm focus:outline-none focus:ring-2 focus:ring-teal-400"
            />
            {pq.choices.length > 2 && (
              <button type="button" onClick={() => removeChoice(ci)} className="text-gray-400 hover:text-red-500 transition shrink-0">
                <svg xmlns="http://www.w3.org/2000/svg" className="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
              </button>
            )}
          </div>
        ))}
        {pq.choices.length < 5 && (
          <button type="button" onClick={addChoice} className="text-[11px] text-teal-600 hover:text-teal-800 flex items-center gap-1 transition">
            <svg xmlns="http://www.w3.org/2000/svg" className="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M12 4v16m8-8H4"/>
            </svg>
            Ajouter un choix
          </button>
        )}
      </div>
    </div>
  );
}

export function PollForm({ onAdd, onCancel, initialValues = null }) {
  const [pollQuestions, setPollQuestions] = useState(
    initialValues?.poll_questions?.length ? initialValues.poll_questions : [emptyPollQuestion()]
  );
  const [duration, setDuration] = useState(
    initialValues?.poll_duration ? String(initialValues.poll_duration) : ''
  );
  const isEdit = initialValues !== null;

  const updatePq  = (i, val) => setPollQuestions(pqs => pqs.map((pq, idx) => idx === i ? val : pq));
  const addPq     = () => { if (pollQuestions.length < 10) setPollQuestions(pqs => [...pqs, emptyPollQuestion()]); };
  const removePq  = (i) => { if (pollQuestions.length > 1) setPollQuestions(pqs => pqs.filter((_, idx) => idx !== i)); };

  const validPqs = pollQuestions.filter(pq => pq.question.trim() && pq.choices.filter(c => c.trim()).length >= 2);
  const canSubmit = validPqs.length > 0;

  return (
    <div className="rounded-[12px] border border-teal-200 bg-teal-50 p-4 space-y-3">
      <p className="text-sm font-semibold text-teal-800 flex items-center gap-2">
        <PollIcon className="h-4 w-4" />
        {isEdit ? 'Modifier le sondage' : 'Nouveau sondage'}
      </p>

      <div className="space-y-3">
        {pollQuestions.map((pq, i) => (
          <PollQuestionBlock key={i} pq={pq} index={i} total={pollQuestions.length}
            onChange={(val) => updatePq(i, val)}
            onRemove={() => removePq(i)}
          />
        ))}
      </div>

      {pollQuestions.length < 10 && (
        <button type="button" onClick={addPq}
          className="text-xs font-semibold text-teal-700 hover:text-teal-900 flex items-center gap-1 transition">
          <svg xmlns="http://www.w3.org/2000/svg" className="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M12 4v16m8-8H4"/>
          </svg>
          Ajouter une question
        </button>
      )}

      <div>
        <label className="block text-xs font-medium text-gray-700 mb-1">
          Durée totale pour l'ensemble des questions
          <span className="ml-1 font-normal text-gray-400">(optionnel)</span>
        </label>
        <div className="flex items-center gap-2">
          <input type="number" min="1" max="120" value={duration}
            onChange={(e) => setDuration(e.target.value)}
            placeholder="—"
            className="w-24 rounded-[8px] border border-gray-300 px-3 py-1.5 text-sm text-center focus:outline-none focus:ring-2 focus:ring-teal-400"
          />
          <span className="text-sm text-gray-500">minutes</span>
        </div>
      </div>

      <div className="flex gap-2 justify-end">
        <button type="button" onClick={onCancel}
          className="px-3 py-1.5 rounded-[8px] border border-gray-300 text-sm text-gray-600 hover:bg-gray-50 transition">
          Annuler
        </button>
        <button type="button"
          onClick={() => { if (canSubmit) onAdd({ poll_questions: validPqs, poll_duration: duration ? Number(duration) : null }); }}
          disabled={!canSubmit}
          className="px-3 py-1.5 rounded-[8px] bg-teal-600 text-white text-sm font-medium hover:bg-teal-700 disabled:opacity-40 disabled:cursor-not-allowed transition">
          {isEdit ? 'Enregistrer' : 'Ajouter'}
        </button>
      </div>
    </div>
  );
}
