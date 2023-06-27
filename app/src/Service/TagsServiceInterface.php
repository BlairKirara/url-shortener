<?php
/**
 * Tags service interface.
 */

namespace App\Service;

use App\Entity\Tags;
use Knp\Component\Pager\Pagination\PaginationInterface;

/**
 * Interface TagsServiceInterface.
 */
interface TagsServiceInterface
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
     * @param Tags $tags Tags entity
     */
    public function save(Tags $tags): void;

    /**
     * Find by title.
     *
     * @param string $title Tags title
     *
     * @return Tags|null Tags entity
     */
    public function findOneByTitle(string $title): ?Tags;

}