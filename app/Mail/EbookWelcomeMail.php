<?php

namespace App\Mail;

use App\Models\Subscriber;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class EbookWelcomeMail extends Mailable
{
    use Queueable, SerializesModels;

    public Subscriber $subscriber;

    public string $downloadUrl;

    /**
     * Create a new message instance.
     */
    public function __construct(Subscriber $subscriber, string $downloadUrl)
    {
        $this->subscriber = $subscriber;
        $this->downloadUrl = $downloadUrl;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        return $this->subject('Welcome! Your Free eBook is Ready')
            ->view('emails.ebook-welcome')
            ->with([
                'subscriber' => $this->subscriber,
                'downloadUrl' => $this->downloadUrl,
            ]);
    }
}
