<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Module extends Model
{
    use SoftDeletes;
    protected $fillable = [
    'category_id','subcategory_id','formateur_id',
    'module_image','header_image',
    'module_title','module_name','module_name_slug','description','objectifs',
    'module_video','label','duree','resources','certificat','prerequi',
    'bestseller','vedette','surevalue','status','evaluation_id'
    ];

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
        return $this->hasMany(\App\Models\ModuleLecture::class, 'module_id');
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
    // en bas des relations
    public function scopeActive($q)
    {
        return $q->where('status', 1);
    }
    // ✅ cast du statut
    protected $casts = ['status' => 'boolean'];

    /** Règle de visibilité uniforme */
    public function isVisibleTo(?\App\Models\User $user): bool
    {
        if ($user && $user->role === 'admin') return true; // admin voit tout
        return (bool) $this->status;                        // autres+visiteurs: actifs uniquement
    }

}
