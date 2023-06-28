<?php

namespace App\Service;

use App\Entity\Tag;
use App\Repository\TagRepository;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;

/**
 * Class TagService.
 */
class TagService implements TagServiceInterface
{
    /**
     * @var TagRepository
     */
    private TagRepository $tagRepository;

    /**
     * @var PaginatorInterface
     */
    private PaginatorInterface $paginator;

    /**
     * Constructor.
     *
     * @param TagRepository $tagRepository
     * @param PaginatorInterface $paginator
     */
    public function __construct(TagRepository $tagRepository, PaginatorInterface $paginator)
    {
        $this->tagRepository = $tagRepository;
        $this->paginator = $paginator;
    }

    /**
     * @param int $page
     * @return PaginationInterface
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
     * @param Tag $tag
     * @return void
     */
    public function save(Tag $tag): void
    {
        $this->tagRepository->save($tag);
    }

    /**
     * @param Tag $tag
     * @return void
     */
    public function delete(Tag $tag): void
    {
        $this->tagRepository->delete($tag);
    }

    /**
     * @param string $name
     * @return Tag|null
     */
    public function findOneByName(string $name): ?Tag
    {
        return $this->tagRepository->findOneByName($name);
    }

    /**
     * @param int $id
     * @return Tag|null
     */
    public function findOneById(int $id): ?Tag
    {
        return $this->tagRepository->findOneById($id);
    }
}
