<?php

namespace App\Service;

use App\Entity\GuestUser;

/**
 * Interface GuestUserInterface.
 */
interface GuestUserServiceInterface
{
    /**
     * @param GuestUser $guestUser
     * @return void
     */
    public function save(GuestUser $guestUser): void;

    /**
     * @param string $email
     * @return int
     */
    public function countEmailUse(string $email): int;
}
