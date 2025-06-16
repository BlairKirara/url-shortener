<?php

/**
 * User service test.
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
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;

/**
 * Class UserServiceTest.
 *
 * Provides tests for UserService.
 */
class UserServiceTest extends TestCase
{
    private UserRepository $userRepository;
    private PaginatorInterface $paginator;
    private UserPasswordHasherInterface $passwordHasher;
    private PaginationInterface $pagination;
    private UserService $userService;

    /**
     * Constructor.
     *
     * Sets up the test environment.
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
            ->with($queryBuilder, $page, UserRepository::PAGINATOR_ITEMS_PER_PAGE)
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
     * Tests saving a new user with an empty password.
     *
     * @return void
     */
    public function testSaveNewUserWithEmptyPassword(): void
    {
        $user = $this->getMockBuilder(User::class)
            ->onlyMethods(['getId'])
            ->getMock();
        $user->method('getId')->willReturn(null);
        $user->setEmail('nullpass@example.com');
        $user->setPassword('');

        $this->passwordHasher->expects($this->once())
            ->method('hashPassword')
            ->with($user, '')
            ->willReturn('');

        $this->userRepository->expects($this->once())
            ->method('save')
            ->with($user);

        $this->userService->save($user);

        $this->assertEquals('', $user->getPassword());
        $this->assertEquals(['ROLE_USER'], $user->getRoles());
    }

    /**
     * Tests saving a new user with an empty email.
     *
     * @return void
     */
    public function testSaveNewUserWithEmptyEmail(): void
    {
        $user = $this->getMockBuilder(User::class)
            ->onlyMethods(['getId'])
            ->getMock();
        $user->method('getId')->willReturn(null);
        $user->setEmail('');
        $user->setPassword('somepassword');

        $this->passwordHasher->expects($this->once())
            ->method('hashPassword')
            ->with($user, 'somepassword')
            ->willReturn('hashed_somepassword');

        $this->userRepository->expects($this->once())
            ->method('save')
            ->with($user);

        $this->userService->save($user);

        $this->assertEquals('hashed_somepassword', $user->getPassword());
        $this->assertEquals(['ROLE_USER'], $user->getRoles());
    }

    /**
     * Tests saving a new user with a custom role.
     *
     * @return void
     */
    public function testSaveNewUserWithCustomRole(): void
    {
        $user = $this->getMockBuilder(User::class)
            ->onlyMethods(['getId'])
            ->getMock();
        $user->method('getId')->willReturn(null);
        $user->setEmail('customrole@example.com');
        $user->setPassword('customrolepass');
        $user->setRoles(['ROLE_ADMIN']);

        $this->passwordHasher->expects($this->once())
            ->method('hashPassword')
            ->with($user, 'customrolepass')
            ->willReturn('hashed_customrolepass');

        $this->userRepository->expects($this->once())
            ->method('save')
            ->with($user);

        $this->userService->save($user);

        $this->assertEquals('hashed_customrolepass', $user->getPassword());
        $this->assertEquals(['ROLE_USER'], $user->getRoles());
    }

    /**
     * Cleans up after tests.
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

    /**
     * Tests upgrading a user's password.
     *
     * @return void
     */
    public function testUpgradePassword(): void
    {
        $userRepository = $this->getMockBuilder(UserRepository::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['save'])
            ->getMock();

        $entityManager = $this->createMock(\Doctrine\ORM\EntityManager::class);
        $reflection = new \ReflectionProperty(UserRepository::class, '_em');
        $reflection->setAccessible(true);
        $reflection->setValue($userRepository, $entityManager);

        $user = new User();
        $user->setEmail('test@example.com');
        $user->setPassword('old-password');

        $userRepository->expects($this->once())
            ->method('save')
            ->with($user);

        $userRepository->upgradePassword($user, 'new-hashed-password');

        $this->assertEquals('new-hashed-password', $user->getPassword());
    }

