<?php

namespace App\Http\Controllers;

use App\Http\Requests\ContactRequest;
use App\Mail\ContactMessage;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
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
        $data = $request->validated();

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

        Mail::to('contact@oneduc.fr')->send(new ContactMessage($payload));
        Mail::to($payload['email'])->send(new ContactConfirmation($payload));

        $this->sendToDiscord($payload);

        return back()->with('success', 'Votre message a bien été envoyé.');
    }

    private function sendToDiscord(array $payload): void
    {
        $webhookUrl = (string) config('services.discord.support_webhook_url');
        if ($webhookUrl === '') {
            return;
        }

        $objetLabels = [
            'demande_info' => 'Demande d’information',
            'support' => 'Support technique',
            'creation_module' => 'Création de module/leçon',
            'autre' => 'Autre',
            'bug' => 'Signalement de bug',
            'incomprehension' => 'Incompréhension sur une leçon',
            'probleme_connexion' => 'Problème de connexion',
        ];

        $objet = (string) ($payload['objet'] ?? 'autre');
        $objetLabel = $objetLabels[$objet] ?? ucfirst(str_replace('_', ' ', $objet));

        $discordPayload = [
            'username' => 'Support Oneduc',
            'embeds' => [[
                'title' => 'Nouvelle demande de support - oneduc.fr',
                'color' => 3447003,
                'fields' => [
                    ['name' => 'Auteur', 'value' => trim(($payload['prenom'] ?? '').' '.$payload['nom']), 'inline' => true],
                    ['name' => 'Email', 'value' => $payload['email'], 'inline' => true],
                    ['name' => 'Profil', 'value' => ucfirst((string) $payload['type_utilisateur']), 'inline' => true],
                    ['name' => 'Motif', 'value' => $objetLabel, 'inline' => false],
                    ['name' => 'Téléphone', 'value' => $payload['phone'] !== '' ? $payload['phone'] : 'Non renseigné', 'inline' => true],
                    ['name' => 'Créneau d’appel', 'value' => $payload['heure_appel'] !== '' ? $payload['heure_appel'] : 'Non renseigné', 'inline' => true],
                    ['name' => 'Message', 'value' => mb_substr((string) $payload['message'], 0, 1024), 'inline' => false],
                ],
                'footer' => ['text' => 'Envoyé depuis le centre de support Oneduc'],
            ]],
        ];

        try {
            Http::asJson()->timeout(8)->post($webhookUrl, $discordPayload)->throw();
        } catch (\Throwable $e) {
            Log::warning('Discord support webhook failed.', ['error' => $e->getMessage()]);
        }
    }
}
