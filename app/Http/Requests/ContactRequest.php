<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ContactRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            // Honeypot : doit rester vide
            'website'          => 'nullable|prohibited',

            'prenom'           => 'nullable|string|max:255',
            'nom'              => 'required|string|max:255',
            'type_utilisateur' => 'required|in:formateur,stagiaire',

            // Objet conditionnel
            'objet_formateur'  => 'required_if:type_utilisateur,formateur|nullable|in:demande_info,support,creation_module,autre',
            'objet_stagiaire'  => 'required_if:type_utilisateur,stagiaire|nullable|in:bug,incomprehension,probleme_connexion',

            'email'            => 'required|email:rfc,dns',
            'phone'            => 'nullable|string|max:40',
            'heure_appel'      => 'nullable|string|max:10',
            'message'          => 'required|string|min:5|max:5000',

            // CAPTCHA sera ajouté à l’étape 6
            'g-recaptcha-response' => 'required|captcha',
            'g-recaptcha-response.required' => 'Veuillez cocher la case reCAPTCHA.',
            'g-recaptcha-response.captcha'  => 'Vérification reCAPTCHA invalide.',
        ];
    }

    public function messages(): array
    {
        return [
            'website.prohibited' => 'Anti-spam détecté.',
            'nom.required'       => 'Le nom est obligatoire.',
            'type_utilisateur.required' => 'Indiquez votre profil.',
            'objet_formateur.required_if' => 'Sélectionnez un objet.',
            'objet_stagiaire.required_if' => 'Sélectionnez un objet.',
            'email.required'     => 'L’email est obligatoire.',
            'email.email'        => 'Email invalide.',
            'message.required'   => 'Le message est obligatoire.',
        ];
    }
}
