<?php
/**
 * Guest user service interface.
 */

namespace App\Service;

use App\Entity\GuestUser;

/**
 * Interface GuestUserServiceInterface.
 *
 * This interface defines the contract for the guest user service.
 */
interface GuestUserServiceInterface
{
    /**
     * Saves a guest user.
     *
     * @param GuestUser $guestUser The guest user object to save
     */
    public function save(GuestUser $guestUser): void;

    /**
     * Counts the number of times an email has been used by guest users.
     *
     * @param string $email The email to count usage for
     * @return int The number of times the email has been used
     */
    public function countEmailUse(string $email): int;
}
