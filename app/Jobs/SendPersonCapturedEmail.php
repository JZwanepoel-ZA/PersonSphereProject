<?php

namespace App\Jobs;

use App\Mail\PersonCapturedMail;
use App\Models\Person;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendPersonCapturedEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public Person $person)
    {
    }

    public function handle(): void
    {
        Mail::to($this->person->email)->send(new PersonCapturedMail($this->person));
    }
}
