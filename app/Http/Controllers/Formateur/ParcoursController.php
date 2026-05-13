<?php

namespace App\Http\Controllers\Formateur;

use App\Data\ParcoursFormateur;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ParcoursController extends Controller
{
    public function index()
    {
        $catalogue = $this->catalogue();

        return view('formateur.parcours.index', [
            'pageTitle' => 'Parcours formateur',
            'parcoursModules' => $catalogue,
            'activeModuleKey' => null,
            'activeChapterKey' => null,
            'activeLessonKey' => null,
            'breadcrumbs' => [
                ['label' => 'Parcours formateur', 'url' => route('formateur.parcours.index')],
            ],
        ]);
    }

	    public function showModule(string $module)
	    {
	        $catalogue = $this->catalogue();
	        abort_unless(isset($catalogue[$module]), 404);

        $currentModule = $catalogue[$module];

        return view('formateur.parcours.module', [
            'pageTitle' => $currentModule['title'],
            'parcoursModules' => $catalogue,
            'activeModuleKey' => $module,
            'activeChapterKey' => null,
            'activeLessonKey' => null,
            'currentModule' => $currentModule,
            'breadcrumbs' => [
                ['label' => 'Parcours formateur', 'url' => route('formateur.parcours.index')],
                ['label' => $currentModule['title'], 'url' => $currentModule['url']],
            ],
	        ]);
	    }

	    public function showModuleIntroduction(string $module)
	    {
	        $catalogue = $this->catalogue();
	        abort_unless(isset($catalogue[$module]), 404);

	        $currentModule = $catalogue[$module];

        return view('formateur.parcours.introduction', [
            'pageTitle' => 'Introduction - ' . $currentModule['title'],
            'parcoursModules' => $catalogue,
	            'activeModuleKey' => $module,
	            'activeChapterKey' => null,
            'activeLessonKey' => null,
            'currentModule' => $currentModule,
            'introductionScormUrl' => $this->resolveScormUrl($currentModule['introduction_scorm_directory'] ?? null),
            'breadcrumbs' => [
                ['label' => 'Parcours formateur', 'url' => route('formateur.parcours.index')],
                ['label' => $currentModule['title'], 'url' => $currentModule['url']],
	                ['label' => 'Introduction', 'url' => $currentModule['introduction_url']],
	            ],
	        ]);
	    }

    public function showChapter(string $module, string $chapter)
    {
        $catalogue = $this->catalogue();
        abort_unless(isset($catalogue[$module]), 404);
        abort_unless(isset($catalogue[$module]['chapters'][$chapter]), 404);

        $currentModule = $catalogue[$module];
        $currentChapter = $currentModule['chapters'][$chapter];

        return view('formateur.parcours.chapter', [
            'pageTitle' => $currentChapter['title'],
            'parcoursModules' => $catalogue,
            'activeModuleKey' => $module,
            'activeChapterKey' => $chapter,
            'activeLessonKey' => null,
            'currentModule' => $currentModule,
            'currentChapter' => $currentChapter,
            'breadcrumbs' => [
                ['label' => 'Parcours formateur', 'url' => route('formateur.parcours.index')],
                ['label' => $currentModule['title'], 'url' => $currentModule['url']],
                ['label' => $currentChapter['title'], 'url' => $currentChapter['url']],
            ],
        ]);
    }

    public function showLesson(string $module, string $chapter, string $lesson)
    {
        return $this->renderLesson($module, $chapter, $lesson);
    }

    public function showLessonPart(string $module, string $chapter, string $lesson, string $part)
    {
        return $this->renderLesson($module, $chapter, $lesson, $part);
    }

    private function renderLesson(string $module, string $chapter, string $lesson, ?string $part = null)
    {
        $catalogue = $this->catalogue();
        $context = $this->resolveLessonContext($catalogue, $module, $chapter, $lesson);
        $currentPart = $this->resolveLessonPart($context['currentLesson'], $part);
        $this->markLessonPartCompleted($module, $chapter, $lesson, $context['currentLesson'], $currentPart);

        return view('formateur.parcours.lesson', [
            'pageTitle' => $context['currentLesson']['title'],
            'parcoursModules' => $catalogue,
            'activeModuleKey' => $module,
            'activeChapterKey' => $chapter,
            'activeLessonKey' => $lesson,
            'activeActivityKey' => null,
            'currentModule' => $context['currentModule'],
            'currentChapter' => $context['currentChapter'],
            'currentLesson' => $context['currentLesson'],
            'previousLesson' => $context['previousLesson'],
            'nextLesson' => $context['nextLesson'],
            'nextActivity' => $context['nextActivity'],
            'activeLessonPart' => $currentPart,
            'activityStatusMap' => $this->loadActivityStatusMap($module),
            'breadcrumbs' => [
                ['label' => 'Parcours formateur', 'url' => route('formateur.parcours.index')],
                ['label' => $context['currentModule']['title'], 'url' => $context['currentModule']['url']],
                ['label' => $context['currentChapter']['title'], 'url' => $context['currentChapter']['url']],
                ['label' => $context['currentLesson']['code'], 'url' => $context['currentLesson']['url']],
            ],
        ]);
    }

    private function resolveLessonPart(array $lesson, ?string $part): ?string
    {
        if (($lesson['layout'] ?? null) !== 'scorm_form') {
            abort_if($part !== null, 404);

            return null;
        }

        $availableParts = array_keys($lesson['scorm_parts'] ?? []);
        $part ??= $availableParts[0] ?? null;
        abort_unless($part && in_array($part, $availableParts, true), 404);

        return $part;
    }

    private function markLessonPartCompleted(string $module, string $chapter, string $lessonKey, array $lesson, ?string $part): void
    {
        if (! auth()->check() || $part === null) {
            return;
        }

        $partConfig = $lesson['scorm_parts'][$part] ?? null;
        $activityKey = $lesson['completion_activity_key'] ?? null;

        if (! is_array($partConfig) || empty($partConfig['marks_completion']) || ! is_string($activityKey) || $activityKey === '') {
            return;
        }

        $now = now();

        DB::table('trainer_path_activity_attempts')->updateOrInsert(
            [
                'user_id' => auth()->id(),
                'module_key' => $module,
                'chapter_key' => $chapter,
                'lesson_key' => $lessonKey,
                'activity_key' => $activityKey,
            ],
            [
                'activity_type' => 'guided_group_creation',
                'total_items' => 1,
                'correct_items' => 1,
                'is_success' => true,
                'submitted_answer' => json_encode([
                    'completed_part' => $part,
                    'completed_at' => $now->toIso8601String(),
                ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                'expected_answer' => json_encode([
                    'required_part' => $part,
                ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                'wrong_items' => json_encode([], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                'submitted_at' => $now,
                'created_at' => $now,
                'updated_at' => $now,
            ]
        );
    }

    public function showActivity(string $module, string $chapter, string $lesson, string $activity)
    {
        $catalogue = $this->catalogue();
        $context = $this->resolveLessonContext($catalogue, $module, $chapter, $lesson);
        $currentActivity = $context['currentLesson']['activity_page'] ?? null;

        abort_unless(
            is_array($currentActivity) && ($currentActivity['key'] ?? null) === $activity,
            404
        );

        $latestSuccessfulAttempt = DB::table('trainer_path_activity_attempts')
            ->where('user_id', auth()->id())
            ->where('module_key', $module)
            ->where('chapter_key', $chapter)
            ->where('lesson_key', $lesson)
            ->where('activity_key', $activity)
            ->where('is_success', true)
            ->latest('submitted_at')
            ->first();

        $initialPlacements = [];

        if ($latestSuccessfulAttempt) {
            $submittedAnswer = $this->decodeJsonColumn($latestSuccessfulAttempt->submitted_answer ?? null);
            $initialPlacements = is_array($submittedAnswer['placements'] ?? null)
                ? $submittedAnswer['placements']
                : [];
        }

        return view('formateur.parcours.activity', [
            'pageTitle' => $currentActivity['title'],
            'parcoursModules' => $catalogue,
            'activeModuleKey' => $module,
            'activeChapterKey' => $chapter,
            'activeLessonKey' => $lesson,
            'activeActivityKey' => $activity,
            'currentModule' => $context['currentModule'],
            'currentChapter' => $context['currentChapter'],
            'currentLesson' => $context['currentLesson'],
            'currentActivity' => $currentActivity,
            'nextLesson' => $context['nextLesson'],
            'activityStatusMap' => $this->loadActivityStatusMap($module),
            'activityCompleted' => ! is_null($latestSuccessfulAttempt),
            'initialPlacements' => $initialPlacements,
            'breadcrumbs' => [
                ['label' => 'Parcours formateur', 'url' => route('formateur.parcours.index')],
                ['label' => $context['currentModule']['title'], 'url' => $context['currentModule']['url']],
                ['label' => $context['currentChapter']['title'], 'url' => $context['currentChapter']['url']],
                ['label' => $context['currentLesson']['code'], 'url' => $context['currentLesson']['url']],
                ['label' => $currentActivity['code'] ?? 'Activite', 'url' => $currentActivity['url']],
            ],
        ]);
    }

    public function submitActivity(Request $request, string $module, string $chapter, string $lesson, string $activity): JsonResponse
    {
        $catalogue = $this->catalogue();
        $context = $this->resolveLessonContext($catalogue, $module, $chapter, $lesson);
        $currentActivity = $context['currentLesson']['activity_page'] ?? null;

        abort_unless(
            is_array($currentActivity) && ($currentActivity['key'] ?? null) === $activity,
            404
        );

        $placementsInput = $request->input('placements', []);

        if (! is_array($placementsInput)) {
            return response()->json([
                'success' => false,
                'message' => 'Les placements transmis sont invalides.',
                'wrong_item_ids' => [],
                'missing_item_ids' => [],
                'wrong_items' => [],
                'next_url' => $context['nextLesson']['url'] ?? $context['currentChapter']['url'],
            ], 422);
        }

        $items = collect($currentActivity['items'] ?? []);
        $dropzones = collect($currentActivity['dropzones'] ?? []);
        $validItemIds = $items->pluck('id')->map(fn ($id) => (string) $id)->all();
        $expectedCategories = $items->mapWithKeys(
            fn (array $item): array => [(string) $item['id'] => (string) $item['category']]
        )->all();
        $labelsByItem = $items->mapWithKeys(
            fn (array $item): array => [(string) $item['id'] => (string) $item['label']]
        )->all();

        $placements = [];
        foreach ($dropzones as $dropzone) {
            $zoneId = (string) ($dropzone['id'] ?? '');
            if ($zoneId === '') {
                continue;
            }

            $zoneItems = $placementsInput[$zoneId] ?? [];
            $placements[$zoneId] = collect(is_array($zoneItems) ? $zoneItems : [])
                ->map(fn ($value) => (string) $value)
                ->filter(fn (string $value): bool => in_array($value, $validItemIds, true))
                ->unique()
                ->values()
                ->all();
        }

        $submittedCategories = [];
        foreach ($placements as $zoneId => $zoneItems) {
            foreach ($zoneItems as $itemId) {
                $submittedCategories[$itemId] ??= $zoneId;
            }
        }

        $wrongItems = [];
        $correctItemIds = [];

        foreach ($expectedCategories as $itemId => $expectedCategory) {
            $actualCategory = $submittedCategories[$itemId] ?? null;

            if ($actualCategory === $expectedCategory) {
                $correctItemIds[] = $itemId;
                continue;
            }

            $wrongItems[] = [
                'id' => $itemId,
                'label' => $labelsByItem[$itemId] ?? $itemId,
                'expected' => $expectedCategory,
                'actual' => $actualCategory,
            ];
        }

        $missingItemIds = array_values(array_filter(
            array_keys($expectedCategories),
            fn (string $itemId): bool => ! array_key_exists($itemId, $submittedCategories)
        ));

        $isSuccess = empty($wrongItems);
        $message = $isSuccess
            ? (string) ($currentActivity['success_message'] ?? 'Bravo, l activite est validee.')
            : (empty($missingItemIds)
                ? 'Quelques elements sont a revoir avant de poursuivre.'
                : 'Placez tous les elements dans un bloc avant de valider.');

        $now = now();

        DB::table('trainer_path_activity_attempts')->insert([
            'user_id' => auth()->id(),
            'module_key' => $module,
            'chapter_key' => $chapter,
            'lesson_key' => $lesson,
            'activity_key' => $activity,
            'activity_type' => 'sorting',
            'total_items' => count($expectedCategories),
            'correct_items' => count($correctItemIds),
            'is_success' => $isSuccess,
            'submitted_answer' => json_encode([
                'placements' => $placements,
                'submitted_categories' => $submittedCategories,
                'missing_items' => $missingItemIds,
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            'expected_answer' => json_encode([
                'categories' => $expectedCategories,
                'dropzones' => $dropzones->mapWithKeys(
                    fn (array $dropzone): array => [(string) $dropzone['id'] => (string) ($dropzone['label'] ?? $dropzone['id'])]
                )->all(),
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            'wrong_items' => json_encode($wrongItems, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            'submitted_at' => $now,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        return response()->json([
            'success' => $isSuccess,
            'message' => $message,
            'wrong_item_ids' => array_values(array_map(
                fn (array $item): string => (string) $item['id'],
                $wrongItems
            )),
            'missing_item_ids' => $missingItemIds,
            'wrong_items' => $wrongItems,
            'correct_item_ids' => $correctItemIds,
            'next_url' => $context['nextLesson']['url'] ?? $context['currentChapter']['url'],
        ]);
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    private function catalogue(): array
    {
        $modules = ParcoursFormateur::rawModules();

	        foreach ($modules as $moduleKey => &$module) {
	            $module['url'] = route('formateur.parcours.modules.show', ['module' => $moduleKey]);
	            $module['introduction_url'] = route('formateur.parcours.modules.introduction', ['module' => $moduleKey]);
	            $module['chapter_count'] = count($module['chapters']);
            $module['lesson_count'] = array_reduce(
                $module['chapters'],
                fn (int $carry, array $chapter): int => $carry + count($chapter['lessons'] ?? []),
                0
            );

	            $firstChapterKey = array_key_first($module['chapters']);
	            $module['first_chapter_url'] = $firstChapterKey
	                ? route('formateur.parcours.chapters.show', ['module' => $moduleKey, 'chapter' => $firstChapterKey])
	                : $module['url'];
	            $module['entry_url'] = $firstChapterKey ? $module['introduction_url'] : $module['url'];

            foreach ($module['chapters'] as $chapterKey => &$chapter) {
                $chapter['url'] = route('formateur.parcours.chapters.show', [
                    'module' => $moduleKey,
                    'chapter' => $chapterKey,
                ]);
                $chapter['lesson_count'] = count($chapter['lessons']);

                $firstLessonKey = array_key_first($chapter['lessons']);
                $chapter['first_lesson_url'] = $firstLessonKey
                    ? route('formateur.parcours.lessons.show', [
                        'module' => $moduleKey,
                        'chapter' => $chapterKey,
                        'lesson' => $firstLessonKey,
                    ])
                    : $chapter['url'];

                foreach ($chapter['lessons'] as $lessonKey => &$lesson) {
                    $lesson['url'] = route('formateur.parcours.lessons.show', [
                        'module' => $moduleKey,
                        'chapter' => $chapterKey,
                        'lesson' => $lessonKey,
                    ]);

                    if (($lesson['layout'] ?? null) === 'scorm_form') {
                        $lesson['part_urls'] = [];
                        $lesson['scorm_part_urls'] = [];

                        foreach ($lesson['scorm_parts'] ?? [] as $partKey => $partConfig) {
                            $lesson['part_urls'][$partKey] = route('formateur.parcours.lessons.part', [
                                'module' => $moduleKey,
                                'chapter' => $chapterKey,
                                'lesson' => $lessonKey,
                                'part' => $partKey,
                            ]);
                            $lesson['scorm_part_urls'][$partKey] = $this->resolveScormUrl($partConfig['directory'] ?? null);
                        }

                        $firstPartKey = array_key_first($lesson['part_urls']);
                        $lesson['url'] = $firstPartKey ? $lesson['part_urls'][$firstPartKey] : $lesson['url'];
                    }

                    if (! empty($lesson['activity_page'])) {
                        $lesson['activity_page']['key'] = $lesson['activity_page']['key'] ?? 'activite';
                        $lesson['activity_page']['url'] = route('formateur.parcours.activities.show', [
                            'module' => $moduleKey,
                            'chapter' => $chapterKey,
                            'lesson' => $lessonKey,
                            'activity' => $lesson['activity_page']['key'],
                        ]);
                    }

                    $lesson['intro_scorm_url'] = $this->resolveScormUrl($lesson['intro_scorm_directory'] ?? null);
                    $lesson['scorm_url'] = $this->resolveScormUrl($lesson['scorm_directory'] ?? null);
                }
                unset($lesson);
            }
            unset($chapter);
        }
        unset($module);

        return $modules;
    }

    private function resolveScormUrl(?string $directory): ?string
    {
        if (empty($directory)) {
            return null;
        }

        $directory = trim($directory, '/');
        $publicScormIndex = public_path($directory . '/index_lms.html');

        return file_exists($publicScormIndex)
            ? asset($directory . '/index_lms.html')
            : null;
    }

    /**
     * @param  array<string, array<string, mixed>>  $catalogue
     * @return array<string, mixed>
     */
    private function resolveLessonContext(array $catalogue, string $module, string $chapter, string $lesson): array
    {
        abort_unless(isset($catalogue[$module]), 404);
        abort_unless(isset($catalogue[$module]['chapters'][$chapter]), 404);
        abort_unless(isset($catalogue[$module]['chapters'][$chapter]['lessons'][$lesson]), 404);

        $currentModule = $catalogue[$module];
        $currentChapter = $currentModule['chapters'][$chapter];
        $currentLesson = $currentChapter['lessons'][$lesson];
        $lessonSequence = [];

        foreach ($currentModule['chapters'] as $chapterKey => $chapterItem) {
            foreach ($chapterItem['lessons'] as $lessonKey => $lessonItem) {
                $lessonSequence[] = [
                    'chapter_key' => $chapterKey,
                    'lesson_key' => $lessonKey,
                    'lesson' => $lessonItem,
                ];
            }
        }

        $lessonIndex = collect($lessonSequence)->search(
            fn (array $item): bool => $item['chapter_key'] === $chapter && $item['lesson_key'] === $lesson
        );

        $previousLesson = $lessonIndex !== false && isset($lessonSequence[$lessonIndex - 1])
            ? $lessonSequence[$lessonIndex - 1]['lesson']
            : null;

        $nextLesson = $lessonIndex !== false && isset($lessonSequence[$lessonIndex + 1])
            ? $lessonSequence[$lessonIndex + 1]['lesson']
            : null;

        return [
            'currentModule' => $currentModule,
            'currentChapter' => $currentChapter,
            'currentLesson' => $currentLesson,
            'previousLesson' => $previousLesson,
            'nextLesson' => $nextLesson,
            'nextActivity' => $currentLesson['activity_page'] ?? null,
        ];
    }

    /**
     * @return array<string, bool>
     */
    private function loadActivityStatusMap(string $moduleKey): array
    {
        if (! auth()->check()) {
            return [];
        }

        return DB::table('trainer_path_activity_attempts')
            ->where('user_id', auth()->id())
            ->where('module_key', $moduleKey)
            ->where('is_success', true)
            ->get(['chapter_key', 'lesson_key', 'activity_key'])
            ->mapWithKeys(fn ($row): array => [
                $this->activityStatusKey(
                    (string) $row->chapter_key,
                    (string) $row->lesson_key,
                    (string) $row->activity_key
                ) => true,
            ])
            ->all();
    }

    private function activityStatusKey(string $chapterKey, string $lessonKey, string $activityKey): string
    {
        return implode('.', [$chapterKey, $lessonKey, $activityKey]);
    }

    /**
     * @return array<string, mixed>
     */
    private function decodeJsonColumn(mixed $value): array
    {
        if (is_array($value)) {
            return $value;
        }

        if (! is_string($value) || $value === '') {
            return [];
        }

        $decoded = json_decode($value, true);

        return is_array($decoded) ? $decoded : [];
    }
}
