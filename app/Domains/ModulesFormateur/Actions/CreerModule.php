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
}
