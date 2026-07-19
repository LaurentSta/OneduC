<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ModeleParcours;
use App\Models\ModeleParcoursItem;
use App\Models\Module;
use App\Support\Parcours\RegistreOutilsParcours;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use JsonException;

class ModeleParcoursController extends Controller
{
    public function __construct(private readonly RegistreOutilsParcours $registre) {}

    public function index(): View
    {
        $modeles = ModeleParcours::query()
            ->with('auteur:id,prenom,name,username')
            ->withCount(['items', 'copiesFormateurs'])
            ->latest()
            ->paginate(15);

        return view('admin.modeles-parcours.index', compact('modeles'));
    }

    public function create(): View
    {
        return view('admin.modeles-parcours.create', $this->donneesFormulaire());
    }

    public function store(Request $request): RedirectResponse
    {
        $donnees = $this->validerDonnees($request);

        $modele = DB::transaction(function () use ($donnees): ModeleParcours {
            $modele = ModeleParcours::create([
                'auteur_admin_id' => auth()->id(),
                'titre' => trim($donnees['titre']),
                'description' => $donnees['description'] ?? null,
                'statut' => ModeleParcours::STATUT_BROUILLON,
            ]);

            $this->synchroniserItems($modele, $donnees['items']);

            return $modele;
        });

        return redirect()
            ->route('admin.modeles-parcours.edit', $modele)
            ->with('success', 'Le modèle de parcours a été créé en brouillon.');
    }

    public function edit(ModeleParcours $modele): View|RedirectResponse
    {
        if (! $modele->estBrouillon()) {
            return redirect()
                ->route('admin.modeles-parcours.index')
                ->with('error', 'Un modèle publié ou archivé est immuable. Dupliquez-le pour préparer une nouvelle version.');
        }

        $modele->load('items.module');

        return view('admin.modeles-parcours.edit', $this->donneesFormulaire($modele) + compact('modele'));
    }

    public function update(Request $request, ModeleParcours $modele): RedirectResponse
    {
        abort_unless($modele->estBrouillon(), 409, 'Un modèle publié ou archivé est immuable.');

        $donnees = $this->validerDonnees($request);

        DB::transaction(function () use ($donnees, $modele): void {
            $modele->update([
                'titre' => trim($donnees['titre']),
                'description' => $donnees['description'] ?? null,
            ]);

            $this->synchroniserItems($modele, $donnees['items']);
        });

        return redirect()
            ->route('admin.modeles-parcours.edit', $modele)
            ->with('success', 'Le brouillon a été enregistré.');
    }

    public function publier(ModeleParcours $modele): RedirectResponse
    {
        abort_unless($modele->estBrouillon(), 409, 'Seul un brouillon peut être publié.');

        $modele->load('items');
        $this->validerItemsExistantsPourPublication($modele);

        $modele->update([
            'statut' => ModeleParcours::STATUT_PUBLIE,
            'publie_le' => now(),
            'archive_le' => null,
        ]);

        return redirect()
            ->route('admin.modeles-parcours.index')
            ->with('success', 'Le modèle est maintenant publié et duplicable par les formateurs.');
    }

    public function archiver(ModeleParcours $modele): RedirectResponse
    {
        abort_unless($modele->estPublie(), 409, 'Seul un modèle publié peut être archivé.');

        $modele->update([
            'statut' => ModeleParcours::STATUT_ARCHIVE,
            'archive_le' => now(),
        ]);

        return redirect()
            ->route('admin.modeles-parcours.index')
            ->with('success', 'Le modèle a été archivé. Les copies existantes sont conservées.');
    }

    public function dupliquerEnBrouillon(ModeleParcours $modele): RedirectResponse
    {
        $modele->load('items');

        $copie = DB::transaction(function () use ($modele): ModeleParcours {
            $copie = ModeleParcours::create([
                'auteur_admin_id' => auth()->id(),
                'titre' => 'Copie de '.$modele->titre,
                'description' => $modele->description,
                'statut' => ModeleParcours::STATUT_BROUILLON,
            ]);

            foreach ($modele->items as $item) {
                $copie->items()->create([
                    'position' => $item->position,
                    'type' => $item->type,
                    'module_id' => $item->module_id,
                    'outil' => $item->outil,
                    'configuration' => $item->configuration,
                ]);
            }

            return $copie;
        });

        return redirect()
            ->route('admin.modeles-parcours.edit', $copie)
            ->with('success', 'Une nouvelle version brouillon a été créée.');
    }

    public function destroy(ModeleParcours $modele): RedirectResponse
    {
        abort_unless($modele->estBrouillon(), 409, 'Seul un brouillon peut être supprimé.');

        $modele->delete();

        return redirect()
            ->route('admin.modeles-parcours.index')
            ->with('success', 'Le brouillon a été supprimé.');
    }

