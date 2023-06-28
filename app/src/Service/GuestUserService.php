<?php
/**
 * Guest User service.
 */

namespace App\Service;

use App\Entity\GuestUser;
use App\Repository\GuestUserRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;


class GuestUserService implements GuestUserServiceInterface
{

    private GuestUserRepository $guestUserRepository;


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


    public function countEmailsUsedInLast24Hours(string $email): int
    {
        return $this->guestUserRepository->countEmailsUsedInLast24Hours($email);
    }
}
