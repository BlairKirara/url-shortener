<?php

/**
 * Tag repository.
 */

namespace App\Repository;

use App\Entity\Tag;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Class TagRepository.
 *
 * This repository is responsible for managing Tag entities.
 */
class TagRepository extends ServiceEntityRepository
{
    public const PAGINATOR_ITEMS_PER_PAGE = 10;

    /**
     * Constructor.
     *
     * @param ManagerRegistry $registry The manager registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Tag::class);
    }

    /**
     * Returns a query builder for fetching all tags.
     *
     * @return QueryBuilder The query builder
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
     * Saves a Tag entity.
     *
     * @param Tag $tag The Tag entity to save
     */
    public function save(Tag $tag): void
    {
        $this->_em->persist($tag);
        $this->_em->flush();
    }

    /**
     * Deletes a Tag entity.
     *
     * @param Tag $tag The Tag entity to delete
     */
    public function delete(Tag $tag): void
    {
        $this->_em->remove($tag);
        $this->_em->flush();
    }

    /**
     * Returns a QueryBuilder instance or creates a new one.
     *
     * @param QueryBuilder|null $queryBuilder The optional QueryBuilder instance
     *
     * @return QueryBuilder The QueryBuilder instance
     */
    private function getOrCreateQueryBuilder(?QueryBuilder $queryBuilder = null): QueryBuilder
    {
        return $queryBuilder ?? $this->createQueryBuilder('tag');
    }
}
