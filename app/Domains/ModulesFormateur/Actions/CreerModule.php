<?php

namespace App\Domains\ModulesFormateur\Actions;

use App\Models\Category;
use App\Models\Module;
use App\Models\SubCategory;
use Illuminate\Support\Str;

class CreerModule
{
    public function __construct(
        private readonly CreerChapitre $creerChapitre,
        private readonly CreerLecon $creerLecon,
    ) {}

    public function execute(array $data, int $trainerId): Module
    {
        $module = $this->creerModuleVide($data, $trainerId);

        $this->seedExempleDeStructure($module);

        return $module;
    }

    public function creerModuleVide(array $data, int $trainerId): Module
    {
        $category = $this->resolveTrainerCategory();
        $subcategory = $this->resolveTrainerSubcategory($category);

        return Module::create([
            'module_title' => $data['module_title'],
            'module_name' => $data['module_title'],
            'module_name_slug' => Str::slug($data['module_title']),
            'description' => $data['description'] ?? null,
            'objectifs' => $data['objectifs'] ?? null,
            'formateur_id' => $trainerId,
            'category_id' => $category->id,
            'subcategory_id' => $subcategory->id,
            'status' => 1,
            'is_trainer_authored' => true,
        ]);
    }

    public function creerFormationCatalogueVide(array $data, int $administrateurId, ?int $formateurReferentId = null): Module
    {
        [$categorie, $sousCategorie] = $this->classificationCatalogue(
            isset($data['category_id']) ? (int) $data['category_id'] : null,
        );

        return Module::create([
            'module_title' => $data['module_title'],
            'module_name' => $data['module_title'],
            'module_name_slug' => Str::slug($data['module_title']).'-'.Str::lower(Str::random(6)),
            'description' => $data['description'] ?? null,
            'objectifs' => $data['objectifs'] ?? null,
            'formateur_id' => $formateurReferentId,
            'created_by' => $administrateurId,
            'category_id' => $categorie->id,
            'subcategory_id' => $sousCategorie->id,
            'status' => false,
            'is_trainer_authored' => false,
            'publication_state' => Module::PUBLICATION_DRAFT,
        ]);
    }

    public function classerDansCatalogue(Module $module, ?int $categorieId = null): Module
    {
        [$categorie, $sousCategorie] = $this->classificationCatalogue($categorieId);

        $module->update([
            'category_id' => $categorie->id,
            'subcategory_id' => $sousCategorie->id,
        ]);

        return $module;
    }

    private function seedExempleDeStructure(Module $module): void
    {
        $premierChapitre = $this->creerChapitre->execute($module, 'Chapitre 1');
        $this->creerLecon->execute($premierChapitre, 'Leçon 1', null);
        $this->creerLecon->execute($premierChapitre, 'Leçon 2', null);

        $deuxiemeChapitre = $this->creerChapitre->execute($module, 'Chapitre 2');
        $this->creerLecon->execute($deuxiemeChapitre, 'Leçon 1', null);
    }

    private function resolveTrainerCategory(): Category
    {
        return Category::updateOrCreate(
            ['category_slug' => 'modules-formateurs'],
            ['category_name' => 'Formations formateurs']
        );
    }

    private function resolveTrainerSubcategory(Category $category): SubCategory
    {
        return SubCategory::firstOrCreate(
            ['subcategory_slug' => 'contenu-personnalise'],
            ['category_id' => $category->id, 'subcategory_name' => 'Contenu personnalisé']
        );
    }

    /**
     * @return array{0: Category, 1: SubCategory}
     */
    private function classificationCatalogue(?int $categorieId): array
    {
        $categorie = $categorieId
            ? Category::query()->findOrFail($categorieId)
            : Category::query()->firstOrCreate(
                ['category_slug' => 'catalogue-oneduc'],
                ['category_name' => 'Catalogue Oneduc'],
            );

        $sousCategorie = SubCategory::query()->firstOrCreate(
            [
                'category_id' => $categorie->id,
                'subcategory_slug' => 'catalogue-oneduc-'.$categorie->id,
            ],
            ['subcategory_name' => 'Catalogue Oneduc'],
        );

        return [$categorie, $sousCategorie];
    }
}
