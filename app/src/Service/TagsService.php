<?php
/**
 * Tags service.
 */

namespace App\Service;

use App\Entity\Tags;
use App\Repository\TagsRepository;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;

class TagsService implements TagsServiceInterface
{
    private TagsRepository $tagsRepository;

    private PaginatorInterface $paginator;

    public function __construct(TagsRepository $tagsRepository, PaginatorInterface $paginator)
    {
        $this->tagsRepository = $tagsRepository;
        $this->paginator = $paginator;
    }// end __construct()

    public function getPaginatedList(int $page): PaginationInterface
    {
        return $this->paginator->paginate(
            $this->tagsRepository->queryAll(),
            $page,
            TagsRepository::PAGINATOR_ITEMS_PER_PAGE
        );
    }// end getPaginatedList()

    /**
     * Save entity.
     *
     * @param Tags $tags Tags entity
     */
    public function save(Tags $tags): void
    {
        $this->tagsRepository->save($tags);
    }// end save()

    /**
     * Delete entity.
     *
     * @param Tags $tags Tags entity
     */
    public function delete(Tags $tags): void
    {
        $this->tagsRepository->delete($tags);
    }// end delete()

    /**
     * Find by title.
     *
     * @param string $name Tags name
     *
     * @return Tags|null Tags entity
     */
    public function findOneByName(string $name): ?Tags
    {
        return $this->tagsRepository->findOneByName($name);
    }// end findOneByName()

    /**
     * Find by id.
     *
     * @param int $id Tags id
     *
     * @return Tags|null Tags entity
     *
     * @throws NonUniqueResultException
     */
    public function findOneById(int $id): ?Tags
    {
        return $this->tagsRepository->findOneById($id);
    }// end findOneById()

    /**
     * Find by title.
     *
     * @param string $title Tag title
     *
     * @return Tag|null Tag entity
     */
    public function findOneByTitle(string $title): ?Tag
    {
        return $this->tagRepository->findOneByTitle($title);
    }// end findOneByTitle()
}// end class
