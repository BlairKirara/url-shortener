<?php
/**
 * Url repository.
 */

namespace App\Repository;

use App\Entity\Tag;
use App\Entity\Url;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Class UrlRepository.
 *
 * This repository is responsible for managing Url entities.
 */
class UrlRepository extends ServiceEntityRepository
{
    public const PAGINATOR_ITEMS_PER_PAGE = 10;

    /**
     * Constructor.
     *
     * @param ManagerRegistry $registry The manager registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Url::class);
    }

    /**
     * Retrieves a QueryBuilder instance with applied filters.
     *
     * @param array $filters The filters to apply
     *
     * @return QueryBuilder The QueryBuilder instance
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
     * Checks and removes the block status of URLs.
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
     * Retrieves a QueryBuilder instance filtered by author and other filters.
     *
     * @param UserInterface|null $user    The author of the URLs
     * @param array              $filters The additional filters to apply
     *
     * @return QueryBuilder The QueryBuilder instance
     */
    public function queryByAuthor(?UserInterface $user, array $filters = []): QueryBuilder
    {
        $queryBuilder = $this->queryAll($filters);

        $queryBuilder->andWhere('url.users = :users')
            ->setParameter('users', $user);

        return $queryBuilder;
    }

    /**
     * Saves a Url entity.
     *
     * @param Url $url The Url entity to save
     */
    public function save(Url $url): void
    {
        $this->_em->persist($url);
        $this->_em->flush();
    }

    /**
     * Deletes a Url entity.
     *
     * @param Url $url The Url entity to delete
     */
    public function delete(Url $url): void
    {
        $this->_em->remove($url);
        $this->_em->flush();
    }

    /**
     * Applies additional filters to the QueryBuilder.
     *
     * @param QueryBuilder $queryBuilder The QueryBuilder instance
     * @param array        $filters      The filters to apply
     *
     * @return QueryBuilder The modified QueryBuilder instance
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
     * Returns a QueryBuilder instance or creates a new one.
     *
     * @param QueryBuilder|null $queryBuilder The optional QueryBuilder instance
     *
     * @return QueryBuilder The QueryBuilder instance
     */
    private function getOrCreateQueryBuilder(QueryBuilder $queryBuilder = null): QueryBuilder
    {
        return $queryBuilder ?? $this->createQueryBuilder('url');
    }
}
