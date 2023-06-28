<?php

namespace App\Service;

use App\Entity\GuestUser;


interface GuestUserServiceInterface
{

    public function save(GuestUser $guestUser): void;


    public function countEmailsUsedInLast24Hours(string $email): int;
}
