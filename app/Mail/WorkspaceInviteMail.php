<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class WorkspaceInviteMail extends Mailable
{
    use Queueable, SerializesModels;

    public $inviteUrl;  // phải khai báo public để biến được tự động truyền ra view

    public function __construct($inviteUrl)
    {
        $this->inviteUrl = $inviteUrl;
    }

    public function build()
    {
        return $this->subject('You are invited to join a workspace')
                    ->markdown('emails.workspace_invite');
    }
}
