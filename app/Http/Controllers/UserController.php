<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use App\Models\ScormInteraction;
use App\Models\LessonFeedback;
use App\Models\VideoSegmentTracking;
use App\Models\ScormEvaluationScore;

class UserController extends Controller
{
    /* -------------------------------------------------------------------------
     | Pages publiques
     |-------------------------------------------------------------------------- */
    public function Index()
    {
        return view('frontend.index');
    }

    public function Projet()
    {
        return view('frontend.contenu.projet');
    }

    public function Association()
    {
        return view('frontend.contenu.association');
    }

    public function Adhesion()
    {
        return view('frontend.contenu.adhesion');
    }



    /* -------------------------------------------------------------------------
     | Auth & Session
     |-------------------------------------------------------------------------- */
    public function UserLogout(Request $request)
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }

    /* -------------------------------------------------------------------------
     | Stagiaire Dashboard & Profil
     |-------------------------------------------------------------------------- */

    // Tableau de bord stagiaire
    public function StagiaireDashboard()
    {
        $user = auth()->user();

        $videoStats = VideoSegmentTracking::where('user_id', $user->id)
    ->selectRaw('SUM(total_watch_time) as watch_time, COUNT(*) as segments, SUM(watch_count) - COUNT(*) as replays')
    ->first();

    $totalVideoWatchTime = (int) round($videoStats->watch_time ?? 0);
    $totalVideoSegments = (int) ($videoStats->segments ?? 0);
    $totalVideoReplays = (int) ($videoStats->replays ?? 0);
        // ✅ Tous les scores SCORM du stagiaire
        $scormScores = \App\Models\ScormScore::with('lecture')
            ->where('user_id', $user->id)
            ->get();

        // 🔁 Recalcul des scores pour chaque leçon
        foreach ($scormScores as $score) {
            $lectureId = $score->lecture_id;

            $reponsesCorrectes = \App\Models\ScormInteraction::where('lecture_id', $lectureId)
                ->where('user_id', $user->id)
                ->where('result', 'correct')
                ->count();

            $questionsTotal = \App\Models\ScormInteraction::where('lecture_id', $lectureId)
                ->where('user_id', $user->id)
                ->whereNotNull('interaction_weighting')
                ->count();

            $score->correct_score = $reponsesCorrectes * 10;
            $score->total_score_possible = $questionsTotal * 10;
        }

        // ✅ Calcul de progression globale
        $total = $scormScores->count();
        $completed = $scormScores->where('is_completed', true)->count();
        $progressionGlobale = $total > 0 ? round(($completed / $total) * 100) : 0;

        // ✅ Score global
        $totalCorrectScore = $scormScores->sum('correct_score');
        $totalScorePossible = $scormScores->sum('total_score_possible');
        $tauxBonnesReponses = $totalScorePossible > 0
            ? round(($totalCorrectScore / $totalScorePossible) * 100)
            : 0;

        // ✅ Récupération des groupes + modules + formateur
        $groupes = $user->groupesStagiaire()->with('modules', 'instructor')->get();
        $modules = $groupes->flatMap->modules->unique('id')->values();
        $formateur = $groupes->first()?->instructor;

        // ✅ Temps passé sur la plateforme
        $totalSiteTime = $user->total_site_time;

        // ✅ Temps passé sur les activités SCORM
        $totalScormTime = \App\Models\ScormScore::where('user_id', $user->id)
            ->sum('session_time');
        // ✅ Nombre de réponses (correctes ou non)
        $answeredCount = \App\Models\ScormInteraction::where('user_id', $user->id)
            ->whereIn('result', ['correct', 'wrong'])
            ->count();
        // 🔁 On récupère tous les latencies en format "HH:MM:SS"
        $latencies = \App\Models\ScormInteraction::where('user_id', $user->id)
            ->whereNotNull('latency')
            ->pluck('latency');
        // ⏱️ Convertir en secondes
        $latencySeconds = $latencies->map(function ($latency) {
            try {
                [$h, $m, $s] = array_pad(explode(':', $latency), 3, 0);
                return (int)$h * 3600 + (int)$m * 60 + (int)$s;
            } catch (\Exception $e) {
                return 0; // en cas de données corrompues
            }
        });
        // ✅ Temps total à répondre aux questions
        $totalLatencyTime = $latencySeconds->sum();
        // ✅ Temps moyen de réponse (en secondes)
        $averageLatencyTime = $latencySeconds->count() > 0
            ? round($totalLatencyTime / $latencySeconds->count())
            : 0;

        $commentairesTotal = LessonFeedback::withTrashed()
        ->where('user_id', $user->id)
        ->count();

        // --- Statistiques ÉVALUATIONS SCORM ---

        $scormEvalScores = ScormEvaluationScore::with('evaluation')
            ->where('user_id', $user->id)
            ->get();

        // Globales
        $totalEvaluationsDone = $scormEvalScores->count();
        $averageEvaluationScore = $scormEvalScores->avg('last_score');
        $bestEvaluationScore = $scormEvalScores->max('best_score');
        $totalSuccessEvaluations = $scormEvalScores->where('best_score', '>=', 75)->count();
        $tauxReussiteEvaluation = $totalEvaluationsDone > 0
            ? round($totalSuccessEvaluations / $totalEvaluationsDone * 100, 1)
            : 0;
        $totalEvaluationTime = $scormEvalScores->sum('session_time');
        $totalEvaluationQuestions = $scormEvalScores->sum('questions_answered');


            return view('stagiaire.index', compact(
                'scormScores',
                'progressionGlobale',
                'modules',
                'formateur',
                'totalSiteTime',
                'totalScormTime',
                'answeredCount',
                'tauxBonnesReponses',
                'totalLatencyTime',
                'averageLatencyTime',
                'commentairesTotal',
                'totalVideoWatchTime',
                'totalVideoSegments',
                'totalVideoReplays',
                'scormEvalScores',
                'totalEvaluationsDone',
                'averageEvaluationScore',
                'bestEvaluationScore',
                'tauxReussiteEvaluation',
                'totalEvaluationTime',
                'totalEvaluationQuestions'
            ));


    }
    // Vue du profil stagiaire
    public function UserProfile()
    {
        $id = Auth::user()->id;
        $profileData = User::findOrFail($id);

        return view('stagiaire.stagiaire_profile_view', compact('profileData'));
    }

    // Paramètres du compte stagiaire
    public function UserParametre()
    {
        $id = Auth::user()->id;
        $profileData = User::findOrFail($id);

        return view('stagiaire.stagiaire_parametre', compact('profileData'));
    }

    public function StagiaireModules()
    {
        $user = Auth::user();

        // Récupérer tous les groupes où il est stagiaire
        $groupes = \App\Models\Group::with('modules')
            ->whereHas('students', function ($query) use ($user) {
                $query->where('email', $user->email);
            })
            ->get();

        // Fusionner tous les modules (en supprimant les doublons)
        $modules = $groupes->flatMap->modules->unique('id');

        return view('stagiaire.stagiaire_modules', compact('modules'));
    }

    public function StagiaireResultats()
        {
            $userId = auth()->id();

            $resultats = \App\Models\ScormScore::with('lecture')
                ->where('user_id', $userId)
                ->get();

                foreach ($resultats as $score) {
                    $lectureId = $score->lecture_id;

                    $reponsesCorrectes = ScormInteraction::where('lecture_id', $lectureId)
                        ->where('user_id', $userId)
                        ->where('result', 'correct')
                        ->count();

                    $questionsTotal = ScormInteraction::where('lecture_id', $lectureId)
                        ->where('user_id', $userId)
                        ->whereNotNull('interaction_weighting')
                        ->count();

                    $score->answered_questions = ScormInteraction::where('lecture_id', $lectureId)
                        ->where('user_id', $userId)
                        ->whereIn('result', ['correct', 'wrong'])
                        ->count();

                    $score->total_questions = $questionsTotal;
                    $score->correct_score = $reponsesCorrectes * 10;
                    $score->total_score_possible = $questionsTotal * 10;

                    // 🔐 Ne réécrit pas un statut déjà 'completed'
                    if ($score->lesson_status !== 'completed') {
                        $score->lesson_status = \App\Models\ScormScore::where('user_id', $userId)
                            ->where('lecture_id', $lectureId)
                            ->value('lesson_status') ?? null;
                    }
                    // 🕒 Ajoute une version formatée du temps
                    $score->formatted_session_time = gmdate('H\h i\m s\s', $score->session_time ?? 0);

                    if ($score->lesson_status !== 'completed') {
                        $score->lesson_status = \App\Models\ScormScore::where('user_id', $userId)
                            ->where('lecture_id', $lectureId)
                            ->value('lesson_status') ?? null;
                    }

                }

            return view('stagiaire.stagiaire_resultats', compact('resultats'));
        }

    public function UserProfilStore(Request $request)
{
    $id = Auth::id();
    $user = User::findOrFail($id);

    // ✅ Validation sécurisée
    $request->validate([
        'name' => 'required|string|max:255',
        'prenom' => 'required|string|max:255',
        'email' => 'required|email|max:255',
        'phoneNumber' => 'nullable|string|max:20',
        'address' => 'nullable|string|max:255',
        'photo' => 'nullable|image|max:2048',
    ]);

    // ✅ Mise à jour des champs
    if ($request->has('name')) {
    $user->name = $request->name;
    }
    if ($request->has('prenom')) {
        $user->prenom = $request->prenom;
    }
    if ($request->has('email')) {
        $user->email = $request->email;
    }
    if ($request->has('phoneNumber')) {
        $user->phone = $request->phoneNumber;
    }
    if ($request->has('address')) {
        $user->address = $request->address;
    }


    // ✅ Upload image si présente
    if ($request->file('photo')) {
        $file = $request->file('photo');
        $filename = date('YmdHi') . '_' . $file->getClientOriginalName();
        $file->move(public_path('/upload/user_images'), $filename);
        $user->photo = $filename;
    }

    $user->save();

    return redirect()->back()->with('message', 'Profil mis à jour avec succès.');
}


    /* -------------------------------------------------------------------------
     | Sécurité / Mot de passe
     |-------------------------------------------------------------------------- */

    public function showUserSecurite()
    {
        $user = Auth::user();

        return view('stagiaire.stagiaire_securite', compact('user'));
    }

    public function UserSecurite(Request $request)
    {
        $id = Auth::user()->id;
        $user = User::findOrFail($id);

        // Validation des champs
        $request->validate([
            'currentPassword' => 'required',
            'newPassword' => 'required|min:8|confirmed',
        ]);

        // Vérification du mot de passe actuel
        if (!Hash::check($request->currentPassword, $user->password)) {
            return back()->with('error', 'Le mot de passe actuel est incorrect.');
        }

        // Mise à jour du mot de passe
        $user->password = Hash::make($request->newPassword);
        $user->save();

        return back()->with('message', 'Votre mot de passe a été modifié avec succès.');
    }
    public function groupesStagiaire()
    {
        return $this->belongsToMany(Group::class, 'group_user');
    }

    public function showCodeLoginForm()
    {
        return view('frontend.contenu.code_login');
    }
    public function loginByCode(Request $request)
    {
        $request->validate([
            'code_acces' => 'required|string|size:6',
        ]);

        $user = User::where('code_acces', strtoupper($request->code_acces))->first();

        if ($user && $user->role === 'stagiaire') {
            Auth::login($user);
            return redirect()->route('stagiaire.dashboard')->with('success', 'Bienvenue !');
        }

        return back()->withErrors([
            'code_acces' => 'Code d’accès invalide ou utilisateur non autorisé.',
        ]);
    }





}
