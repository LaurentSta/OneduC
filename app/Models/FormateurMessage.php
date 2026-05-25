<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FormateurMessage extends Model
{
    protected $fillable = [
        'formateur_id',
        'stagiaire_id',
        'subject',
        'body',
        'sent_as_notification',
        'sent_as_email',
    ];

    protected $casts = [
        'sent_as_notification' => 'boolean',
        'sent_as_email' => 'boolean',
    ];

    public function formateur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'formateur_id');
    }

    public function stagiaire(): BelongsTo
    {
        return $this->belongsTo(User::class, 'stagiaire_id');
    }
}
