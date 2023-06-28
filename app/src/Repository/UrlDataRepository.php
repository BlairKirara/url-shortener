<?php

namespace App\Repository;

use App\Entity\UrlData;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;


class UrlDataRepository extends ServiceEntityRepository
{
    public const PAGINATOR_ITEMS_PER_PAGE = 10;


    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UrlData::class);
    }


    public function countAllVisitsForUrl(): array
    {
        $queryBuilder = $this->getOrCreateQueryBuilder()
            ->select('count(urlData.id) as visits, url.longName, url.shortName, MAX(urlData.visitTime) as latestVisitTime')
            ->leftJoin('urlData.url', 'url')
            ->groupBy('urlData.url', 'url.longName', 'url.shortName')
            ->orderBy('visits', 'DESC')
            ->addOrderBy('latestVisitTime', 'DESC');

        return $queryBuilder->getQuery()->getResult();
    }


    public function save(UrlData $urlData): void
    {
        $this->_em->persist($urlData);
        $this->_em->flush();
    }


    public function deleteAllVisitsForUrl(int $id): void
    {
        $this->createQueryBuilder('urlData')
            ->delete()
            ->where('urlData.url = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->execute();
    }


    private function getOrCreateQueryBuilder(QueryBuilder $queryBuilder = null): QueryBuilder
    {
        return $queryBuilder ?? $this->createQueryBuilder('urlData');
    }
}
