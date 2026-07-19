<?php

namespace App\Http\Controllers\Backend;

use App\Domains\CatalogueFormations\Actions\BasculerGroupesVersionFormation;
use App\Domains\CatalogueFormations\Actions\CreerVersionFormationCatalogue;
use App\Domains\CatalogueFormations\Actions\PublierVersionFormationCatalogue;
use App\Http\Controllers\Controller;
use App\Models\Module;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class VersionFormationCatalogueController extends Controller
{
    public function __construct(
        private readonly CreerVersionFormationCatalogue $creerVersion,
        private readonly PublierVersionFormationCatalogue $publierVersion,
        private readonly BasculerGroupesVersionFormation $basculerGroupes,
    ) {}

    public function store(Module $module): RedirectResponse
    {
        abort_unless(! $module->is_trainer_authored, 404);

        $version = $this->creerVersion->execute($module, (int) auth()->id());

        return redirect()
            ->route('admin.formations.constructeur.edit', $version)
            ->with('success', 'Nouvelle version brouillon créée. La version publiée reste inchangée.');
    }

    public function duplicateTrainerCreation(Module $module): RedirectResponse
    {
        abort_unless($module->is_trainer_authored, 404);

        $copie = $this->creerVersion->dupliquerCreationFormateur($module, (int) auth()->id());

        return redirect()
            ->route('admin.formations.constructeur.edit', $copie)
            ->with('success', 'Création du formateur dupliquée dans le catalogue. L’original reste inchangé.');
    }

    public function publish(Module $module): RedirectResponse
    {
        $module = $this->publierVersion->execute($module);

        return redirect()
            ->route('admin.formations.constructeur.edit', $module)
            ->with('success', 'La version '.$module->version_number.' est maintenant publiée.');
    }

    public function archive(Module $module): RedirectResponse
    {
        abort_unless(! $module->is_trainer_authored, 404);
        abort_unless($module->publication_state === Module::PUBLICATION_PUBLISHED, 422);

        $module->forceFill([
            'publication_state' => Module::PUBLICATION_ARCHIVED,
            // Une version encore liée à des groupes doit rester accessible à leurs stagiaires.
            'status' => $module->groups()->exists(),
        ])->save();

        return redirect()
            ->route('admin.formations.constructeur.index')
            ->with('success', 'Formation archivée. Les groupes déjà liés conservent leur accès.');
    }

    public function switchGroups(Request $request, Module $module): RedirectResponse
    {
        $validated = $request->validate([
            'group_ids' => ['required', 'array', 'min:1'],
            'group_ids.*' => ['integer', 'exists:groups,id'],
        ]);

        $total = $this->basculerGroupes->execute($module, $validated['group_ids']);

        return back()->with(
            $total > 0 ? 'success' : 'warning',
            $total > 0
                ? $total.' groupe(s) basculé(s) vers cette version.'
                : 'Aucun groupe sélectionné n’utilisait une version antérieure de cette formation.',
        );
    }
}
