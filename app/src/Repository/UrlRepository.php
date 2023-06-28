<?php

namespace App\Repository;

use App\Entity\Tag;
use App\Entity\Url;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Class UrlRepository.
 */
class UrlRepository extends ServiceEntityRepository
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
        parent::__construct($registry, Url::class);
    }

    /**
     * @param array $filters
     * @return QueryBuilder
     */
    public function queryAll(array $filters): QueryBuilder
    {
        $this->checkBlockTime();

        $queryBuilder = $this->getOrCreateQueryBuilder()
            ->select(
                'partial url.{id, longName, shortName, createTime, isBlocked, blockTime}',
                'partial tags.{id, name}',
            )
            ->leftJoin('url.tags', 'tags')
            ->orderBy('url.createTime', 'DESC');

        return $this->applyFiltersToList($queryBuilder, $filters);
    }

    /**
     * @return void
     */
    public function checkBlockTime(): void
    {
        $queryBuilder = $this->getOrCreateQueryBuilder()
            ->update(Url::class, 'url')
            ->set('url.isBlocked', 'false')
            ->set('url.blockTime', 'null')
            ->where('url.blockTime < :now')
            ->setParameter('now', new \DateTime('now'));

        $queryBuilder->getQuery()->execute();
    }

    /**
     * @param UserInterface|null $user
     * @param array $filters
     * @return QueryBuilder
     */
    public function queryByAuthor(?UserInterface $user, array $filters = []): QueryBuilder
    {
        $queryBuilder = $this->queryAll($filters);

        $queryBuilder->andWhere('url.users = :users')
            ->setParameter('users', $user);

        return $queryBuilder;
    }

    /**
     * @param Url $url
     * @return void
     */
    public function save(Url $url): void
    {
        $this->_em->persist($url);
        $this->_em->flush();
    }

    /**
     * @param Url $url
     * @return void
     */
    public function delete(Url $url): void
    {
        $this->_em->remove($url);
        $this->_em->flush();
    }

    /**
     * @param QueryBuilder $queryBuilder
     * @param array $filters
     * @return QueryBuilder
     */
    private function applyFiltersToList(QueryBuilder $queryBuilder, array $filters = []): QueryBuilder
    {
        if (isset($filters['tag']) && $filters['tag'] instanceof Tag) {
            $queryBuilder->andWhere('tags IN (:tag)')
                ->setParameter('tag', $filters['tag']);
        }

        return $queryBuilder;
    }

    /**
     * @param QueryBuilder|null $queryBuilder
     * @return QueryBuilder
     */
    private function getOrCreateQueryBuilder(QueryBuilder $queryBuilder = null): QueryBuilder
    {
        return $queryBuilder ?? $this->createQueryBuilder('url');
    }
}
