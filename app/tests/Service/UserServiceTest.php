<?php

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
 *
 * Tests for the UserService.
 */
class UserServiceTest extends TestCase
{
    /**
     * User repository.
     */
    private UserRepository $userRepository;

    /**
     * Paginator.
     */
    private PaginatorInterface $paginator;

    /**
     * Password hasher.
     */
    private UserPasswordHasherInterface $passwordHasher;

    /**
     * User service.
     */
    private UserService $userService;

    /**
     * Pagination.
     */
    private PaginationInterface $pagination;

    /**
     * Set up test environment.
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
     * Test getPaginatedList method.
     */
    public function testGetPaginatedList(): void
    {
        // Given
        $page = 1;
        $queryBuilder = $this->createMock(QueryBuilder::class);

        // Expect queryAll method to be called once
        $this->userRepository->expects($this->once())
            ->method('queryAll')
            ->willReturn($queryBuilder);

        // Expect paginate method to be called once with the correct parameters
        $this->paginator->expects($this->once())
            ->method('paginate')
            ->with(
                $queryBuilder,
                $page,
                UserRepository::PAGINATOR_ITEMS_PER_PAGE
            )
            ->willReturn($this->pagination);

        // When
        $result = $this->userService->getPaginatedList($page);

        // Then
        $this->assertSame($this->pagination, $result);
    }

    /**
     * Test save method for a new user.
     */
    public function testSaveNewUser(): void
    {
        // Given
        $user = new User();
        $plainPassword = 'password123';
        $hashedPassword = 'hashed_password_123';

        $user->setEmail('test@example.com');
        $user->setPassword($plainPassword);

        // Expect getId method to return null for a new user
        $user = $this->getMockBuilder(User::class)
            ->onlyMethods(['getId'])
            ->getMock();
        $user->method('getId')
            ->willReturn(null);

        $user->setEmail('test@example.com');
        $user->setPassword($plainPassword);

        // Expect hashPassword method to be called once with the correct parameters
        $this->passwordHasher->expects($this->once())
            ->method('hashPassword')
            ->with(
                $this->equalTo($user),
                $this->equalTo($plainPassword)
            )
            ->willReturn($hashedPassword);

        // Expect setPassword method to be called with the hashed password
        // This happens inside the service

        // Expect setRoles method to be called with the default role
        // This happens inside the service

        // Expect save method to be called once with the user
        $this->userRepository->expects($this->once())
            ->method('save')
            ->with($this->equalTo($user));

        // When
        $this->userService->save($user);

        // Then
        $this->assertEquals($hashedPassword, $user->getPassword());
        $this->assertEquals(['ROLE_USER'], $user->getRoles());
    }

    /**
     * Test save method for an existing user.
     */
    public function testSaveExistingUser(): void
    {
        // Given
        $userId = 1;
        $originalPassword = 'hashed_password_existing';
        $roles = ['ROLE_USER', 'ROLE_ADMIN'];

        // Create a mock user that returns a non-null ID to simulate an existing user
        $user = $this->getMockBuilder(User::class)
            ->onlyMethods(['getId'])
            ->getMock();
        $user->method('getId')
            ->willReturn($userId);

        $user->setEmail('existing@example.com');
        $user->setPassword($originalPassword);
        $user->setRoles($roles);

        // Expect the password hasher NOT to be called for existing users
        $this->passwordHasher->expects($this->never())
            ->method('hashPassword');

        // Expect save method to be called once with the user
        $this->userRepository->expects($this->once())
            ->method('save')
            ->with($this->equalTo($user));

        // When
        $this->userService->save($user);

        // Then
        $this->assertEquals($originalPassword, $user->getPassword());
        $this->assertEquals($roles, $user->getRoles());
    }

    /**
     * Tear down test environment.
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