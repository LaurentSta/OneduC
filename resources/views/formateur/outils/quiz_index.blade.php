@extends('formateur.dashboard')

@section('formateur')
<div class="w-full px-6 lg:px-8"
     x-data="{
       groups: {{ Js::from($toolGroups) }},
       groupId: '',
       moduleId: '',
       lectureId: '',
       get selectedGroup()   { return this.groups.find(g => String(g.id) === String(this.groupId)) || null; },
       get modules()         { return this.selectedGroup?.modules || []; },
       get selectedModule()  { return this.modules.find(m => String(m.id) === String(this.moduleId)) || null; },
       get lectures()        { return this.selectedModule?.lectures || []; },
       get canLaunch()       { return this.groupId && this.moduleId && this.lectureId; },
       syncModule() {
         if (!this.modules.some(m => String(m.id) === String(this.moduleId))) {
           this.moduleId  = this.modules[0]?.id ?? '';
           this.lectureId = this.lectures[0]?.id ?? '';
         }
       },
       syncLecture() {
         if (!this.lectures.some(l => String(l.id) === String(this.lectureId))) {
           this.lectureId = this.lectures[0]?.id ?? '';
         }
       },
       init() {
         this.$watch('groupId',  () => this.syncModule());
         this.$watch('moduleId', () => this.syncLecture());
       }
     }">

  {{-- En-tête --}}
  <header class="bg-white rounded-[20px] shadow-md px-8 pt-5 pb-6 my-6">
    <div class="flex flex-wrap items-center justify-between gap-4">
      <div>
        <nav class="text-sm font-varela text-gray-500 mb-2">
          <ol class="inline-flex items-center space-x-1">
            <li>
              <a href="{{ route('formateur.outils.index') }}" class="text-orangeone hover:underline">Outils numériques</a>
            </li>
            <li><span class="mx-2 text-gray-400">/</span></li>
            <li class="text-gray-400">Quiz en direct</li>
          </ol>
        </nav>
        <p class="font-raleway text-2xl text-bleuone">Quiz en direct</p>
        <p class="text-sm text-gray-500 mt-1">
          Lancez une session synchronisée avec votre groupe. Les stagiaires répondent en temps réel.
        </p>
      </div>
    </div>
  </header>

  <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">

    {{-- ── Formulaire de lancement ──────────────────────────────── --}}
    <div class="lg:col-span-1">
      <div class="bg-white rounded-[20px] shadow-md p-6 sticky top-6">
        <p class="font-varela text-base font-bold text-bleuone mb-4">Nouvelle session</p>

        @if($toolGroups->isEmpty())
          <div class="rounded-[10px] bg-amber-50 border border-amber-200 px-4 py-3 text-sm text-amber-700">
            Aucun groupe avec quiz disponible. Assignez des modules contenant des questions actives à vos groupes.
          </div>
        @else
          <form method="POST"
                action="{{ route('formateur.live-quiz.launch') }}"
                target="_blank"
                class="space-y-4">
            @csrf
            <input type="hidden" name="group_id"   :value="groupId">
            <input type="hidden" name="module_id"  :value="moduleId">
            <input type="hidden" name="lecture_id" :value="lectureId">

            {{-- Groupe --}}
            <div>
              <label class="block text-xs font-semibold text-gray-600 mb-1">Groupe</label>
              <select x-model="groupId" required
                      class="w-full rounded-[10px] border border-gray-300 px-3 py-2.5 text-sm focus:border-[#004461] focus:outline-none focus:ring-2 focus:ring-[#004461]/20">
                <option value="">Choisir un groupe…</option>
                <template x-for="g in groups" :key="g.id">
                  <option :value="String(g.id)" x-text="g.name"></option>
                </template>
              </select>
            </div>

            {{-- Module --}}
            <div>
              <label class="block text-xs font-semibold text-gray-600 mb-1">Module</label>
              <select x-model="moduleId" :disabled="!modules.length" required
                      class="w-full rounded-[10px] border border-gray-300 px-3 py-2.5 text-sm focus:border-[#004461] focus:outline-none focus:ring-2 focus:ring-[#004461]/20 disabled:opacity-50">
                <option value="">Choisir un module…</option>
                <template x-for="m in modules" :key="m.id">
                  <option :value="String(m.id)" x-text="m.title"></option>
                </template>
              </select>
            </div>

            {{-- Leçon --}}
            <div>
              <label class="block text-xs font-semibold text-gray-600 mb-1">Leçon avec quiz</label>
              <select x-model="lectureId" :disabled="!lectures.length" required name="lecture_id"
                      class="w-full rounded-[10px] border border-gray-300 px-3 py-2.5 text-sm focus:border-[#004461] focus:outline-none focus:ring-2 focus:ring-[#004461]/20 disabled:opacity-50">
                <option value="">Choisir une leçon…</option>
                <template x-for="l in lectures" :key="l.id">
                  <option :value="String(l.id)" x-text="l.label"></option>
                </template>
              </select>
              <p class="mt-1 text-[10px] text-gray-400">Seules les leçons avec des questions actives sont listées.</p>
            </div>

            <button type="submit"
                    :disabled="!canLaunch"
                    :class="canLaunch ? 'bg-[#004461] hover:bg-[#005577]' : 'bg-gray-300 cursor-not-allowed'"
                    class="w-full inline-flex items-center justify-center gap-2 rounded-[10px] px-4 py-2.5 text-sm font-bold text-white transition">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
              </svg>
              Lancer la session
            </button>
          </form>

          <div class="mt-5 rounded-[10px] bg-[#004461]/5 border border-[#004461]/10 px-4 py-3">
            <p class="text-xs text-[#004461] font-semibold mb-1">Comment ça marche ?</p>
            <ol class="text-[11px] text-gray-600 space-y-1 list-decimal list-inside leading-relaxed">
              <li>Choisissez groupe, module et leçon</li>
              <li>Cliquez "Lancer" — le cockpit s'ouvre</li>
              <li>Les stagiaires scannent le QR code ou saisissent le code</li>
              <li>Pilotez les questions, affichez les corrections</li>
            </ol>
          </div>
        @endif
      </div>
    </div>

    {{-- ── Sessions récentes ────────────────────────────────────── --}}
    <div class="lg:col-span-2 space-y-4">

      @forelse($sessions as $session)
        @php
          $statusLabel = match($session->status) {
            'waiting'          => 'En attente',
            'question_open'    => 'Question en cours',
            'answer_revealed'  => 'Correction affichée',
            'closed'           => 'Terminée',
            default            => $session->status,
          };
          $statusColor = match($session->status) {
            'waiting'          => 'bg-yellow-100 text-yellow-700',
            'question_open'    => 'bg-blue-100 text-blue-700',
            'answer_revealed'  => 'bg-purple-100 text-purple-700',
            'closed'           => 'bg-gray-100 text-gray-500',
            default            => 'bg-gray-100 text-gray-500',
          };
          $cockpitUrl = route('formateur.live-quiz.show', [
            'module'  => $session->module_id,
            'section' => $session->section_id,
            'lecture' => $session->lecture_id,
            'session' => $session->id,
          ]);
        @endphp

        <div class="bg-white rounded-[20px] shadow-md p-5 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
          <div class="flex items-start gap-3 min-w-0">
            <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl
              {{ $session->status === 'closed' ? 'bg-gray-100 text-gray-400' : 'bg-[#004461]/10 text-[#004461]' }}">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
              </svg>
            </div>
            <div class="min-w-0">
              <div class="flex flex-wrap items-center gap-2 mb-0.5">
                <p class="text-sm font-bold text-gray-900 truncate">
                  {{ $session->lecture?->lecture_title ?? 'Leçon inconnue' }}
                </p>
                <span class="inline-flex items-center rounded-full px-2 py-0.5 text-[10px] font-bold {{ $statusColor }}">
                  {{ $statusLabel }}
                </span>
              </div>
              <p class="text-xs text-gray-500 truncate">
                @if($session->group)
                  Groupe : <span class="font-semibold">{{ $session->group->name }}</span> ·
                @endif
                Code : <span class="font-mono font-semibold text-[#004461]">{{ $session->access_code }}</span>
              </p>
              <p class="text-[10px] text-gray-400 mt-0.5">
                {{ $session->created_at->diffForHumans() }}
                @if($session->status !== 'closed')
                  · Q {{ $session->current_position }} / {{ $session->total_questions }}
                @endif
              </p>
            </div>
          </div>
          <div class="flex gap-2 shrink-0">
            @if($session->status !== 'closed')
              <a href="{{ $cockpitUrl }}"
                 class="inline-flex items-center gap-1.5 rounded-[8px] bg-[#004461] px-3 py-1.5 text-xs font-bold text-white hover:bg-[#005577] transition">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                </svg>
                Reprendre
              </a>
            @else
              <a href="{{ $cockpitUrl }}"
                 class="inline-flex items-center gap-1.5 rounded-[8px] border border-gray-300 bg-white px-3 py-1.5 text-xs font-bold text-gray-600 hover:bg-gray-50 transition">
                Résultats
              </a>
            @endif
          </div>
        </div>

      @empty
        <div class="flex flex-col items-center justify-center rounded-[20px] border-2 border-dashed border-gray-200 bg-white py-16 text-center">
          <div class="mb-3 flex h-12 w-12 items-center justify-center rounded-full bg-[#004461]/10 text-[#004461]">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
            </svg>
          </div>
          <p class="text-sm font-semibold text-gray-700">Aucune session lancée</p>
          <p class="text-xs text-gray-400 mt-1">Lancez votre première session avec le formulaire.</p>
        </div>
      @endforelse

    </div>
  </div>

</div>
@endsection
