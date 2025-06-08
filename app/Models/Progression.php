<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Progression extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'lecture_id', 'completed_at'];

    public function lecture()
    {
        return $this->belongsTo(ModuleLecture::class, 'lecture_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
