<?php

/**
 * Url data repository.
 */

namespace App\Repository;

use App\Entity\UrlData;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Class UrlDataRepository.
 *
 * This repository is responsible for managing UrlData entities.
 */
class UrlDataRepository extends ServiceEntityRepository
{
    public const PAGINATOR_ITEMS_PER_PAGE = 10;

    /**
     * Constructor.
     *
     * @param ManagerRegistry $registry The manager registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UrlData::class);
    }

    /**
     * Counts the visits for each URL.
     *
     * @return array An array containing the visit count, short name, and long name of each URL
     */
    public function countVisits(): array
    {
        $queryBuilder = $this->getOrCreateQueryBuilder();
        $queryBuilder->select('count(urlData.id) as visits, url.shortName, url.longName');
        $queryBuilder->leftJoin('urlData.url', 'url');
        $queryBuilder->groupBy('urlData.url', 'url.longName', 'url.shortName');
        $queryBuilder->orderBy('visits', 'DESC');

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * Deletes all URL visits for a given URL ID.
     *
     * @param int $id The ID of the URL
     */
    public function deleteUrlVisits(int $id): void
    {
        $queryBuilder = $this->createQueryBuilder('urlData');
        $queryBuilder
            ->delete()
            ->where('urlData.url = :id')
            ->setParameter('id', $id);

        $query = $queryBuilder->getQuery();
        $query->execute();
    }

    /**
     * Saves a UrlData entity.
     *
     * @param UrlData $urlData The UrlData entity to save
     */
    public function save(UrlData $urlData): void
    {
        $this->_em->persist($urlData);
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
        return $queryBuilder ?? $this->createQueryBuilder('urlData');
    }
}
