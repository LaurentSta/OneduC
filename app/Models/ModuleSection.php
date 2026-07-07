<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ModuleSection extends Model
{
    use HasFactory;

    protected $fillable = [
        'module_id',
        'section_title',
        'section_html',
        'objectif',
        'methode',
        'contexte',
        'scorm_video_path',
        'video_url',
        'position',
    ];

    public function lectures()
    {
        return $this->hasMany(ModuleLecture::class, 'section_id')->orderBy('position');
    }

    public function module()
    {
        return $this->belongsTo(Module::class, 'module_id');
    }
}
