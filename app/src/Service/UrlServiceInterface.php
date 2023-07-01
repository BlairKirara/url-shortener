<?php

namespace App\Service;

use App\Entity\Url;
use App\Entity\User;
use Knp\Component\Pager\Pagination\PaginationInterface;

/**
 * Interface UrlServiceInterface.
 */
interface UrlServiceInterface
{
    public function getPaginatedList(int $page, User $users): PaginationInterface;

    public function getPaginatedListForAll(int $page, array $filters = []): PaginationInterface;

    public function save(Url $url): void;

    public function delete(Url $url): void;

    public function shortenUrl(): string;

    public function findOneByShortName(string $shortName): ?Url;
}
