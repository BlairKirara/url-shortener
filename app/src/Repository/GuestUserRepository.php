<?php

/**
 * Guest user repository.
 */

namespace App\Repository;

use App\Entity\GuestUser;
use App\Entity\Url;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Class GuestUserRepository.
 *
 * This repository is responsible for managing GuestUser entities.
 */
class GuestUserRepository extends ServiceEntityRepository
{
    /**
     * Constructor.
     *
     * @param ManagerRegistry $registry The manager registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, GuestUser::class);
    }

    /**
     * Saves a GuestUser entity.
     *
     * @param GuestUser $guestUser The GuestUser entity to save
     */
    public function save(GuestUser $guestUser): void
    {
        $this->_em->persist($guestUser);
        $this->_em->flush();
    }

    /**
     * Counts the number of times an email has been used by a GuestUser within the last 24 hours.
     *
     * @param string $email The email address to count
     *
     * @return int The count of email usage
     *
     * @throws \Doctrine\ORM\NoResultException
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function countEmailUse(string $email): int
    {
        $queryBuilder = $this->getOrCreateQueryBuilder()
            ->select('count(guestUser.id)')
            ->leftJoin(Url::class, 'url', 'WITH', 'url.guestUser = guestUser')
            ->where('guestUser.email = :email')
            ->andWhere('url.createTime > :time')
            ->setParameter('email', $email)
            ->setParameter('time', new \DateTimeImmutable('-24 hours'));

        return $queryBuilder->getQuery()->getSingleScalarResult();
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
        return $queryBuilder ?? $this->createQueryBuilder('guestUser');
    }
}
