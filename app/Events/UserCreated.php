<?php

namespace App\Events;

use App\Models\User;
use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Events\Dispatchable;

class UserCreated
{
    use Dispatchable, SerializesModels;

    public $user;
    public $password;

    public function __construct(User $user, $password)
    {
        $this->user = $user;
        $this->password = $password;
    }
}
