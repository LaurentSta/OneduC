<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Throwable;

class StagiaireController extends Controller
{
    public function resetProgression(User $user): RedirectResponse
    {
        if ($user->role !== 'stagiaire') {
            return back()->with('error', 'Seul un compte stagiaire peut être réinitialisé.');
        }

        try {
            DB::transaction(function () use ($user): void {
                $tentativesQuiz = DB::table('quiz_attempts')
                    ->where('user_id', $user->id)
                    ->pluck('id');

                if ($tentativesQuiz->isNotEmpty()) {
                    DB::table('quiz_attempt_questions')
                        ->whereIn('attempt_id', $tentativesQuiz)
                        ->delete();
                }

                $tablesParUtilisateur = [
                    'quiz_attempts',
                    'scorm_scores',
                    'scorm_interactions',
                    'scorm_results',
                    'scorm_evaluation_scores',
                    'scorm_evaluation_results',
                    'scorm_evaluation_interactions',
                    'content_block_scorm_scores',
                    'content_block_scorm_results',
                    'video_segment_trackings',
                    'progressions',
                ];

                foreach ($tablesParUtilisateur as $table) {
                    if (! Schema::hasTable($table)) {
                        continue;
                    }

                    DB::table($table)
                        ->where('user_id', $user->id)
                        ->delete();
                }

                if (Schema::hasTable('module_completion_notifications')) {
                    DB::table('module_completion_notifications')
                        ->where('stagiaire_id', $user->id)
                        ->delete();
                }

                $user->update(['total_site_time' => 0]);
            });
        } catch (Throwable $exception) {
            report($exception);

            return back()->with('error', 'La progression n’a pas pu être réinitialisée. Merci de réessayer.');
        }

        return back()->with('success', "La progression de {$user->name} a été remise à zéro.");
    }
}
