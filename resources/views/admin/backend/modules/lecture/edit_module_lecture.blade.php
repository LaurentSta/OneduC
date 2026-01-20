{{-- /home/laurents/Oneduc_Dev/resources/views/admin/backend/modules/lecture/edit_module_lecture.blade.php --}}
@extends('admin.admin_dashboard')

@push('styles')
    <style>
        .form-card { background: rgba(0, 68, 97, 0.04); border: 1px solid rgba(0, 68, 97, 0.1); border-radius: 20px; padding: 20px; }
        .form-card-title { font-size: 1rem; font-weight: 800; color: #004461; display: flex; align-items: center; gap: 8px; margin-bottom: 12px; text-transform: uppercase; letter-spacing: 0.5px; }
        .page-title { font-size: 1.5rem; font-weight: 800; color: #004461; }
        .btn-action { transition: all 0.2s; display: inline-flex; align-items: center; justify-content: center; gap: 8px; font-weight: 600; border-radius: 12px; border: none; cursor: pointer; }
        .btn-action:hover { transform: translateY(-1px); box-shadow: 0 4px 12px rgba(0,0,0,0.1); }
    </style>
@endpush

@section('admin')
<div class="max-w-4xl mx-auto mt-8 px-4">
    <div class="bg-white p-8 rounded-3xl shadow-xl border border-gray-100">

        {{-- Top Bar --}}
        <div class="flex items-center justify-between mb-8">
            <div>
                <h2 class="page-title text-2xl">Édition de la leçon</h2>
                <p class="text-sm text-gray-500 font-medium">Configuration des contenus et validations</p>
            </div>
            <a href="{{ route('admin.modules.lecture.add', ['id' => $mlecture->module_id]) }}" 
               class="btn-action px-4 py-2 bg-gray-100 text-gray-600 text-sm hover:bg-gray-200">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
                Retour
            </a>
        </div>

        {{-- Alertes --}}
        @if(session('success'))
            <div class="mb-6 p-4 rounded-xl bg-green-50 border border-green-100 text-green-700 text-sm flex items-center gap-3 font-semibold">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" /></svg>
                {{ session('success') }}
            </div>
        @endif
        @if(session('error'))
            <div class="mb-6 p-4 rounded-xl bg-red-50 border border-red-100 text-red-700 text-sm font-semibold">
                {{ session('error') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="mb-6 p-4 rounded-xl bg-red-50 border border-red-100 text-red-700 text-sm font-semibold">
                <div class="mb-2">Certaines informations sont incorrectes :</div>
                <ul class="list-disc ml-5 space-y-1 font-normal">
                    @foreach ($errors->all() as $message)
                        <li>{{ $message }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- FORMULAIRE PRINCIPAL (Titre + Quiz) --}}
        <form method="POST" action="{{ route('admin.lectures.update') }}" class="space-y-8" id="main-lecture-form">
            @csrf
            <input type="hidden" name="id" value="{{ $mlecture->id }}">

            {{-- 1. Titre --}}
            <div class="space-y-2">
                <label for="lecture_title" class="text-sm font-extrabold text-bleuone uppercase ml-1">Titre de la leçon</label>
                <input type="text" name="lecture_title" id="lecture_title" value="{{ old('lecture_title', $mlecture->lecture_title) }}" 
                       class="w-full px-5 py-4 bg-gray-50 border-none rounded-2xl focus:ring-2 focus:ring-orangeone text-lg font-bold text-gray-800 shadow-inner" required>
            </div>

            {{-- 2. BLOC SCORM --}}
            <div class="form-card">
                <div class="form-card-title">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9" /></svg>
                    Module Interactif (SCORM)
                </div>

                {{-- On vérifie s'il y a un chemin valide (soit déjà en base, soit venant de l'import immédiat) --}}
                @php
                    $currentScormPath = session('new_scorm_path', $mlecture->scorm_index_path ?? $mlecture->scorm_path);
                @endphp


                @if($currentScormPath)
                    <div class="flex items-center justify-between p-5 mb-5 bg-white rounded-2xl border border-gray-100 shadow-sm">
                        <div class="flex items-center gap-4 text-left">
                            <div class="w-12 h-12 flex items-center justify-center bg-blue-50 text-bleuone rounded-xl">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                            </div>
                            <div>
                                <div class="font-bold text-gray-900 line-clamp-1">Contenu prêt</div>
                                <div class="text-[10px] text-gray-400 font-mono line-clamp-1">{{ $currentScormPath }}</div>
                            </div>
                        </div>
                        <a href="{{ route('lecture.scorm', ['id' => $mlecture->id]) }}" target="_blank" 
                           class="btn-action px-5 py-2.5 bg-bleuone text-white text-sm">
                            Visualiser
                        </a>
                    </div>
                @else
                    <p class="text-xs text-gray-500 mb-5 italic text-center p-4 bg-white/50 rounded-xl border border-dashed border-gray-200">Aucun contenu SCORM lié.</p>
                @endif

                {{-- Zone d'import --}}
                <div class="bg-white/50 p-5 rounded-2xl border border-dashed border-gray-200">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 items-center">
                        <div>
                            <input type="file" name="zip" accept=".zip" form="form-import-scorm" 
                                   class="block w-full text-xs text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-bold file:bg-gray-100 file:text-gray-700 hover:file:bg-gray-200" required>
                        </div>
                        <div class="flex items-center justify-end">
                            <button type="submit" form="form-import-scorm" class="btn-action px-6 py-2 bg-orangeone/10 text-orangeone text-xs border border-orangeone/20 uppercase tracking-widest hover:bg-orangeone hover:text-white">
                                Importer ZIP
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            {{-- 3. Quiz --}}
            <div class="form-card">
                <div class="flex items-center justify-between mb-4">
                    <div class="form-card-title mb-0 font-extrabold uppercase">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" /></svg>
                        Validation Quiz
                    </div>
                    <a href="{{ route('admin.quiz.questions.index', ['lecture' => $mlecture->id]) }}" 
                       class="btn-action px-4 py-2 bg-white border border-gray-200 text-gray-700 text-xs shadow-sm hover:bg-gray-50">
                        Gérer les questions
                    </a>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 bg-white/50 p-5 rounded-2xl">
                    <label class="flex items-center gap-4 cursor-pointer p-2">
                        <input type="hidden" name="quiz_enabled" value="0">
                        <input type="checkbox" name="quiz_enabled" id="quiz_enabled" value="1" {{ old('quiz_enabled', $mlecture->quiz_enabled) ? 'checked' : '' }} class="h-6 w-6 rounded-lg text-orangeone border-gray-300 focus:ring-orangeone">
                        <div>
                            <span class="block font-bold text-gray-800">Activer le quiz</span>
                            <span class="text-xs text-gray-500">Obligatoire pour valider la leçon</span>
                        </div>
                    </label>
                    <div class="flex flex-col justify-center">
                        <div class="flex flex-col justify-center">
                            <div class="flex items-center justify-between gap-3 mb-1">
                                <label class="block text-[10px] font-black text-gray-400 uppercase ml-1">
                                    Nb de questions par tentative
                                </label>

                                <span class="inline-flex items-center gap-2 text-[10px] font-extrabold uppercase tracking-wider
                                            bg-white border border-gray-200 rounded-full px-3 py-1 text-gray-600">
                                    Questions créées :
                                    <span class="text-bleuone">
                                        {{ $quizQuestionsCount ?? ($mlecture->quiz_questions_count ?? 0) }}
                                    </span>
                                </span>
                            </div>

                            <input
                                type="number"
                                name="quiz_questions_per_attempt"
                                value="{{ old('quiz_questions_per_attempt', $mlecture->quiz_questions_per_attempt) }}"
                                min="0"
                                class="w-full px-4 py-2 bg-white border border-gray-200 rounded-xl font-bold focus:ring-2 focus:ring-orangeone"
                            >
                        </div>

                    </div>
                </div>
            </div>

            {{-- IMPORTANT : On stocke le chemin scorm_path ici pour le formulaire principal --}}
            <input type="hidden" name="scorm_path" value="{{ $currentScormPath }}">

            {{-- Footer Actions --}}
            <div class="flex flex-col md:flex-row items-center justify-end gap-4 pt-6 border-t border-gray-100">
                <button type="submit" name="save_action" value="stay" class="btn-action w-full md:w-auto px-8 py-4 bg-gray-100 text-gray-600 hover:bg-gray-200">
                    Enregistrer
                </button>

                <button type="submit" name="save_action" value="back" class="btn-action w-full md:w-auto px-10 py-4 bg-orangeone text-white shadow-lg shadow-orangeone/20 hover:opacity-90">
                    Enregistrer et quitter
                </button>
            </div>
        </form>
    </div>
</div>

{{-- FORMULAIRE D'IMPORT SCORM (Indépendant et situé HORS du form principal) --}}
<form id="form-import-scorm" method="POST" action="{{ route('admin.scorm.import') }}" enctype="multipart/form-data" class="hidden">
    @csrf
    <input type="hidden" name="lecture_id" value="{{ $mlecture->id }}">
</form>

@endsection