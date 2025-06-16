<?php

namespace App\Tests\Service;

use App\Entity\GuestUser;
use App\Repository\GuestUserRepository;
use App\Service\GuestUserService;
use PHPUnit\Framework\TestCase;

class GuestUserServiceTest extends TestCase
{
    private GuestUserRepository $guestUserRepository;
    private GuestUserService $guestUserService;

    protected function setUp(): void
    {
        $this->guestUserRepository = $this->getMockBuilder(GuestUserRepository::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['save', 'countEmailUse'])
            ->addMethods(['findOneByEmail'])
            ->getMock();
            
        $this->guestUserService = new GuestUserService($this->guestUserRepository);
    }

    public function testSave(): void
    {
        $guestUser = new GuestUser();
        $guestUser->setEmail('test@example.com');

        // Expect findOneByEmail to return null (no existing user)
        $this->guestUserRepository
            ->expects($this->once())
            ->method('findOneByEmail')
            ->with('test@example.com')
            ->willReturn(null);

        // Expect save to be called once
        $this->guestUserRepository
            ->expects($this->once())
            ->method('save')
            ->with($this->equalTo($guestUser));

        $this->guestUserService->save($guestUser);
    }

    public function testSaveWithExistingEmail(): void
    {
        $guestUser = new GuestUser();
        $guestUser->setEmail('existing@example.com');

        // Simulate existing user with same email
        $existingUser = new GuestUser();
        $existingUser->setEmail('existing@example.com');

        $this->guestUserRepository
            ->expects($this->once())
            ->method('findOneByEmail')
            ->with('existing@example.com')
            ->willReturn($existingUser);

        // Save should not be called when email exists
        $this->guestUserRepository
            ->expects($this->never())
            ->method('save');

        $this->guestUserService->save($guestUser);
    }

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

    protected function tearDown(): void
    {
        unset(
            $this->guestUserRepository,
            $this->guestUserService
        );
    }



}