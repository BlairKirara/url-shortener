<?php

namespace App\Service;

use App\Entity\User;
use Knp\Component\Pager\Pagination\PaginationInterface;

/**
 * Interface UserServiceInterface.
 */
interface UserServiceInterface
{
    /**
     * @param int $page
     * @return PaginationInterface
     */
    public function getPaginatedList(int $page): PaginationInterface;

    /**
     * @param User $user
     * @return void
     */
    public function save(User $user): void;

    /**
     * @param string $email
     * @return User|null
     */
    public function findOneBy(string $email): ?User;
}
