<?php
/**
 * Tag service.
 */

namespace App\Service;

use App\Entity\Tag;
use App\Repository\TagRepository;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;

/**
 * Class TagService.
 *
 * This class provides services related to tags.
 */
class TagService implements TagServiceInterface
{
    private TagRepository $tagRepository;

    private PaginatorInterface $paginator;

    /**
     * Constructor.
     *
     * @param TagRepository      $tagRepository The tag repository
     * @param PaginatorInterface $paginator     The paginator
     */
    public function __construct(TagRepository $tagRepository, PaginatorInterface $paginator)
    {
        $this->tagRepository = $tagRepository;
        $this->paginator = $paginator;
    }

    /**
     * Retrieves a paginated list of tags.
     *
     * @param int $page The page number
     *
     * @return PaginationInterface The paginated list of tags
     */
    public function getPaginatedList(int $page): PaginationInterface
    {
        return $this->paginator->paginate(
            $this->tagRepository->queryAll(),
            $page,
            TagRepository::PAGINATOR_ITEMS_PER_PAGE
        );
    }

    /**
     * Saves a tag.
     *
     * @param Tag $tag The tag to save
     */
    public function save(Tag $tag): void
    {
        $this->tagRepository->save($tag);
    }

    /**
     * Deletes a tag.
     *
     * @param Tag $tag The tag to delete
     */
    public function delete(Tag $tag): void
    {
        $this->tagRepository->delete($tag);
    }

    /**
     * Finds a tag by name.
     *
     * @param string $name The name of the tag
     *
     * @return Tag|null The found tag or null if not found
     */
    public function findOneByName(string $name): ?Tag
    {
        return $this->tagRepository->findOneByName($name);
    }

    /**
     * Finds a tag by ID.
     *
     * @param int $id The ID of the tag
     *
     * @return Tag|null The found tag or null if not found
     */
    public function findOneById(int $id): ?Tag
    {
        return $this->tagRepository->findOneById($id);
    }
}
