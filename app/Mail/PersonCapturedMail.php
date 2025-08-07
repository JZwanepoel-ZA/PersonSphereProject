<?php

namespace App\Mail;

use App\Models\Person;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PersonCapturedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Person $person)
    {
    }

    public function build(): self
    {
        return $this->subject('Person Captured')
            ->view('emails.person_captured');
    }
}
