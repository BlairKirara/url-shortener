<?php

/**
 * Class UserServiceTest.
 *
 * Unit tests for UserService.
 */

namespace App\Tests\Service;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Service\UserService;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;

/**
 * Class UserServiceTest.
 */
class UserServiceTest extends TestCase
{
    /**
     * User repository mock.
     */
    private UserRepository $userRepository;

    /**
     * Paginator mock.
     */
    private PaginatorInterface $paginator;

    /**
     * Password hasher mock.
     */
    private UserPasswordHasherInterface $passwordHasher;

    /**
     * Pagination mock.
     */
    private PaginationInterface $pagination;

    /**
     * User service.
     */
    private UserService $userService;

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
     * Test getPaginatedList returns paginated users.
     */
    public function testGetPaginatedList(): void
    {
        $page = 1;
        $queryBuilder = $this->createMock(\Doctrine\ORM\QueryBuilder::class);

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
     * Test saving a new user hashes password and sets default role.
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
     * Test saving an existing user does not re-hash password or change roles.
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
     * Test upgradePassword with a valid User.
     */
    public function testUpgradePasswordWithValidUser(): void
    {
        $user = $this->getMockBuilder(User::class)
            ->onlyMethods(['setPassword'])
            ->getMock();
        $newHashedPassword = 'new_hashed_password';

        $user->expects($this->once())
            ->method('setPassword')
            ->with($newHashedPassword);

        $userRepository = $this->getMockBuilder(UserRepository::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['save'])
            ->getMock();

        $userRepository->expects($this->once())
            ->method('save')
            ->with($user);

        $userRepository->upgradePassword($user, $newHashedPassword);
    }

    /**
     * Test upgradePassword throws exception for unsupported user type.
     */
    public function testUpgradePasswordWithInvalidUserThrowsException(): void
    {
        $this->expectException(UnsupportedUserException::class);

        $invalidUser = $this->createMock(PasswordAuthenticatedUserInterface::class);

        $userRepository = $this->getMockBuilder(UserRepository::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['save'])
            ->getMock();

        $userRepository->upgradePassword($invalidUser, 'irrelevant');
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
