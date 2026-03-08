<?php

namespace App\Mail;

use App\Models\Group;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class StagiaireGroupInvitation extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public User $user,
        public Group $group,
        public string $loginUrl
    ) {
    }

    public function build(): self
    {
        return $this
            ->subject('Accès à votre espace stagiaire Onéduc')
            ->markdown('emails.stagiaire_group_invitation', [
                'user' => $this->user,
                'group' => $this->group,
                'loginUrl' => $this->loginUrl,
            ]);
    }
}

