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

    /**
     * Test repository upgradePassword method
     */
    public function testUpgradePassword(): void
    {
        // Create a mock UserRepository instance but use real upgradePassword method
        $userRepository = $this->getMockBuilder(UserRepository::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['save'])
            ->getMock();

        // Set the entity manager using reflection
        $entityManager = $this->createMock(\Doctrine\ORM\EntityManager::class);
        $reflection = new \ReflectionProperty(UserRepository::class, '_em');
        $reflection->setAccessible(true);
        $reflection->setValue($userRepository, $entityManager);

        // Create a user with new password
        $user = new User();
        $user->setEmail('test@example.com');
        $user->setPassword('old-password');

        // Expect save to be called once
        $userRepository->expects($this->once())
            ->method('save')
            ->with($this->equalTo($user));

        // Call the upgradePassword method
        $userRepository->upgradePassword($user, 'new-hashed-password');

        // Verify password was updated
        $this->assertEquals('new-hashed-password', $user->getPassword());
    }

    /**
     * Test upgradePassword with unsupported user exception
     */
    public function testUpgradePasswordUnsupportedUser(): void
    {
        $userRepository = $this->getMockBuilder(UserRepository::class)
            ->disableOriginalConstructor()
            ->getMock();

        // Create a mock of PasswordAuthenticatedUserInterface that is not a User
        $unsupportedUser = $this->createMock(\Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface::class);

        // Expect exception
        $this->expectException(\Symfony\Component\Security\Core\Exception\UnsupportedUserException::class);

        // Call method with reflection to bypass the mock
        $method = new \ReflectionMethod(UserRepository::class, 'upgradePassword');
        $method->setAccessible(true);
        $method->invoke($userRepository, $unsupportedUser, 'new-password');
    }

    /**
     * Test getOrCreateQueryBuilder method with null parameter
     */
    public function testGetOrCreateQueryBuilderWithNullParameter(): void
    {
        // Setup
        $managerRegistry = $this->createMock(\Doctrine\Persistence\ManagerRegistry::class);
        $entityManager = $this->createMock(\Doctrine\ORM\EntityManager::class);
        $queryBuilder = $this->createMock(QueryBuilder::class);

        $managerRegistry->expects($this->any())
            ->method('getManagerForClass')
            ->willReturn($entityManager);

        // Create repository with mocked methods
        $repository = $this->getMockBuilder(UserRepository::class)
            ->setConstructorArgs([$managerRegistry])
            ->onlyMethods(['createQueryBuilder'])
            ->getMock();

        // Expect createQueryBuilder to be called with 'user'
        $repository->expects($this->once())
            ->method('createQueryBuilder')
            ->with('user')
            ->willReturn($queryBuilder);

        // Use reflection to access private method
        $reflectionMethod = new \ReflectionMethod(UserRepository::class, 'getOrCreateQueryBuilder');
        $reflectionMethod->setAccessible(true);

        // Call the private method with null
        $result = $reflectionMethod->invoke($repository, null);

        // Verify result is the expected query builder
        $this->assertSame($queryBuilder, $result);
    }

    /**
     * Test getOrCreateQueryBuilder method with existing query builder
     */
    public function testGetOrCreateQueryBuilderWithExistingQueryBuilder(): void
    {
        // Setup
        $managerRegistry = $this->createMock(\Doctrine\Persistence\ManagerRegistry::class);
        $existingQueryBuilder = $this->createMock(QueryBuilder::class);

        // Create repository
        $repository = new UserRepository($managerRegistry);

        // Use reflection to access private method
        $reflectionMethod = new \ReflectionMethod(UserRepository::class, 'getOrCreateQueryBuilder');
        $reflectionMethod->setAccessible(true);

        // Call the private method with existing query builder
        $result = $reflectionMethod->invoke($repository, $existingQueryBuilder);

        // Verify the same query builder is returned
        $this->assertSame($existingQueryBuilder, $result);
    }

    /**
     * Test save method directly
     */
    public function testRepositorySaveMethod(): void
    {
        // Create a user
        $user = new User();
        $user->setEmail('repository-test@example.com');

        // Create mocks
        $entityManager = $this->createMock(\Doctrine\ORM\EntityManager::class);
        $managerRegistry = $this->createMock(\Doctrine\Persistence\ManagerRegistry::class);

        $managerRegistry->expects($this->any())
            ->method('getManagerForClass')
            ->willReturn($entityManager);

        // Create repository
        $repository = new UserRepository($managerRegistry);

        // Set entity manager using reflection
        $reflection = new \ReflectionProperty(UserRepository::class, '_em');
        $reflection->setAccessible(true);
        $reflection->setValue($repository, $entityManager);

        // Expect persist and flush to be called
        $entityManager->expects($this->once())
            ->method('persist')
            ->with($this->equalTo($user));

        $entityManager->expects($this->once())
            ->method('flush');

        // Call save method
        $repository->save($user);
    }

    /**
     * Test queryAll method implementation details
     */
    public function testRepositoryQueryAll(): void
    {
        // Create mocks
        $managerRegistry = $this->createMock(\Doctrine\Persistence\ManagerRegistry::class);
        $queryBuilder = $this->createMock(QueryBuilder::class);

        // Create repository with mocked createQueryBuilder
        $repository = $this->getMockBuilder(UserRepository::class)
            ->setConstructorArgs([$managerRegistry])
            ->onlyMethods(['createQueryBuilder'])
            ->getMock();

        // Setup expectations for query building
        $repository->expects($this->once())
            ->method('createQueryBuilder')
            ->with('user')
            ->willReturn($queryBuilder);

        // Expect select to be called with the correct partial selection
        $queryBuilder->expects($this->once())
            ->method('select')
            ->with('partial user.{id, email, roles}')
            ->willReturnSelf();

        // Expect orderBy to be called with the correct parameters
        $queryBuilder->expects($this->once())
            ->method('orderBy')
            ->with('user.id', 'ASC')
            ->willReturnSelf();

        // Execute the queryAll method
        $result = $repository->queryAll();

        // Verify the query builder is returned
        $this->assertSame($queryBuilder, $result);
    }
}