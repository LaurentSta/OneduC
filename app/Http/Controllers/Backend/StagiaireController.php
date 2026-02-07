<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class StagiaireController extends Controller
{
    /**
     * Réinitialise toute la progression (Méthode "Nucléaire")
     * Force la suppression en désactivant temporairement les contraintes SQL.
     */
    public function resetProgression(User $user)
{
    // CORRECTION ICI : on vérifie 'stagiaire' et non 'user'
    // On autorise si c'est un stagiaire OU si c'est l'admin connecté (pour test)
    if ($user->role !== 'stagiaire' && auth()->id() !== $user->id) {
        return back()->with('error', 'Action non autorisée sur ce compte (seuls les stagiaires peuvent être réinitialisés).');
    }

    try {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        DB::transaction(function () use ($user) {
            $id = $user->id;

            // --- VIDAGE COMPLET ---
            // ... (le reste du code de suppression reste identique) ...
            
            // 1. Quiz
            DB::table('quiz_attempt_questions')
                ->join('quiz_attempts', 'quiz_attempt_questions.attempt_id', '=', 'quiz_attempts.id')
                ->where('quiz_attempts.user_id', $id)
                ->delete();
            DB::table('quiz_attempts')->where('user_id', $id)->delete();

            // 2. SCORM
            DB::table('scorm_scores')->where('user_id', $id)->delete();
            DB::table('scorm_interactions')->where('user_id', $id)->delete();
            DB::table('scorm_results')->where('user_id', $id)->delete();
            DB::table('scorm_evaluation_scores')->where('user_id', $id)->delete();

            // 3. Vidéos & Progression
            DB::table('video_segment_trackings')->where('user_id', $id)->delete();
            DB::table('progressions')->where('user_id', $id)->delete();

            // 4. Temps
            $user->update(['total_site_time' => 0]);
        });

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        return back()->with('success', "La progression de {$user->name} a été remise à zéro.");

    } catch (\Exception $e) {
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        return back()->with('error', "Erreur : " . $e->getMessage());
    }
}
}