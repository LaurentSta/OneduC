<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Module extends Model
{
    use SoftDeletes;

    protected $guarded = [];

    // Catégorie principale
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    // Sous-catégorie
    public function subCategory()
    {
        return $this->belongsTo(\App\Models\SubCategory::class, 'subcategory_id');
    }


    public function stagiaires()
    {
        return User::where('role', 'stagiaire')
            ->whereHas('groupesStagiaire', function ($q) {
                $q->whereIn('group_id', $this->groups()->pluck('groups.id'));
            });
    }


    // Formateur
    public function formateur()
    {
        return $this->belongsTo(User::class, 'formateur_id');
    }

    // Sections de cours
    public function sections()
    {
        return $this->hasMany(ModuleSection::class);
    }

    // Lectures (contenus)
    public function lectures()
    {
        return $this->hasMany(ModuleLecture::class);
    }

    // Groupes liés à ce module
    public function groups()
    {
        return $this->belongsToMany(Group::class, 'group_module');
    }

    public function evaluation()
    {
        return $this->belongsTo(\App\Models\Evaluation::class);
    }



}
