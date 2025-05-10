<?php

/**
 * User repository.
 */

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;
use Doctrine\ORM\QueryBuilder;

/**
 * Class UserRepository.
 *
 * This repository is responsible for managing User entities.
 */
class UserRepository extends ServiceEntityRepository implements PasswordUpgraderInterface
{
    public const PAGINATOR_ITEMS_PER_PAGE = 10;

    /**
     * Constructor.
     *
     * @param ManagerRegistry $registry The manager registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    /**
     * Retrieves a QueryBuilder instance for fetching all users.
     *
     * @return QueryBuilder The QueryBuilder instance
     */
    public function queryAll(): QueryBuilder
    {
        return $this->getOrCreateQueryBuilder()
            ->select('partial user.{id, email, roles}')
            ->orderBy('user.id', 'ASC');
    }

    /**
     * Upgrades the user's password.
     *
     * @param PasswordAuthenticatedUserInterface $user              The user to upgrade the password for
     * @param string                             $newHashedPassword The new hashed password
     *
     * @throws UnsupportedUserException If the user is not an instance of User
     */
    public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', $user::class));
        }

        $user->setPassword($newHashedPassword);

        $this->save($user);
    }

    /**
     * Saves a User entity.
     *
     * @param User $user The User entity to save
     */
    public function save(User $user): void
    {
        $this->_em->persist($user);
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
        return $queryBuilder ?? $this->createQueryBuilder('user');
    }
}
