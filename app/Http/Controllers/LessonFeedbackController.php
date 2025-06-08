<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\LessonFeedback;
use Illuminate\Support\Facades\Auth;
use App\Models\ModuleLecture;

class LessonFeedbackController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'lesson_id' => 'required|exists:module_lectures,id',
            'comment'   => 'required|string',
            'type'      => 'nullable|string|in:bug,erreur,incomprehension,suggestion',
            'rating'    => 'nullable|integer|min:1|max:5',
            'urgency' => 'nullable|integer|min:1|max:5',

        ]);

        $validated['user_id'] = auth()->id();

        LessonFeedback::create($validated);


        // ✅ On récupère la leçon et le module parent
        $lecture = ModuleLecture::with('section.module')->findOrFail($validated['lesson_id']);
        $moduleId = $lecture->section->module->id ?? null;

        return redirect()->route('module.lecture', [
            'id' => $moduleId,
            'lecon' => $lecture->id,
        ])->with('success', 'Merci pour votre retour !')
        ->with('open_modal', true);
    }
    public function index($lesson_id)
    {
        $feedbacks = LessonFeedback::with('user')
            ->where('lesson_id', $lesson_id)
            ->latest()
            ->get();

        return response()->json($feedbacks);
    }

    public function adminIndex()
    {
        $feedbacks = LessonFeedback::with(['user', 'lesson'])->latest()->paginate(15);
        return view('admin.backend.feedback.index', compact('feedbacks'));
    }
    public function destroy($id)
    {
        $feedback = LessonFeedback::findOrFail($id);
        $feedback->delete();

        return back()->with('success', 'Le commentaire a été supprimé avec succès.');
    }

}
