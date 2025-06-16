<?php

/**
 * Class UserServiceTest.
 *
 * This class provides unit tests for UserService.
 */

namespace App\Tests\Service;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Service\UserService;
use Doctrine\ORM\QueryBuilder;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/**
 * Class UserServiceTest.
 */
class UserServiceTest extends TestCase
{
    /**
     * User repository.
     *
     * @var UserRepository
     */
    private UserRepository $userRepository;

    /**
     * Paginator.
     *
     * @var PaginatorInterface
     */
    private PaginatorInterface $paginator;

    /**
     * Password hasher.
     *
     * @var UserPasswordHasherInterface
     */
    private UserPasswordHasherInterface $passwordHasher;

    /**
     * Pagination.
     *
     * @var PaginationInterface
     */
    private PaginationInterface $pagination;

    /**
     * User service.
     *
     * @var UserService
     */
    private UserService $userService;

    /**
     * Set up test environment.
     *
     * @return void
     */
    protected function setUp(): void
    {
        $this->userRepository = $this->createMock(UserRepository::class);
        $this->paginator = $this->createMock(PaginatorInterface::class);
        $this->passwordHasher = $this->createMock(UserPasswordHasherInterface::class);
        $this->pagination = $this->createMock(PaginationInterface::class);

        $this->userService = new UserService(
            $this->userRepository,
            $this->paginator,
            $this->passwordHasher
        );
    }

    /**
     * Tests retrieving a paginated list of users.
     *
     * @return void
     */
    public function testGetPaginatedList(): void
    {
        $page = 1;
        $queryBuilder = $this->createMock(QueryBuilder::class);

        $this->userRepository->expects($this->once())
            ->method('queryAll')
            ->willReturn($queryBuilder);

        $this->paginator->expects($this->once())
            ->method('paginate')
            ->with(
                $queryBuilder,
                $page,
                UserRepository::PAGINATOR_ITEMS_PER_PAGE
            )
            ->willReturn($this->pagination);

        $result = $this->userService->getPaginatedList($page);

        $this->assertSame($this->pagination, $result);
    }

    /**
     * Tests saving a new user.
     *
     * @return void
     */
    public function testSaveNewUser(): void
    {
        $plainPassword = 'password123';
        $hashedPassword = 'hashed_password_123';

        $user = $this->getMockBuilder(User::class)
            ->onlyMethods(['getId'])
            ->getMock();
        $user->method('getId')->willReturn(null);
        $user->setEmail('test@example.com');
        $user->setPassword($plainPassword);

        $this->passwordHasher->expects($this->once())
            ->method('hashPassword')
            ->with($user, $plainPassword)
            ->willReturn($hashedPassword);

        $this->userRepository->expects($this->once())
            ->method('save')
            ->with($user);

        $this->userService->save($user);

        $this->assertEquals($hashedPassword, $user->getPassword());
        $this->assertEquals(['ROLE_USER'], $user->getRoles());
    }

    /**
     * Tests saving an existing user.
     *
     * @return void
     */
    public function testSaveExistingUser(): void
    {
        $userId = 1;
        $originalPassword = 'hashed_password_existing';
        $roles = ['ROLE_USER', 'ROLE_ADMIN'];

        $user = $this->getMockBuilder(User::class)
            ->onlyMethods(['getId'])
            ->getMock();
        $user->method('getId')->willReturn($userId);
        $user->setEmail('existing@example.com');
        $user->setPassword($originalPassword);
        $user->setRoles($roles);

        $this->passwordHasher->expects($this->never())
            ->method('hashPassword');

        $this->userRepository->expects($this->once())
            ->method('save')
            ->with($user);

        $this->userService->save($user);

        $this->assertEquals($originalPassword, $user->getPassword());
        $this->assertEquals($roles, $user->getRoles());
    }

    /**
     * Clean up after tests.
     *
     * @return void
     */
    protected function tearDown(): void
    {
        unset(
            $this->userRepository,
            $this->paginator,
            $this->passwordHasher,
            $this->pagination,
            $this->userService
        );
    }
}