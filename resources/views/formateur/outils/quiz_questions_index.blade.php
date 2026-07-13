@extends('formateur.dashboard')

@section('formateur')
<div class="w-full px-6 lg:px-8"
     x-data="{
       modules: {{ Js::from($toolModules) }},
       moduleId: '',
       lectureId: '',
       get selectedModule()  { return this.modules.find(m => String(m.id) === String(this.moduleId)) || null; },
       get lectures()        { return this.selectedModule?.lectures || []; },
       get selectedLecture() { return this.lectures.find(l => String(l.id) === String(this.lectureId)) || null; },
       get canManage()       { return !!this.selectedLecture; },
       syncLecture() {
         if (!this.lectures.some(l => String(l.id) === String(this.lectureId))) {
           this.lectureId = this.lectures[0]?.id ?? '';
         }
       },
       init() {
         this.$watch('moduleId', () => this.syncLecture());
       }
     }">

  {{-- En-tête --}}
  <header class="bg-white rounded-[20px] shadow-md px-8 pt-5 pb-6 my-6">
    <nav class="text-sm font-varela text-gray-500 mb-2">
      <ol class="inline-flex items-center space-x-1">
        <li>
          <a href="{{ route('formateur.outils.index') }}" class="text-orangeone hover:underline">Outils numériques</a>
        </li>
        <li><span class="mx-2 text-gray-400">/</span></li>
        <li class="text-gray-400">Banque de questions de quiz</li>
      </ol>
    </nav>
    <p class="font-raleway text-2xl text-bleuone">Banque de questions de quiz</p>
    <p class="text-sm text-gray-500 mt-1">
      Choisissez une formation puis une leçon pour créer, modifier ou importer les questions de son quiz.
    </p>
  </header>

  <div class="bg-white rounded-[20px] shadow-md p-6 max-w-2xl">
    @if($toolModules->isEmpty())
      <div class="rounded-[10px] bg-amber-50 border border-amber-200 px-4 py-3 text-sm text-amber-700">
        Vous n'avez pas encore de formation avec des leçons. Créez d'abord une formation dans "Mes créations".
      </div>
    @else
      <div class="space-y-4">
        <div>
          <label class="block text-xs font-semibold text-gray-600 mb-1">Formation</label>
          <select x-model="moduleId" required
                  class="w-full rounded-[10px] border border-gray-300 px-3 py-2.5 text-sm focus:border-bleuone focus:outline-none focus:ring-2 focus:ring-bleuone/20">
            <option value="">Choisir une formation…</option>
            <template x-for="m in modules" :key="m.id">
              <option :value="String(m.id)" x-text="m.title"></option>
            </template>
          </select>
        </div>

        <div>
          <label class="block text-xs font-semibold text-gray-600 mb-1">Leçon</label>
          <select x-model="lectureId" :disabled="!lectures.length" required
                  class="w-full rounded-[10px] border border-gray-300 px-3 py-2.5 text-sm focus:border-bleuone focus:outline-none focus:ring-2 focus:ring-bleuone/20 disabled:opacity-50">
            <option value="">Choisir une leçon…</option>
            <template x-for="l in lectures" :key="l.id">
              <option :value="String(l.id)" x-text="l.label + (l.questions_count ? ' (' + l.questions_count + ' question(s))' : '')"></option>
            </template>
          </select>
          <p class="mt-1 text-[10px] text-gray-400" x-show="selectedLecture && !selectedLecture.quiz_enabled" x-cloak>
            Le quiz n'est pas encore activé pour cette leçon (réglable depuis la leçon, onglet "Quiz").
          </p>
        </div>

        <a :href="canManage ? selectedLecture.manage_url : '#'"
           :class="canManage ? 'bg-orangeone hover:bg-orangeone-hover' : 'bg-gray-300 cursor-not-allowed pointer-events-none'"
           class="inline-flex w-full items-center justify-center gap-2 rounded-[10px] px-4 py-2.5 text-sm font-bold text-white transition">
          Gérer la banque de questions
        </a>
      </div>
    @endif
  </div>

</div>
@endsection
