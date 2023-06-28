<?php


namespace App\Repository;

use App\Entity\GuestUser;
use App\Entity\Url;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;


class GuestUserRepository extends ServiceEntityRepository
{

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, GuestUser::class);
    }

    public function save(GuestUser $guestUser): void
    {
        $this->_em->persist($guestUser);
        $this->_em->flush();
    }


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

    private function getOrCreateQueryBuilder(QueryBuilder $queryBuilder = null): QueryBuilder
    {
        return $queryBuilder ?? $this->createQueryBuilder('guestUser');
    }
}
