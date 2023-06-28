<?php

namespace App\Service;

use App\Entity\UrlData;
use Knp\Component\Pager\Pagination\PaginationInterface;

/**
 * Interface UrlDataServiceInterface.
 */
interface UrlDataServiceInterface
{
    /**
     * @param UrlData $urlData
     * @return void
     */
    public function save(UrlData $urlData): void;

    /**
     * @param int $page
     * @return PaginationInterface
     */
    public function countVisits(int $page): PaginationInterface;

    /**
     * @param int $id
     * @return void
     */
    public function deleteUrlVisits(int $id): void;
}
