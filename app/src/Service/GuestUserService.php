<?php

namespace App\Service;

use App\Entity\GuestUser;
use App\Repository\GuestUserRepository;

/**
 * Class GuestUserService.
 */
class GuestUserService implements GuestUserServiceInterface
{
    private GuestUserRepository $guestUserRepository;

    /**
     * Constructor.
     */
    public function __construct(GuestUserRepository $guestUserRepository)
    {
        $this->guestUserRepository = $guestUserRepository;
    }

    public function save(GuestUser $guestUser): void
    {
        if ($this->guestUserRepository->findOneByEmail($guestUser->getEmail())) {
            return;
        }

        $this->guestUserRepository->save($guestUser);
    }

    /**
     * @throws \Doctrine\ORM\NoResultException
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function countEmailUse(string $email): int
    {
        return $this->guestUserRepository->countEmailUse($email);
    }
}
