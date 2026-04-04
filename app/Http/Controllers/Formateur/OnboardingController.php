<?php

namespace App\Http\Controllers\Formateur;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;

class OnboardingController extends Controller
{
    public function show(?string $step = null): RedirectResponse
    {
        return redirect()->route('formateur.parcours.index');
    }
}
