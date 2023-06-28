<?php

namespace App\Service;

use App\Entity\User;
use Knp\Component\Pager\Pagination\PaginationInterface;

interface UserServiceInterface
{

    public function getPaginatedList(int $page): PaginationInterface;


    public function save(User $user): void;

    public function findOneBy(string $email): ?User;
}
