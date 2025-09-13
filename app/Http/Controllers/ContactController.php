<?php

namespace App\Http\Controllers;

use App\Http\Requests\ContactRequest;
use App\Mail\ContactMessage;
use Illuminate\Support\Facades\Mail;
use Illuminate\Http\Request;
use App\Mail\ContactConfirmation;

class ContactController extends Controller
{
    /**
     * Affiche le formulaire de contact
     */
    public function index()
    {
        return view('frontend.contenu.contact');
    }

    /**
     * Traite l’envoi du formulaire de contact
     */
    public function send(ContactRequest $request)
    {
        // Validation avec captcha
        $data = $request->validate([
            'prenom'              => 'nullable|string|max:100',
            'nom'                 => 'required|string|max:100',
            'type_utilisateur'    => 'required|in:formateur,stagiaire,autre',
            'objet_formateur'     => 'nullable|string|max:100',
            'objet_stagiaire'     => 'nullable|string|max:100',
            'email'               => 'required|email',
            'phone'               => 'nullable|string|max:30',
            'heure_appel'         => 'nullable|string|max:50',
            'message'             => 'required|string|max:5000',
            'g-recaptcha-response'=> 'required|captcha', // <-- ajout captcha
        ]);

        // Détermination de l’objet en fonction du profil
        $objet = $data['type_utilisateur'] === 'formateur'
            ? ($data['objet_formateur'] ?? 'autre')
            : ($data['objet_stagiaire'] ?? 'bug');

        // Payload pour la vue d’email
        $payload = [
            'prenom'           => $data['prenom'] ?? '',
            'nom'              => $data['nom'],
            'type_utilisateur' => $data['type_utilisateur'],
            'objet'            => $objet,
            'email'            => $data['email'],
            'phone'            => $data['phone'] ?? '',
            'heure_appel'      => $data['heure_appel'] ?? '',
            'message'          => $data['message'],
        ];

        // Envoi de l’email
        Mail::to('contact@oneduc.fr')->send(new ContactMessage($payload));

        // déjà présent :
        Mail::to('contact@oneduc.fr')->send(new ContactMessage($payload));

        // confirmation à l’expéditeur
        Mail::to($payload['email'])->send(new ContactConfirmation($payload));


        return back()->with('success', 'Votre message a bien été envoyé.');
    }
}
