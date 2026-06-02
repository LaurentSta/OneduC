<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ModuleQuestionnaireSubmitted extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public array $questionnaire)
    {
    }

    public function envelope(): Envelope
    {
        $trainerEmail = (string) ($this->questionnaire['trainer']['email'] ?? '');
        $trainerName = trim((string) ($this->questionnaire['trainer']['full_name'] ?? ''));
        $moduleNumber = (int) ($this->questionnaire['module']['number'] ?? 0);

        return new Envelope(
            from: new Address(config('mail.from.address'), config('mail.from.name')),
            replyTo: filter_var($trainerEmail, FILTER_VALIDATE_EMAIL)
                ? [new Address($trainerEmail, $trainerName ?: $trainerEmail)]
                : [],
            subject: 'Questionnaire d’évaluation Onéduc - Module '.$moduleNumber,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.module-questionnaire-submitted',
            with: ['questionnaire' => $this->questionnaire],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
