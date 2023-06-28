<?php

namespace App\Service;

use App\Entity\GuestUser;
use App\Repository\GuestUserRepository;

/**
 * Class GuestUserService.
 */
class GuestUserService implements GuestUserServiceInterface
{
    /**
     * @var GuestUserRepository
     */
    private GuestUserRepository $guestUserRepository;

    /**
     * Constructor.
     *
     * @param GuestUserRepository $guestUserRepository
     */
    public function __construct(GuestUserRepository $guestUserRepository)
    {
        $this->guestUserRepository = $guestUserRepository;
    }

    /**
     * @param GuestUser $guestUser
     * @return void
     */
    public function save(GuestUser $guestUser): void
    {
        if ($this->guestUserRepository->findOneByEmail($guestUser->getEmail())) {
            return;
        }

        $this->guestUserRepository->save($guestUser);
    }

    /**
     * @param string $email
     * @return int
     * @throws \Doctrine\ORM\NoResultException
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function countEmailUse(string $email): int
    {
        return $this->guestUserRepository->countEmailUse($email);
    }
}