    /**
     * @return array<string, mixed>
     */
    private function donneesFormulaire(?ModeleParcours $modele = null): array
    {
        $modules = Module::query()
            ->publiclyListable()
            ->orderBy('module_title')
            ->get(['id', 'module_title', 'status']);

        $outils = $this->registre->actifs();
        $configurationsParDefaut = collect($outils)
            ->mapWithKeys(fn (array $outil, string $cle): array => [
                $cle => $outil['configuration_defaut'],
            ])
            ->all();

        $itemsInitiaux = $modele?->items
            ->map(fn (ModeleParcoursItem $item): array => [
                'type' => $item->type,
                'module_id' => $item->module_id,
                'outil' => $item->outil,
                'configuration' => $item->configuration,
            ])
            ->values()
            ->all() ?? [];

        return compact('modules', 'outils', 'configurationsParDefaut', 'itemsInitiaux');
    }

    /**
     * @return array{titre: string, description?: string|null, items: array<int, array<string, mixed>>}
     */
    private function validerDonnees(Request $request): array
    {
        $donnees = $request->validate([
            'titre' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'items' => ['required', 'array', 'min:1', 'max:200'],
            'items.*.type' => ['required', 'string', 'in:module,outil'],
            'items.*.module_id' => ['nullable', 'integer'],
            'items.*.outil' => ['nullable', 'string', 'max:64'],
            'items.*.configuration' => ['nullable'],
        ]);

        $donnees['items'] = collect($donnees['items'])
            ->values()
            ->map(fn (array $item, int $index): array => $this->validerItem($item, $index))
            ->all();

        return $donnees;
    }

    /**
     * @param  array<string, mixed>  $item
     * @return array<string, mixed>
     */
    private function validerItem(array $item, int $index): array
    {
        if ($item['type'] === ModeleParcoursItem::TYPE_MODULE) {
            $moduleId = (int) ($item['module_id'] ?? 0);
            $moduleValide = Module::query()
                ->whereKey($moduleId)
                ->publiclyListable()
                ->exists();

            if (! $moduleValide) {
                throw ValidationException::withMessages([
                    "items.{$index}.module_id" => 'Sélectionnez une formation officielle du catalogue Oneduc.',
                ]);
            }

            return [
                'type' => ModeleParcoursItem::TYPE_MODULE,
                'module_id' => $moduleId,
                'outil' => null,
                'configuration' => null,
            ];
        }

        $outil = trim((string) ($item['outil'] ?? ''));
        $configuration = $this->decoderConfiguration($item['configuration'] ?? [], $index);

        return [
            'type' => ModeleParcoursItem::TYPE_OUTIL,
            'module_id' => null,
            'outil' => $outil,
            'configuration' => $this->registre->valider(
                $outil,
                $configuration,
                "items.{$index}.configuration"
            ),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function decoderConfiguration(mixed $configuration, int $index): array
    {
        if (is_array($configuration)) {
            return $configuration;
        }

        try {
            $decodee = json_decode((string) $configuration, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            throw ValidationException::withMessages([
                "items.{$index}.configuration" => 'La configuration doit être un objet JSON valide.',
            ]);
        }

        if (! is_array($decodee) || array_is_list($decodee)) {
            throw ValidationException::withMessages([
                "items.{$index}.configuration" => 'La configuration doit être un objet JSON.',
            ]);
        }

        return $decodee;
    }

    /**
     * @param  array<int, array<string, mixed>>  $items
     */
    private function synchroniserItems(ModeleParcours $modele, array $items): void
    {
        $modele->items()->delete();

        foreach ($items as $index => $item) {
            $modele->items()->create([
                ...$item,
                'position' => $index + 1,
            ]);
        }
    }

    private function validerItemsExistantsPourPublication(ModeleParcours $modele): void
    {
        if ($modele->items->isEmpty()) {
            throw ValidationException::withMessages([
                'modele' => 'Ajoutez au moins une étape avant de publier le modèle.',
            ]);
        }

        foreach ($modele->items as $index => $item) {
            if ($item->estModule()) {
                $moduleValide = Module::query()
                    ->whereKey($item->module_id)
                    ->publiclyListable()
                    ->exists();

                if (! $moduleValide) {
                    throw ValidationException::withMessages([
                        'modele' => 'Toutes les formations du modèle doivent être publiées dans le catalogue Oneduc.',
                    ]);
                }

                continue;
            }

            $this->registre->valider(
                (string) $item->outil,
                $item->configuration ?? [],
                "items.{$index}.configuration"
            );
        }
    }
}
