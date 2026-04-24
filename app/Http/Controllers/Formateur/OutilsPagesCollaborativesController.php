<?php

namespace App\Http\Controllers\Formateur;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class OutilsPagesCollaborativesController extends Controller
{
    public function index(): View
    {
        $baseUrl = rtrim((string) config('services.hedgedoc.base_url', ''), '/');
        $newPath = (string) config('services.hedgedoc.new_path', '/new');

        $newUrl = $baseUrl !== ''
            ? $baseUrl . '/' . ltrim($newPath, '/')
            : null;

        return view('formateur.outils.pages_collaboratives_index', [
            'baseUrl' => $baseUrl,
            'newUrl' => $newUrl,
        ]);
    }
}
