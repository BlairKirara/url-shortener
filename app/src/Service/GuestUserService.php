<?php
/**
 * Guest user service.
 */

namespace App\Service;

use App\Entity\GuestUser;
use App\Repository\GuestUserRepository;

/**
 * Class GuestUserService.
 *
 * This class provides services related to guest users.
 */
class GuestUserService implements GuestUserServiceInterface
{
    private GuestUserRepository $guestUserRepository;

    /**
     * Constructor.
     *
     * @param GuestUserRepository $guestUserRepository The repository for guest users
     */
    public function __construct(GuestUserRepository $guestUserRepository)
    {
        $this->guestUserRepository = $guestUserRepository;
    }

    /**
     * Saves a guest user.
     *
     * @param GuestUser $guestUser The guest user object to save
     */
    public function save(GuestUser $guestUser): void
    {
        // Check if a guest user with the same email already exists
        if ($this->guestUserRepository->findOneByEmail($guestUser->getEmail())) {
            return; // If a guest user already exists, return without saving
        }

        // Save the guest user
        $this->guestUserRepository->save($guestUser);
    }

    /**
     * Counts the number of times an email has been used by guest users.
     *
     * @param string $email The email to count usage for
     *
     * @return int The number of times the email has been used
     *
     * @throws \Doctrine\ORM\NoResultException
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function countEmailUse(string $email): int
    {
        return $this->guestUserRepository->countEmailUse($email);
    }
}
