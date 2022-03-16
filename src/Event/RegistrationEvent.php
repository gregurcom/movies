<?php

declare(strict_types=1);

namespace App\Event;

use App\Entity\User;

class RegistrationEvent
{
    public function __construct(private User $user) {}

    public function getUser(): User
    {
        return $this->user;
    }
}