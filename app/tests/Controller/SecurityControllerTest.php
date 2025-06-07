<?php

namespace App\Tests\Controller;

use App\Controller\SecurityController;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\HttpKernel\Exception\LogicException;

class SecurityControllerTest extends TestCase
{
    public function testLoginReturnsResponseWithExpectedTemplateVariables(): void
    {
        $lastUsername = 'testuser';
        $error = new \Exception('Invalid credentials');

        // Mock AuthenticationUtils
        $authUtilsMock = $this->createMock(AuthenticationUtils::class);
        $authUtilsMock->method('getLastAuthenticationError')->willReturn($error);
        $authUtilsMock->method('getLastUsername')->willReturn($lastUsername);

        $controller = new SecurityController();

        // Because render() is a method from AbstractController and is final,
        // we can’t easily mock it without a functional test.
        // Instead, call login() and check Response instance and content type.
        $response = $controller->login($authUtilsMock);

        $this->assertInstanceOf(Response::class, $response);

        // We can check that the response content contains the last username and error message strings,
        // assuming the template renders those somewhere. This is a minimal check without full functional testing.
        $content = $response->getContent();

        $this->assertStringContainsString($lastUsername, $content);
        $this->assertStringContainsString('Invalid credentials', $content);
    }

    public function testLogoutThrowsLogicException(): void
    {
        $controller = new SecurityController();

        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('This method can be blank - it will be intercepted by the logout key on your firewall.');

        $controller->logout();
    }
}
