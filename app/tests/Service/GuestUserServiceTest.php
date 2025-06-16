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
     *
     * @var GuestUserRepository
     */
    private GuestUserRepository $guestUserRepository;

    /**
     * Guest user service.
     *
     * @var GuestUserService
     */
    private GuestUserService $guestUserService;

    /**
     * Set up test environment.
     *
     * @return void
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
     *
     * @return void
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
     *
     * @return void
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
     *
     * @return void
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
     * Clean up after tests.
     *
     * @return void
     */
    protected function tearDown(): void
    {
        unset(
            $this->guestUserRepository,
            $this->guestUserService
        );
    }
}