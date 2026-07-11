<?php

namespace App\Http\Controllers;

use App\Models\Group;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EmargementJoinController extends Controller
{
    public function home(): View
    {
        return view('emargement.join');
    }

    public function resolveCode(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'code' => ['required', 'string', 'max:12'],
        ]);

        return redirect()->route('emargement.join.code', ['code' => strtoupper(trim($data['code']))]);
    }

    public function joinByCode(string $code): RedirectResponse
    {
        $group = Group::query()
            ->where('emargement_code', strtoupper(trim($code)))
            ->where('emargement_enabled', true)
            ->firstOrFail();

        return redirect()->route('stagiaire.emargement.show', $group->id);
    }
}
