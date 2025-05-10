<?php

/**
 * Tag service interface.
 */

namespace App\Service;

use App\Entity\Tag;
use Knp\Component\Pager\Pagination\PaginationInterface;

/**
 * Interface TagServiceInterface.
 *
 * This interface defines the contract for tag-related services.
 */
interface TagServiceInterface
{
    /**
     * Retrieves a paginated list of tags.
     *
     * @param int $page The page number
     *
     * @return PaginationInterface The paginated list of tags
     */
    public function getPaginatedList(int $page): PaginationInterface;

    /**
     * Saves a tag.
     *
     * @param Tag $tag The tag to save
     */
    public function save(Tag $tag): void;

    /**
     * Deletes a tag.
     *
     * @param Tag $tag The tag to delete
     */
    public function delete(Tag $tag): void;

    /**
     * Finds a tag by name.
     *
     * @param string $name The name of the tag
     *
     * @return Tag|null The found tag or null if not found
     */
    public function findOneByName(string $name): ?Tag;
}
