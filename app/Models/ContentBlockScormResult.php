<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContentBlockScormResult extends Model
{
    protected $fillable = [
        'user_id',
        'lecture_id',
        'content_block_key',
        'scorm_key',
        'scorm_value',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function lecture()
    {
        return $this->belongsTo(ModuleLecture::class);
    }
}
