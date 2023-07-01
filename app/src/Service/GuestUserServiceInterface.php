<?php

namespace App\Service;

use App\Entity\GuestUser;

/**
 * Interface GuestUserInterface.
 */
interface GuestUserServiceInterface
{
    public function save(GuestUser $guestUser): void;

    public function countEmailUse(string $email): int;
}
