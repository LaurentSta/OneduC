@extends('stagiaire.formations.master_lecon_evaluation')

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
        if (nextLecture.section_id === currentSectionId) {
            nextUrl = `/stagiaire/modules/${moduleId}/sections/${nextLecture.section_id}/lessons/${nextLecture.id}`;
        } else {
            nextUrl = `/stagiaire/modules/${moduleId}/sections/${nextLecture.section_id}`;
        }
    }
    const finalUrl = `/stagiaire/modules/${moduleId}/fin`; // nouvelle page de fin

    window.SCORM_CONTEXT = {
        lecture_id: currentLectureId,
        next_url: nextUrl,
        goToNextLesson: function () {
            if (this.next_url !== "#") {
                window.location.href = this.next_url;
            } else {
                window.location.href = finalUrl; // redirige vers la page de félicitations
            }
        }
    };
</script>







 @endsection
