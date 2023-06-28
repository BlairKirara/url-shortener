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
    /**
     * @param int $page
     * @param User $users
     * @return PaginationInterface
     */
    public function getPaginatedList(int $page, User $users): PaginationInterface;

    /**
     * @param int $page
     * @param array $filters
     * @return PaginationInterface
     */
    public function getPaginatedListForAll(int $page, array $filters = []): PaginationInterface;

    /**
     * @param Url $url
     * @return void
     */
    public function save(Url $url): void;

    /**
     * @param Url $url
     * @return void
     */
    public function delete(Url $url): void;

    /**
     * @return string
     */
    public function shortenUrl(): string;

    /**
     * @param string $shortName
     * @return Url|null
     */
    public function findOneByShortName(string $shortName): ?Url;
}
