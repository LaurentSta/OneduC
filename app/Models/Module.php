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
   
    public function category()
    {
        return $this->belongsTo(Category::class);
    }
    
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

    public function formateur()
    {
        return $this->belongsTo(User::class, 'formateur_id');
    }

    public function sections()
    {
        return $this->hasMany(ModuleSection::class);
    }

    public function lectures()
    {
        return $this->hasMany(\App\Models\ModuleLecture::class, 'module_id');
    }

    public function groups()
    {
        return $this->belongsToMany(Group::class, 'group_module');
    }

    public function evaluation()
    {
        return $this->belongsTo(\App\Models\Evaluation::class);
    }

    public function scopeActive($q)
    {
        return $q->where('status', 1);
    }

    protected $casts = [
        'status'    => 'boolean',
        'objectifs' => 'array',
    ];

    public function isVisibleTo(?\App\Models\User $user): bool
    {
        if ($user && $user->role === 'admin') return true; 
        return (bool) $this->status;                        
    }

    // --- C'EST ICI QU'ON AJOUTE LE CALCUL DU TEMPS ---

    /**
     * Calcule la durée totale en minutes (somme des durées des leçons).
     * Accessible via : $module->total_minutes
     */
    public function getTotalMinutesAttribute()
    {
        // On somme la durée des leçons via les sections
        return $this->sections->sum(function ($section) {
            return $section->lectures->sum('duration');
        });
    }

    /**
     * Affiche la durée formatée (ex: "2h 15min" ou "45 min").
     * Accessible via : $module->formatted_duration
     */
    public function getFormattedDurationAttribute()
    {
        $minutes = $this->total_minutes;

        if ($minutes <= 0) {
            return null; 
        }

        $hours = floor($minutes / 60);
        $remainingMinutes = $minutes % 60;

        if ($hours > 0) {
            return "{$hours}h" . ($remainingMinutes > 0 ? " {$remainingMinutes}min" : "");
        }

        return "{$remainingMinutes} min";
    }
}