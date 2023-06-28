<?php

namespace App\Repository;

use App\Entity\Tag;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Class TagRepository.
 */
class TagRepository extends ServiceEntityRepository
{
    /**
     *
     */
    public const PAGINATOR_ITEMS_PER_PAGE = 10;

    /**
     * Constructor.
     *
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Tag::class);
    }

    /**
     * @return QueryBuilder
     */
    public function queryAll(): QueryBuilder
    {
        return $this->getOrCreateQueryBuilder()
            ->select(
                'partial tag.{id, name}'
            )
            ->orderBy('tag.id', 'ASC');
    }

    /**
     * @param Tag $tag
     * @return void
     */
    public function save(Tag $tag): void
    {
        $this->_em->persist($tag);
        $this->_em->flush();
    }

    /**
     * @param Tag $tag
     * @return void
     */
    public function delete(Tag $tag): void
    {
        $this->_em->remove($tag);
        $this->_em->flush();
    }

    /**
     * @param QueryBuilder|null $queryBuilder
     * @return QueryBuilder
     */
    private function getOrCreateQueryBuilder(QueryBuilder $queryBuilder = null): QueryBuilder
    {
        return $queryBuilder ?? $this->createQueryBuilder('tag');
    }
}
