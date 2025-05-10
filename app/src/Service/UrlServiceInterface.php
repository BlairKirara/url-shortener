<?php

/**
 * Url service interface.
 */

namespace App\Service;

use App\Entity\Url;
use App\Entity\User;
use Knp\Component\Pager\Pagination\PaginationInterface;

/**
 * Interface UrlServiceInterface.
 *
 * This interface defines the contract for the URL service.
 */
interface UrlServiceInterface
{
    /**
     * Retrieves a paginated list of URLs for a specific user.
     *
     * @param int       $page    The page number
     * @param User|null $users   The user object
     * @param array     $filters The filters to apply
     *
     * @return PaginationInterface The paginated list of URLs
     */
    public function getPaginatedList(int $page, ?User $users, array $filters = []): PaginationInterface;

    /**
     * Retrieves a paginated list of URLs for all users.
     *
     * @param int   $page    The page number
     * @param array $filters The filters to apply
     *
     * @return PaginationInterface The paginated list of URLs
     */
    public function getPaginatedListForAll(int $page, array $filters = []): PaginationInterface;

    /**
     * Saves a URL.
     *
     * @param Url $url The URL to save
     */
    public function save(Url $url): void;

    /**
     * Deletes a URL.
     *
     * @param Url $url The URL to delete
     */
    public function delete(Url $url): void;

    /**
     * Generates a shortened URL.
     *
     * @param int $length The length of the generated URL
     *
     * @return string The generated shortened URL
     */
    public function shortenUrl(int $length = 6): string;

    /**
     * Finds a URL by its short name.
     *
     * @param string $shortName The short name of the URL
     *
     * @return Url|null The URL object if found, null otherwise
     */
    public function findOneByShortName(string $shortName): ?Url;
}
