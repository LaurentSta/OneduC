<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ContactController extends Controller
{
    public function send(Request $request)
    {
        $request->validate([
            'nom' => 'required|string|max:255',
            'email' => 'required|email',
            'message' => 'required|string',
        ]);

        // Envoi d’email ou stockage base (selon ton choix)
        Mail::to('contact@oneduc.fr')->send(new \App\Mail\ContactFormMail($request->all()));

        return back()->with('success', 'Votre message a bien été envoyé. Merci pour votre retour !');
    }

}
