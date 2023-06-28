<?php
/**
 * Tag service.
 */

namespace App\Service;

use App\Entity\Tag;
use App\Repository\TagRepository;
use Doctrine\ORM\NonUniqueResultException;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;


class TagService implements TagServiceInterface
{

    private TagRepository $tagRepository;


    private PaginatorInterface $paginator;


    public function __construct(TagRepository $tagRepository, PaginatorInterface $paginator)
    {
        $this->tagRepository = $tagRepository;
        $this->paginator = $paginator;
    }


    public function getPaginatedList(int $page): PaginationInterface
    {
        return $this->paginator->paginate(
            $this->tagRepository->queryAll(),
            $page,
            TagRepository::PAGINATOR_ITEMS_PER_PAGE
        );
    }


    public function save(Tag $tag): void
    {
        $this->tagRepository->save($tag);
    }


    public function delete(Tag $tag): void
    {
        $this->tagRepository->delete($tag);
    }


    public function findOneByName(string $name): ?Tag
    {
        return $this->tagRepository->findOneByName($name);
    }


    public function findOneById(int $id): ?Tag
    {
        return $this->tagRepository->findOneById($id);
    }
}
