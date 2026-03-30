<?php

namespace App\Mail;

use App\Models\LeaveRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class LeaveStatusUpdated extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public LeaveRequest $leave) {}

    public function envelope(): Envelope
    {
        $subject = match ($this->leave->status) {
            'approved' => 'Your leave has been approved',
            'rejected' => 'Your leave request was not approved',
            default    => 'Your leave request has been updated',
        };

        return new Envelope(subject: $subject);
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.leave-status-updated',
        );
    }
}
