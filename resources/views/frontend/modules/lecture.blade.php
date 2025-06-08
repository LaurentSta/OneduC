@extends('frontend.modules.master_lecture')

@section('content')
<meta name="csrf-token" content="{{ csrf_token() }}">
@if (isset($selectedLecture))
    <script>
        window.currentLectureId = {{ $selectedLecture->id }};
    </script>
@endif
    {{-- LECTURE SCORM --}}
    <main class="flex-1 bg-white">
        @if (isset($selectedLecture) && $selectedLecture->scorm_path)
            {{-- CONTEXTE SCORM --}}
            <script>
                window.SCORM_CONTEXT = {
                    lecture_id: {{ $selectedLecture->id }},
                    next_url: "{{ route('module.lecture', ['id' => $module->id, 'lecon' => $nextLecture->id ?? 0]) }}"
                };
            </script>

            <iframe
                title="Contenu de la leçon"
                src="{{ asset('modules/scorm/00_Lecons/' . $selectedLecture->scorm_path . '/res/index.html') }}"
                frameborder="0"
                allowfullscreen
                class="w-full"
                style="height: calc(100vh - 64px); display: block;">
            </iframe>


            @auth
            <div class="max-w-4xl mx-auto px-4 pt-0 pb-2">
                <h2 class="text-xl font-bold text-gray-800 mb-3">💬 Une remarque ? Une question ?</h2>
                {{-- ✅ FORMULAIRE --}}
                <form action="{{ route('feedback.store') }}" method="POST" class="bg-gray-100 p-4 rounded mb-4">
                    @csrf
                    <input type="hidden" name="lesson_id" value="{{ $selectedLecture->id }}">
                    {{-- Ligne Type + Urgence --}}
<div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
    <div>
        <label for="type" class="block font-medium text-gray-700 mb-1">Type de retour</label>
        <select name="type" id="type" class="w-full border-gray-300 rounded px-3 py-2 focus:ring-blue-500 focus:border-blue-500">
            <option value="">Choisir un type</option>
            <option value="bug">Bug technique</option>
            <option value="erreur">Erreur dans le contenu</option>
            <option value="incomprehension">Incompréhension</option>
            <option value="suggestion">Suggestion</option>
        </select>
    </div>
    <div>
        <label for="urgency" class="block font-medium text-gray-700 mb-1">Niveau d'urgence</label>
        <select name="urgency" id="urgency" class="w-full border-gray-300 rounded px-3 py-2 focus:ring-blue-500 focus:border-blue-500">
            <option value="">Non précisé</option>
            <option value="1">1 – Non prioritaire</option>
            <option value="2">2 – Mineur</option>
            <option value="3">3 – Moyen</option>
            <option value="4">4 – Important</option>
            <option value="5">5 – Très urgent ⚠️</option>
        </select>
    </div>
</div>

{{-- Message --}}
<div class="mb-4">
    <label for="comment" class="block font-medium text-gray-700 mb-1">Votre message</label>
    <textarea name="comment" id="comment"
    class="w-full h-[95px] resize-none border-gray-300 rounded px-3 py-2 focus:ring-blue-500 focus:border-blue-500"
    required>{{ old('comment') }}</textarea>
</div>

{{-- Note --}}
<div class="mb-4">
    <label for="rating" class="block font-medium text-gray-700 mb-1">Note (facultative)</label>
    <select name="rating" id="rating" class="border-gray-300 rounded px-3 py-2 w-32 focus:ring-blue-500 focus:border-blue-500">
        <option value="">Aucune</option>
        @for ($i = 1; $i <= 5; $i++)
            <option value="{{ $i }}">{{ $i }} ★</option>
        @endfor
    </select>
</div>

{{-- Bouton --}}
<div>
    <button type="submit" class="btn-oneduc">
        Envoyer mon retour
    </button>

</div>
                </form>
                {{-- ✅ AFFICHAGE DES RETOURS --}}
                @if ($selectedLecture->feedbacks->count())
                    <h3 class="text-lg font-semibold text-gray-700 mb-3">Retours précédents</h3>
                    <div class="space-y-4">
                        @foreach ($selectedLecture->feedbacks->sortByDesc('created_at') as $feedback)
                            <div class="border-b pb-3">
                                <p class="text-sm text-gray-600">
                                    <strong>{{ $feedback->user->name }}</strong> –
                                    {{ \Illuminate\Support\Carbon::parse($feedback->created_at)->translatedFormat('l d F Y') }}

                                    @if($feedback->rating)
                                        <span class="ml-2 text-yellow-500">{{ $feedback->rating }}★</span>
                                    @endif

                                    @if($feedback->type)
                                        <span class="ml-2 text-xs bg-gray-200 px-2 py-1 rounded text-gray-600">{{ ucfirst($feedback->type) }}</span>
                                    @endif

                                    @if($feedback->urgency)
                                        @php
                                            $urgencyLabels = [
                                                1 => ['label' => 'Non prioritaire', 'icon' => 'ℹ️', 'class' => 'bg-gray-100 text-gray-700'],
                                                2 => ['label' => 'Mineur',         'icon' => '🔹', 'class' => 'bg-blue-100 text-blue-700'],
                                                3 => ['label' => 'Moyen',          'icon' => '🕒', 'class' => 'bg-yellow-100 text-yellow-800'],
                                                4 => ['label' => 'Important',      'icon' => '⏱️', 'class' => 'bg-orange-100 text-orange-700'],
                                                5 => ['label' => 'Très urgent',    'icon' => '⚠️', 'class' => 'bg-red-100 text-red-700'],
                                            ];
                                            $urgency = $urgencyLabels[$feedback->urgency] ?? null;
                                        @endphp

                                        @if($urgency)
                                            <span class="ml-2 text-xs px-2 py-1 rounded inline-flex items-center {{ $urgency['class'] }}">
                                                <span class="mr-1">{{ $urgency['icon'] }}</span>
                                                {{ $urgency['label'] }}
                                            </span>
                                        @endif
                                    @endif
                                </p>

                                <p class="mt-1 text-gray-800">{{ $feedback->comment }}</p>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-gray-500 italic">Aucun retour pour le moment.</p>
                @endif
            </div>
            @endauth


            </div>
        @endif

    </main>
   <script>
    const currentLectureId = {{ $selectedLecture->id }};
    const currentSectionId = {{ $selectedLecture->section_id }};
    const nextLecture = @json($nextLecture);
    const moduleId = {{ $module->id }};

    let nextUrl = "#";

    if (nextLecture) {
        // Si la prochaine leçon est dans la même section → on continue
        if (nextLecture.section_id === currentSectionId) {
            nextUrl = `/formation/module/${moduleId}/lecture?lecon=${nextLecture.id}`;
        } else {
            // Sinon → on passe par la vue section
            nextUrl = `/module/${moduleId}/section/${nextLecture.section_id}`;
        }
    }

    window.SCORM_CONTEXT = {
        lecture_id: currentLectureId,
        next_url: nextUrl,
        goToNextLesson: function () {
            if (this.next_url !== "#") {
                window.location.href = this.next_url;
            } else {
                alert("🎓 Module terminé !");
            }
        }
    };
</script>





 @endsection