    /**
     * Tests upgrading password for unsupported user.
     *
     * @return void
     */
    public function testUpgradePasswordUnsupportedUser(): void
    {
        $userRepository = $this->getMockBuilder(UserRepository::class)
            ->disableOriginalConstructor()
            ->getMock();

        $unsupportedUser = $this->createMock(PasswordAuthenticatedUserInterface::class);

        $this->expectException(UnsupportedUserException::class);

        $method = new \ReflectionMethod(UserRepository::class, 'upgradePassword');
        $method->setAccessible(true);
        $method->invoke($userRepository, $unsupportedUser, 'new-password');
    }

    /**
     * Tests getOrCreateQueryBuilder with null parameter.
     *
     * @return void
     */
    public function testGetOrCreateQueryBuilderWithNullParameter(): void
    {
        $managerRegistry = $this->createMock(\Doctrine\Persistence\ManagerRegistry::class);
        $entityManager = $this->createMock(\Doctrine\ORM\EntityManager::class);
        $queryBuilder = $this->createMock(QueryBuilder::class);

        $managerRegistry->expects($this->any())
            ->method('getManagerForClass')
            ->willReturn($entityManager);

        $repository = $this->getMockBuilder(UserRepository::class)
            ->setConstructorArgs([$managerRegistry])
            ->onlyMethods(['createQueryBuilder'])
            ->getMock();

        $repository->expects($this->once())
            ->method('createQueryBuilder')
            ->with('user')
            ->willReturn($queryBuilder);

        $reflectionMethod = new \ReflectionMethod(UserRepository::class, 'getOrCreateQueryBuilder');
        $reflectionMethod->setAccessible(true);

        $result = $reflectionMethod->invoke($repository, null);

        $this->assertSame($queryBuilder, $result);
    }

    /**
     * Tests getOrCreateQueryBuilder with existing QueryBuilder.
     *
     * @return void
     */
    public function testGetOrCreateQueryBuilderWithExistingQueryBuilder(): void
    {
        $managerRegistry = $this->createMock(\Doctrine\Persistence\ManagerRegistry::class);
        $existingQueryBuilder = $this->createMock(QueryBuilder::class);

        $repository = new UserRepository($managerRegistry);

        $reflectionMethod = new \ReflectionMethod(UserRepository::class, 'getOrCreateQueryBuilder');
        $reflectionMethod->setAccessible(true);

        $result = $reflectionMethod->invoke($repository, $existingQueryBuilder);

        $this->assertSame($existingQueryBuilder, $result);
    }

    /**
     * Tests saving a user in the repository.
     *
     * @return void
     */
    public function testRepositorySaveMethod(): void
    {
        $user = new User();
        $user->setEmail('repository-test@example.com');

        $entityManager = $this->createMock(\Doctrine\ORM\EntityManager::class);
        $managerRegistry = $this->createMock(\Doctrine\Persistence\ManagerRegistry::class);

        $managerRegistry->expects($this->any())
            ->method('getManagerForClass')
            ->willReturn($entityManager);

        $repository = new UserRepository($managerRegistry);

        $reflection = new \ReflectionProperty(UserRepository::class, '_em');
        $reflection->setAccessible(true);
        $reflection->setValue($repository, $entityManager);

        $entityManager->expects($this->once())
            ->method('persist')
            ->with($user);

        $entityManager->expects($this->once())
            ->method('flush');

        $repository->save($user);
    }

    /**
     * Tests querying all users in the repository.
     *
     * @return void
     */
    public function testRepositoryQueryAll(): void
    {
        $managerRegistry = $this->createMock(\Doctrine\Persistence\ManagerRegistry::class);
        $queryBuilder = $this->createMock(QueryBuilder::class);

        $repository = $this->getMockBuilder(UserRepository::class)
            ->setConstructorArgs([$managerRegistry])
            ->onlyMethods(['createQueryBuilder'])
            ->getMock();

        $repository->expects($this->once())
            ->method('createQueryBuilder')
            ->with('user')
            ->willReturn($queryBuilder);

        $queryBuilder->expects($this->once())
            ->method('select')
            ->with('partial user.{id, email, roles}')
            ->willReturnSelf();

        $queryBuilder->expects($this->once())
            ->method('orderBy')
            ->with('user.id', 'ASC')
            ->willReturnSelf();

        $result = $repository->queryAll();

        $this->assertSame($queryBuilder, $result);
    }
}