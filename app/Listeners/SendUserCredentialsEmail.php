<?php

namespace App\Listeners;

use App\Events\UserCreated; 
use App\Mail\UserCredentialsMail;
use Illuminate\Support\Facades\Mail;
class SendUserCredentialsEmail
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(UserCreated $event): void
    {
        Mail::to($event->user->email)->send(new UserCredentialsMail($event->user, $event->password));
   
    }
}
