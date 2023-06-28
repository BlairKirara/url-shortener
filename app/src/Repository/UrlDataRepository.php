<?php

namespace App\Repository;

use App\Entity\UrlData;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Class UrlDataRepository.
 */
class UrlDataRepository extends ServiceEntityRepository
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
        parent::__construct($registry, UrlData::class);
    }

    /**
     * @return array
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
     * @param int $id
     * @return void
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
     * @param UrlData $urlData
     * @return void
     */
    public function save(UrlData $urlData): void
    {
        $this->_em->persist($urlData);
        $this->_em->flush();
    }

    /**
     * @param QueryBuilder|null $queryBuilder
     * @return QueryBuilder
     */
    private function getOrCreateQueryBuilder(QueryBuilder $queryBuilder = null): QueryBuilder
    {
        return $queryBuilder ?? $this->createQueryBuilder('urlData');
    }
}
