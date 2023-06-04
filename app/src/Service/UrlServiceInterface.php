<?php
/**
 * Url service interface.
 */

namespace App\Service;

use App\Entity\Url;
use Knp\Component\Pager\Pagination\PaginationInterface;

/**
 * Interface UrlServiceInterface.
 */
interface UrlServiceInterface
{
    /**
     * Get paginated list.
     *
     * @param int $page Page number
     *
     * @return PaginationInterface<string, mixed> Paginated list
     */
    public function getPaginatedList(int $page): PaginationInterface;

    /**
     * Save entity.
     *
     * @param Url $url Url entity
     */
    public function save(Url $url): void;


}
