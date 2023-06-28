<?php

namespace App\Service;

use App\Entity\Tag;
use Knp\Component\Pager\Pagination\PaginationInterface;

/**
 * Interface TagServiceInterface.
 */
interface TagServiceInterface
{
    /**
     * @param int $page
     * @return PaginationInterface
     */
    public function getPaginatedList(int $page): PaginationInterface;

    /**
     * @param Tag $tag
     * @return void
     */
    public function save(Tag $tag): void;

    /**
     * @param Tag $tag
     * @return void
     */
    public function delete(Tag $tag): void;

    /**
     * @param string $name
     * @return Tag|null
     */
    public function findOneByName(string $name): ?Tag;
}
