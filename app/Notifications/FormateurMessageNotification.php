<?php

namespace App\Notifications;

use App\Models\FormateurMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class FormateurMessageNotification extends Notification
{
    use Queueable;

    public function __construct(
        private readonly FormateurMessage $message,
        private readonly array $channels,
    ) {
    }

    public function via(object $notifiable): array
    {
        return $this->channels;
    }

    public function toMail(object $notifiable): MailMessage
    {
        $formateur = $this->message->formateur;
        $formateurName = $formateur ? trim($formateur->prenom . ' ' . $formateur->name) : 'Votre formateur';
        $subject = $this->message->subject ?: 'Message de votre formateur';

        $mail = (new MailMessage)
            ->subject('[Onéduc] ' . $subject)
            ->greeting('Bonjour ' . ($notifiable->prenom ?? $notifiable->name) . ',')
            ->line($formateurName . ' vous a envoyé un message :')
            ->line('---')
            ->line($this->message->body);

        if (config('app.url')) {
            $mail->action('Voir mes messages', url(route('stagiaire.messages.index', [], false)));
        }

        return $mail->line('L\'équipe Onéduc');
    }

    public function toArray(object $notifiable): array
    {
        $formateur = $this->message->formateur;
        $formateurName = $formateur ? trim($formateur->prenom . ' ' . $formateur->name) : 'Votre formateur';
        $subject = $this->message->subject ?: 'Message de votre formateur';

        return [
            'type' => 'formateur_message',
            'title' => $subject,
            'message' => $formateurName . ' : ' . mb_strimwidth($this->message->body, 0, 120, '…'),
            'formateur_id' => $this->message->formateur_id,
            'formateur_name' => $formateurName,
            'message_id' => $this->message->id,
            'url' => route('stagiaire.messages.index'),
            'created_at' => now()->toIso8601String(),
        ];
    }
}
