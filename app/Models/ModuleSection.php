<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ModuleSection extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function lectures()
    {
        return $this->hasMany(ModuleLecture::class, 'section_id');
    }

    public function module()
    {
        return $this->belongsTo(Module::class, 'module_id');
    }
}
