<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuizQuestion extends Model
{
    use HasFactory;

    protected $fillable = [
        'lecture_id',
        'questionnaire_id',
        'type',
        'question_text',
        'image_path',
        'image_alt',
        'audio_path',
        'audio_transcript',
        'is_active',
        'created_by',
        'position',
        'points',
        'payload',
    ];

    protected $casts = [
        'position'         => 'integer',
        'lecture_id'       => 'integer',
        'questionnaire_id' => 'integer',
        'points'           => 'integer',
        'payload'          => 'array',
    ];

    /**
     * Relation pour récupérer les choix possibles (A, B, C...).
     * Nommée 'answers' pour fonctionner avec ton FormateurController.
     */
    public function answers()
    {
        return $this->hasMany(QuizOption::class, 'question_id')->orderBy('position')->orderBy('id');
    }

    /**
     * Alias 'options' si tu préfères utiliser ce terme plus tard.
     */
    public function options()
    {
        return $this->answers();
    }

    /**
     * La question appartient à une leçon (la banque est liée à la leçon).
     */
    public function lecture()
    {
        return $this->belongsTo(ModuleLecture::class, 'lecture_id');
    }

    /**
     * Une question de quiz en direct group-only appartient à un questionnaire,
     * indépendant de toute leçon (lecture_id est alors null).
     */
    public function questionnaire()
    {
        return $this->belongsTo(QuizQuestionnaire::class, 'questionnaire_id');
    }

    /**
     * Questions actives et pouvant être intercalées automatiquement comme
     * activité en fin de leçon (cloze exclu pour l'instant, cf. lecture_blocks.blade.php).
     */
    public function scopeEligibleForInlineActivity($query)
    {
        return $query->where('is_active', true)->whereIn('type', ['single', 'multiple', 'boolean']);
    }
}
