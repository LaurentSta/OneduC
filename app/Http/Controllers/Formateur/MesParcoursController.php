<?php

namespace App\Http\Controllers\Formateur;

use App\Http\Controllers\Controller;
use App\Models\FormateurParcours;
use App\Models\FormateurParcoursItem;
use App\Models\Module;
use App\Models\PollSession;
use App\Models\WordCloud;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class MesParcoursController extends Controller
{
    public function index(): View
    {
        $parcours = FormateurParcours::query()
            ->where('formateur_id', auth()->id())
            ->withCount('items')
            ->orderByDesc('created_at')
            ->paginate(12);

        return view('formateur.mes-parcours.index', compact('parcours'));
    }

    public function create(): View
    {
        $availableModules = $this->buildAvailableModulesPayload();
        $toolTemplates    = $this->buildToolTemplatesPayload();

        return view('formateur.mes-parcours.create', [
            'availableModules' => $availableModules,
            'toolTemplates'    => $toolTemplates,
            'selectedItems'    => [],
            'storeUrl'         => route('formateur.mes-parcours.store'),
            'method'           => 'POST',
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate($this->validationRules());

        $parcours = DB::transaction(function () use ($request, $data) {
            $parcours = FormateurParcours::create([
                'formateur_id' => auth()->id(),
                'title'        => trim($data['title']),
                'description'  => $data['description'] ?? null,
            ]);

            $this->syncItems($parcours, $data['items'] ?? []);

            return $parcours;
        });

        return response()->json([
            'success'      => true,
            'redirect_url' => route('formateur.mes-parcours.show', $parcours),
        ]);
    }

    public function show(FormateurParcours $parcours): View
    {
        $this->authorizeOwnership($parcours);

        $parcours->load('items.module');

        $selectedItems = $this->buildSelectedItemsPayload($parcours);

        return view('formateur.mes-parcours.show', compact('parcours', 'selectedItems'));
    }

    public function edit(FormateurParcours $parcours): View
    {
        $this->authorizeOwnership($parcours);

        $parcours->load('items.module');

        $availableModules = $this->buildAvailableModulesPayload();
        $toolTemplates    = $this->buildToolTemplatesPayload();
        $selectedItems    = $this->buildSelectedItemsPayload($parcours);

        return view('formateur.mes-parcours.edit', [
            'parcours'        => $parcours,
            'availableModules' => $availableModules,
            'toolTemplates'    => $toolTemplates,
            'selectedItems'    => $selectedItems,
            'storeUrl'         => route('formateur.mes-parcours.update', $parcours),
            'method'           => 'PUT',
        ]);
    }

    public function update(Request $request, FormateurParcours $parcours): JsonResponse
    {
        $this->authorizeOwnership($parcours);

        $data = $request->validate($this->validationRules());

        DB::transaction(function () use ($data, $parcours) {
            $parcours->update([
                'title'       => trim($data['title']),
                'description' => $data['description'] ?? null,
            ]);

            $this->syncItems($parcours, $data['items'] ?? []);
        });

        return response()->json([
            'success'      => true,
            'redirect_url' => route('formateur.mes-parcours.show', $parcours),
        ]);
    }

    public function destroy(FormateurParcours $parcours): RedirectResponse
    {
        $this->authorizeOwnership($parcours);

        $parcours->delete();

        return redirect()
            ->route('formateur.mes-parcours.index')
            ->with('success', 'Formation supprimée.');
    }

    // -------------------------------------------------------------------------

    private function authorizeOwnership(FormateurParcours $parcours): void
    {
        abort_unless($parcours->formateur_id === (int) auth()->id(), 403);
    }

    private function validationRules(): array
    {
        return [
            'title'                  => ['required', 'string', 'max:255'],
            'description'            => ['nullable', 'string', 'max:2000'],
            'items'                  => ['required', 'array', 'min:1'],
            'items.*.type'            => ['required', 'string', 'in:module,wordcloud,poll'],
            'items.*.position'        => ['required', 'integer', 'min:1'],
            'items.*.module_id'       => ['nullable', 'integer', 'exists:modules,id'],
            'items.*.wc_title'        => ['nullable', 'string', 'max:255'],
            'items.*.wc_questions'    => ['nullable', 'array', 'min:1', 'max:10'],
            'items.*.wc_questions.*'  => ['nullable', 'string', 'max:500'],
            'items.*.poll_questions'            => ['nullable', 'array', 'min:1', 'max:10'],
            'items.*.poll_questions.*.question' => ['nullable', 'string', 'max:500'],
            'items.*.poll_questions.*.choices'  => ['nullable', 'array', 'min:2', 'max:5'],
            'items.*.poll_questions.*.choices.*'=> ['nullable', 'string', 'max:200'],
            'items.*.poll_duration'             => ['nullable', 'integer', 'min:1', 'max:120'],
        ];
    }

    private function syncItems(FormateurParcours $parcours, array $items): void
    {
        $parcours->items()->delete();

        $rows = collect($items)
            ->sortBy('position')
            ->values()
            ->map(function (array $item, int $index) use ($parcours) {
                $base = [
                    'formateur_parcours_id' => $parcours->id,
                    'position'              => $index + 1,
                    'type'                  => $item['type'],
                    'module_id'             => null,
                    'wc_title'              => null,
                    'wc_questions'          => null,
                    'wc_duration'           => null,
                    'poll_questions'        => null,
                    'poll_duration'         => null,
                ];

                if ($item['type'] === 'module') {
                    $base['module_id'] = (int) $item['module_id'];
                } elseif ($item['type'] === 'wordcloud') {
                    $base['wc_title']     = trim($item['wc_title'] ?? '');
                    $base['wc_questions'] = json_encode(
                        collect($item['wc_questions'] ?? [])
                            ->map(fn ($q) => trim((string) $q))
                            ->filter()
                            ->values()
                            ->all()
                    );
                    $base['wc_duration'] = is_numeric($item['wc_duration'] ?? null) && (int) $item['wc_duration'] > 0
                        ? (int) $item['wc_duration'] : null;
                } else {
                    $base['poll_questions'] = json_encode(
                        collect($item['poll_questions'] ?? [])
                            ->map(fn ($pq) => [
                                'question' => trim((string) ($pq['question'] ?? '')),
                                'choices'  => collect($pq['choices'] ?? [])
                                    ->map(fn ($c) => trim((string) $c))
                                    ->filter()
                                    ->values()
                                    ->all(),
                            ])
                            ->filter(fn ($pq) => $pq['question'] !== '' && count($pq['choices']) >= 2)
                            ->values()
                            ->all()
                    );
                    $base['poll_duration'] = is_numeric($item['poll_duration'] ?? null) && (int) $item['poll_duration'] > 0
                        ? (int) $item['poll_duration'] : null;
                }

                return $base;
            });

        FormateurParcoursItem::insert($rows->all());
    }

    private function buildToolTemplatesPayload(): array
    {
        $formateurId = auth()->id();

        $wordClouds = WordCloud::query()
            ->where('formateur_id', $formateurId)
            ->whereNull('group_id')
            ->latest()
            ->get()
            ->map(fn (WordCloud $wc) => [
                'id'             => 'wc-'.$wc->id,
                'type'           => 'wordcloud',
                'wc_title'       => $wc->title,
                'wc_questions'   => $wc->questions_array,
                'wc_duration'    => null,
                'poll_questions' => [],
                'poll_duration'  => null,
            ]);

        $polls = PollSession::query()
            ->where('formateur_id', $formateurId)
            ->whereNull('group_id')
            ->latest()
            ->get()
            ->map(fn (PollSession $poll) => [
                'id'             => 'poll-'.$poll->id,
                'type'           => 'poll',
                'wc_title'       => null,
                'wc_questions'   => [],
                'wc_duration'    => null,
                'poll_questions' => $poll->questions ?? [],
                'poll_duration'  => null,
            ]);

        return $wordClouds->concat($polls)->values()->all();
    }

    private function buildAvailableModulesPayload(): array
    {
        return Module::active()
            ->with([
                'sections.lectures:id,module_id,section_id,duration,question_count,quiz_enabled,quiz_questions_per_attempt',
                'category:id,category_name',
            ])
            ->orderBy('module_title')
            ->get()
            ->map(function (Module $module) {
                $lessonCount = (int) collect($module->sections ?? [])
                    ->flatMap->lectures
                    ->count();

                $questionCount = (int) collect($module->sections ?? [])
                    ->flatMap->lectures
                    ->sum(function ($lecture) {
                        $scorm = (int) ($lecture->question_count ?? 0);
                        $quiz  = (bool) ($lecture->quiz_enabled ?? false)
                            ? (int) ($lecture->quiz_questions_per_attempt ?? 0)
                            : 0;
                        return max($scorm, $quiz);
                    });

                return [
                    'id'             => (int) $module->id,
                    'title'          => (string) $module->module_title,
                    'lesson_count'   => $lessonCount,
                    'question_count' => $questionCount,
                    'duration_label' => (string) ($module->formatted_duration ?? 'Rythme libre'),
                    'category_id'    => $module->category?->id,
                    'category_name'  => $module->category?->category_name ?? 'Non catégorisées',
                ];
            })
            ->values()
            ->all();
    }

    private function buildSelectedItemsPayload(FormateurParcours $parcours): array
    {
        return $parcours->items
            ->map(function (FormateurParcoursItem $item) {
                if ($item->type === 'module' && $item->module) {
                    $module      = $item->module;
                    $lessonCount = (int) collect($module->sections ?? [])
                        ->flatMap->lectures
                        ->count();

                    $questionCount = (int) collect($module->sections ?? [])
                        ->flatMap->lectures
                        ->sum(function ($lecture) {
                            $scorm = (int) ($lecture->question_count ?? 0);
                            $quiz  = (bool) ($lecture->quiz_enabled ?? false)
                                ? (int) ($lecture->quiz_questions_per_attempt ?? 0)
                                : 0;
                            return max($scorm, $quiz);
                        });

                    return [
                        'type'           => 'module',
                        'id'             => (int) $module->id,
                        'title'          => (string) $module->module_title,
                        'position'       => (int) $item->position,
                        'lesson_count'   => $lessonCount,
                        'question_count' => $questionCount,
                        'duration_label' => (string) ($module->formatted_duration ?? 'Rythme libre'),
                    ];
                }

                if ($item->type === 'wordcloud') {
                    return [
                        'type'         => 'wordcloud',
                        'id'           => 'wc-' . $item->id,
                        'position'     => (int) $item->position,
                        'wc_title'     => (string) ($item->wc_title ?? ''),
                        'wc_questions' => $item->wc_questions ?? [],
                        'wc_duration'  => $item->wc_duration ? (int) $item->wc_duration : null,
                    ];
                }

                return [
                    'type'           => 'poll',
                    'id'             => 'poll-' . $item->id,
                    'position'       => (int) $item->position,
                    'poll_questions' => $item->poll_questions ?? [],
                    'poll_duration'  => $item->poll_duration ? (int) $item->poll_duration : null,
                ];
            })
            ->values()
            ->all();
    }
}
