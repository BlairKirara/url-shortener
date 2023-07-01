<?php
/**
 * Url data service interface.
 */

namespace App\Service;

use App\Entity\UrlData;
use Knp\Component\Pager\Pagination\PaginationInterface;

/**
 * Interface UrlDataServiceInterface.
 *
 * This interface defines the contract for URL data service.
 */
interface UrlDataServiceInterface
{
    /**
     * Saves URL data.
     *
     * @param UrlData $urlData The URL data to save
     */
    public function save(UrlData $urlData): void;

    /**
     * Counts visits and returns a paginated list.
     *
     * @param int $page The page number
     *
     * @return PaginationInterface The paginated list of visit counts
     */
    public function countVisits(int $page): PaginationInterface;

    /**
     * Deletes URL visits by ID.
     *
     * @param int $id The ID of the URL visits to delete
     */
    public function deleteUrlVisits(int $id): void;
}
