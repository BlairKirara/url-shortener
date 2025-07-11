<?php

/**
 * Class GuestUserServiceTest.
 *
 * Unit tests for GuestUserService.
 */

namespace App\Tests\Service;

use App\Entity\GuestUser;
use App\Repository\GuestUserRepository;
use App\Service\GuestUserService;
use PHPUnit\Framework\TestCase;

/**
 * Class GuestUserServiceTest.
 *
 * This class tests the GuestUserService.
 */
class GuestUserServiceTest extends TestCase
{
    /**
     * Guest user repository mock.
     */
    private GuestUserRepository $guestUserRepository;

    /**
     * Guest user service.
     */
    private GuestUserService $guestUserService;

    /**
     * Set up test environment.
     */
    protected function setUp(): void
    {
        $this->guestUserRepository = $this->getMockBuilder(GuestUserRepository::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['save', 'countEmailUse'])
            ->addMethods(['findOneByEmail'])
            ->getMock();

        $this->guestUserService = new GuestUserService($this->guestUserRepository);
    }

    /**
     * Test saving a new guest user.
     */
    public function testSave(): void
    {
        $guestUser = new GuestUser();
        $guestUser->setEmail('test@example.com');

        $this->guestUserRepository
            ->expects($this->once())
            ->method('findOneByEmail')
            ->with('test@example.com')
            ->willReturn(null);

        $this->guestUserRepository
            ->expects($this->once())
            ->method('save')
            ->with($this->equalTo($guestUser));

        $this->guestUserService->save($guestUser);
    }

    /**
     * Test saving a guest user with an existing email.
     */
    public function testSaveWithExistingEmail(): void
    {
        $guestUser = new GuestUser();
        $guestUser->setEmail('existing@example.com');

        $existingUser = new GuestUser();
        $existingUser->setEmail('existing@example.com');

        $this->guestUserRepository
            ->expects($this->once())
            ->method('findOneByEmail')
            ->with('existing@example.com')
            ->willReturn($existingUser);

        $this->guestUserRepository
            ->expects($this->never())
            ->method('save');

        $this->guestUserService->save($guestUser);
    }

    /**
     * Test counting email usage.
     */
    public function testCountEmailUse(): void
    {
        $email = 'test@example.com';
        $expectedCount = 5;

        $this->guestUserRepository
            ->expects($this->once())
            ->method('countEmailUse')
            ->with($email)
            ->willReturn($expectedCount);

        $result = $this->guestUserService->countEmailUse($email);
        $this->assertEquals($expectedCount, $result);
    }

    /**
     * Test saving a guest user in the repository.
     */
    public function testRepositorySaveMethod(): void
    {
        $guestUser = new GuestUser();
        $guestUser->setEmail('repository-test@example.com');

        $entityManager = $this->createMock(\Doctrine\ORM\EntityManager::class);
        $managerRegistry = $this->createMock(\Doctrine\Persistence\ManagerRegistry::class);

        $managerRegistry->expects($this->any())
            ->method('getManagerForClass')
            ->willReturn($entityManager);

        $repository = new GuestUserRepository($managerRegistry);

        $reflection = new \ReflectionProperty(GuestUserRepository::class, '_em');
        $reflection->setAccessible(true);
        $reflection->setValue($repository, $entityManager);

        $entityManager->expects($this->once())
            ->method('persist')
            ->with($guestUser);

        $entityManager->expects($this->once())
            ->method('flush');

        $repository->save($guestUser);
    }

    /**
     * Clean up after tests.
     */
    protected function tearDown(): void
    {
        unset(
            $this->guestUserRepository,
            $this->guestUserService
        );
    }
}
