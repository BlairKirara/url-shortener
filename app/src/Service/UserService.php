<?php

namespace App\Service;

use App\Entity\User;
use App\Repository\UserRepository;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/**
 * Class UserService.
 */
class UserService implements UserServiceInterface
{
    /**
     * @var UserRepository
     */
    private UserRepository $userRepository;

    /**
     * @var PaginatorInterface
     */
    private PaginatorInterface $paginator;

    /**
     * @var UserPasswordHasherInterface
     */
    private UserPasswordHasherInterface $passwordHasher;

    /**
     * Constructor.
     *
     * @param UserRepository $userRepository
     * @param PaginatorInterface $paginator
     * @param UserPasswordHasherInterface $passwordHasher
     */
    public function __construct(UserRepository $userRepository, PaginatorInterface $paginator, UserPasswordHasherInterface $passwordHasher)
    {
        $this->userRepository = $userRepository;
        $this->paginator = $paginator;
        $this->passwordHasher = $passwordHasher;
    }

    /**
     * @param int $page
     * @return PaginationInterface
     */
    public function getPaginatedList(int $page): PaginationInterface
    {
        return $this->paginator->paginate(
            $this->userRepository->queryAll(),
            $page,
            UserRepository::PAGINATOR_ITEMS_PER_PAGE
        );
    }

    /**
     * @param User $user
     * @return void
     */
    public function save(User $user): void
    {
        if (null === $user->getId()) {
            $user->setPassword(
                $this->passwordHasher->hashPassword(
                    $user,
                    $user->getPassword()
                )
            );
            $user->setRoles(['ROLE_USER']);
        }

        $this->userRepository->save($user);
    }

    /**
     * @param string $email
     * @return User|null
     */
    public function findOneBy(string $email): ?User
    {
        return $this->userRepository->findOneBy(['email' => $email]);
    }
}
