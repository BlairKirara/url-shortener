<?php

namespace App\Service;

use App\Entity\User;
use Knp\Component\Pager\Pagination\PaginationInterface;

/**
 * Interface UserServiceInterface.
 */
interface UserServiceInterface
{
    public function getPaginatedList(int $page): PaginationInterface;

    public function save(User $user): void;
}
