@extends('formateur.dashboard')

@section('formateur')
<div class="w-full px-6 lg:px-8"
     x-data="{
       modules: {{ Js::from($toolModules) }},
       moduleId: '',
       get selectedModule() { return this.modules.find(m => String(m.id) === String(this.moduleId)) || null; },
       get canManage()      { return !!this.selectedModule; },
       get manageUrl()      { return this.selectedModule?.manage_url || '#'; },
       get lecturesWithQuestions() { return (this.selectedModule?.lectures || []).filter(l => l.questions_count > 0); },
       get modulesWithQuestions()  { return this.modules.filter(m => m.questions_count > 0); },
     }">

  {{-- En-tête --}}
  <header class="bg-white rounded-[20px] shadow-md px-8 pt-5 pb-6 my-6">
    <nav class="text-sm font-varela text-gray-500 mb-2">
      <ol class="inline-flex items-center space-x-1">
        <li>
          <a href="{{ route('formateur.modules.builder.index') }}" class="text-orangeone hover:underline">Mes créations</a>
        </li>
        <li><span class="mx-2 text-gray-400">/</span></li>
        <li class="text-gray-400">Questions de mes formations</li>
      </ol>
    </nav>
    <p class="font-raleway text-2xl text-bleuone">Questions de mes formations</p>
    <p class="text-sm text-gray-500 mt-1">
      Préparez les questions utilisées pour le quiz de validation d'une leçon ou pour une animation en direct.
    </p>
  </header>

  @if($toolModules->isEmpty())
    <div class="bg-white rounded-[20px] shadow-md p-6">
      <div class="rounded-[10px] bg-amber-50 border border-amber-200 px-4 py-3 text-sm text-amber-700">
        Vous n'avez pas encore de formation avec des leçons. Créez d'abord une formation dans "Mes créations".
      </div>
    </div>
  @else
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

      {{-- ── Colonne gauche : choix de la formation ──────────────────── --}}
      <div class="lg:col-span-1">
        <div class="bg-white rounded-[20px] shadow-md p-6 sticky top-6">
          <label class="block text-xs font-semibold text-gray-600 mb-1">Formation</label>
          <select x-model="moduleId" required
                  class="w-full rounded-[10px] border border-gray-300 px-3 py-2.5 text-sm focus:border-bleuone focus:outline-none focus:ring-2 focus:ring-bleuone/20">
            <option value="">Choisir une formation…</option>
            <template x-for="m in modules" :key="m.id">
              <option :value="String(m.id)" x-text="m.title"></option>
            </template>
          </select>
          <p class="mt-1 text-[10px] text-gray-400" x-show="selectedModule" x-cloak>
            <span x-text="selectedModule?.lectures_count"></span> leçon(s) · <span x-text="selectedModule?.questions_count"></span> question(s)
          </p>

          <a :href="canManage ? manageUrl : '#'"
             :class="canManage ? 'bg-orangeone hover:bg-orangeone-hover' : 'bg-gray-300 cursor-not-allowed pointer-events-none'"
             class="mt-4 inline-flex w-full items-center justify-center gap-2 rounded-[10px] px-4 py-2.5 text-sm font-bold text-white transition">
            Préparer les questions
          </a>
        </div>
      </div>

      {{-- ── Colonne droite : leçons (ou formations) ayant déjà des questions ────────── --}}
      <div class="lg:col-span-2">
        <template x-if="!selectedModule && modulesWithQuestions.length > 0">
          <div>
            <p class="text-xs font-semibold text-gray-500 mb-3">Formations ayant déjà des questions</p>
            <div class="space-y-3">
              <template x-for="m in modulesWithQuestions" :key="m.id">
                <button type="button" @click="moduleId = String(m.id)"
                        class="w-full flex items-center justify-between gap-3 bg-white rounded-[16px] shadow-sm border border-gray-100 px-5 py-4 hover:shadow-md transition text-left">
                  <span class="text-sm font-semibold text-bleuone" x-text="m.title"></span>
                  <span class="shrink-0 inline-flex items-center rounded-full bg-green-100 text-green-700 px-3 py-1 text-xs font-bold"
                        x-text="m.questions_count + ' question' + (m.questions_count > 1 ? 's' : '')"></span>
                </button>
              </template>
            </div>
          </div>
        </template>

        <template x-if="!selectedModule && modulesWithQuestions.length === 0">
          <div class="flex flex-col items-center justify-center rounded-[20px] border-2 border-dashed border-gray-200 bg-white py-20 text-center">
            <i class="ti ti-arrow-left text-3xl text-gray-300 mb-3"></i>
            <p class="text-sm font-semibold text-gray-700">Choisissez une formation</p>
            <p class="text-xs text-gray-400 mt-1">Aucune formation n'a encore de questions.</p>
          </div>
        </template>

        <template x-if="selectedModule && lecturesWithQuestions.length === 0">
          <div class="flex flex-col items-center justify-center rounded-[20px] border-2 border-dashed border-gray-200 bg-white py-20 text-center">
            <i class="ti ti-help-circle text-3xl text-gray-300 mb-3"></i>
            <p class="text-sm font-semibold text-gray-700">Aucune leçon n'a encore de questions</p>
            <p class="text-xs text-gray-400 mt-1">Utilisez « Préparer les questions » pour en créer.</p>
          </div>
        </template>

        <template x-if="selectedModule && lecturesWithQuestions.length > 0">
          <div class="space-y-3">
            <template x-for="l in lecturesWithQuestions" :key="l.id">
              <a :href="l.manage_url"
                 class="flex items-center justify-between gap-3 bg-white rounded-[16px] shadow-sm border border-gray-100 px-5 py-4 hover:shadow-md transition">
                <span class="text-sm font-semibold text-bleuone" x-text="l.label"></span>
                <span class="shrink-0 inline-flex items-center rounded-full bg-green-100 text-green-700 px-3 py-1 text-xs font-bold"
                      x-text="l.questions_count + ' question' + (l.questions_count > 1 ? 's' : '')"></span>
              </a>
            </template>
          </div>
        </template>
      </div>
    </div>
  @endif

</div>
@endsection
