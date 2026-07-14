{{-- resources/views/shared/lecture_quiz_activity.blade.php --}}
@php
    /** @var \App\Models\QuizQuestion $question */
    $type = (string) ($question->type ?? 'single');
    $options = $question->options;
    $correctIds = $options->where('is_correct', true)->pluck('id')->values()->all();
@endphp

<div
    x-data="{
        selected: null,
        selectedMultiple: [],
        submitted: false,
        correctIds: @js($correctIds),
        isFullyCorrect() {
            return this.correctIds.length === this.selectedMultiple.length
                && this.correctIds.every((id) => this.selectedMultiple.includes(id));
        },
    }"
    class="mb-6 rounded-2xl border border-gray-200 bg-white p-6"
>
    <p class="mb-2 text-xs font-semibold uppercase tracking-wider text-orangeone">Petite question</p>
    <p class="mb-4 text-lg leading-relaxed text-gray-900">{{ $question->question_text }}</p>

    @if(!empty($question->image_path))
        <div class="mb-4">
            <img src="{{ \Illuminate\Support\Facades\Storage::url($question->image_path) }}"
                 alt="{{ $question->image_alt ?? '' }}"
                 class="max-h-80 w-full rounded-xl border border-gray-200 object-contain">
        </div>
    @endif

    @if(!empty($question->audio_path))
        <div class="mb-4">
            <audio controls class="w-full">
                <source src="{{ asset($question->audio_path) }}">
            </audio>
            @if(!empty($question->audio_transcript))
                <details class="mt-2">
                    <summary class="cursor-pointer text-sm text-bleuone">Transcription</summary>
                    <div class="mt-2 whitespace-pre-line text-sm text-gray-700">{{ $question->audio_transcript }}</div>
                </details>
            @endif
        </div>
    @endif

    @if($type === 'multiple')
        <div class="space-y-2">
            @foreach($options as $opt)
                <label class="flex items-start gap-3 rounded-xl border p-3 transition"
                       :class="!submitted
                           ? 'border-gray-200 hover:bg-gray-50 cursor-pointer'
                           : ({{ $opt->is_correct ? 'true' : 'false' }}
                               ? 'border-green-500 bg-green-50'
                               : (selectedMultiple.includes({{ $opt->id }}) ? 'border-red-500 bg-red-50' : 'border-gray-200 opacity-60'))">
                    <input type="checkbox" class="mt-1" :disabled="submitted"
                           @change="$event.target.checked
                               ? selectedMultiple.push({{ $opt->id }})
                               : selectedMultiple = selectedMultiple.filter((id) => id !== {{ $opt->id }})">
                    <span class="text-gray-900">{{ $opt->option_text }}</span>
                </label>
            @endforeach
        </div>

        <div class="mt-4" x-show="!submitted">
            <button type="button"
                    class="btn-oneduc-blue !px-6 !py-2 !text-sm"
                    :disabled="selectedMultiple.length === 0"
                    @click="submitted = true; answered[{{ $segIndex }}] = true">
                Valider
            </button>
        </div>

        <p class="mt-4 text-sm font-semibold" x-cloak x-show="submitted"
           :class="isFullyCorrect() ? 'text-green-700' : 'text-red-700'"
           x-text="isFullyCorrect() ? 'Bonne réponse !' : 'Ce n\'est pas tout à fait ça.'">
        </p>
    @else
        {{-- single & boolean: one click both answers and opens the "Continuer" gate --}}
        <div class="space-y-2">
            @foreach($options as $opt)
                <button type="button"
                        class="flex w-full items-center gap-3 rounded-xl border p-3 text-left transition"
                        :class="selected === null
                            ? 'border-gray-200 hover:bg-gray-50'
                            : ({{ $opt->is_correct ? 'true' : 'false' }}
                                ? 'border-green-500 bg-green-50'
                                : (selected === {{ $opt->id }} ? 'border-red-500 bg-red-50' : 'border-gray-200 opacity-60'))"
                        :disabled="selected !== null"
                        @click="selected = {{ $opt->id }}; answered[{{ $segIndex }}] = true">
                    <span class="flex-1 text-gray-900">{{ $opt->option_text }}</span>
                    <i class="ti ti-check text-green-600" x-show="selected !== null && {{ $opt->is_correct ? 'true' : 'false' }}"></i>
                    <i class="ti ti-x text-red-600" x-show="selected === {{ $opt->id }} && !{{ $opt->is_correct ? 'true' : 'false' }}"></i>
                </button>
            @endforeach
        </div>
    @endif
</div>
