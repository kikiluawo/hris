<?php

namespace App\Mail;

use App\Models\Application;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ScreeningPassed extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Application $application)
    {
        $this->application->loadMissing(['candidate', 'jobOpening.company']);
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Selamat, Anda lolos screening CV — ' . $this->application->jobOpening->title,
        );
    }

    public function content(): Content
    {
        return new Content(view: 'emails.screening-passed');
    }
}
