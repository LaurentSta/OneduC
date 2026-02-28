<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ModuleCompletionNotification extends Model
{
    use HasFactory;

    protected $fillable = [
        'module_id',
        'stagiaire_id',
        'recipient_id',
    ];
}
