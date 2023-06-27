<?php

namespace App\Repository;

use App\Entity\UrlData;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<UrlData>
 *
 * @method UrlData|null find($id, $lockMode = null, $lockVersion = null)
 * @method UrlData|null findOneBy(array $criteria, array $orderBy = null)
 * @method UrlData[]    findAll()
 * @method UrlData[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UrlDataRepository extends ServiceEntityRepository
{

    /**
     * Items per page.
     *
     * Use constants to define configuration options that rarely change instead
     * of specifying them in configuration files.
     * See https://symfony.com/doc/current/best_practices.html#configuration
     *
     * @constant int
     */
    public const PAGINATOR_ITEMS_PER_PAGE = 10;
    // ...

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UrlData::class);
    }

    public function save(UrlData $urlData): void
    {
        $this->_em->persist($urlData);
        $this->_em->flush();
    }

    public function remove(UrlData $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * Query all records.
     *
     * @return QueryBuilder Query builder
     */
    public function queryAll(): QueryBuilder
    {
        return $this->getOrCreateQueryBuilder()
            ->orderBy('url_data.visit_time', 'DESC');
    }

    /**
     * Get or create new query builder.
     *
     * @param QueryBuilder|null $queryBuilder Query builder
     *
     * @return QueryBuilder Query builder
     */
    private function getOrCreateQueryBuilder(QueryBuilder $queryBuilder = null): QueryBuilder
    {
        return $queryBuilder ?? $this->createQueryBuilder('url_data');
    }

}
