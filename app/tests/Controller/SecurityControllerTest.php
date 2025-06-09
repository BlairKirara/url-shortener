<?php

namespace App\Tests\Controller;

use App\Controller\SecurityController;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityControllerTest extends TestCase
{
    public function testLoginReturnsResponseWithExpectedTemplateVariables(): void
    {
        $lastUsername = 'testuser';
        $error = $this->createMock(AuthenticationException::class);

        $authUtilsMock = $this->createMock(AuthenticationUtils::class);
        $authUtilsMock->method('getLastAuthenticationError')->willReturn($error);
        $authUtilsMock->method('getLastUsername')->willReturn($lastUsername);

        $controller = $this->getMockBuilder(SecurityController::class)
            ->onlyMethods(['render'])
            ->getMock();

        $controller->expects($this->once())
            ->method('render')
            ->with(
                'security/login.html.twig',
                $this->callback(function ($context) use ($lastUsername, $error) {
                    return $context['last_username'] === $lastUsername &&
                        $context['error'] === $error;
                })
            )
            ->willReturn(new Response('Mocked login page'));

        $response = $controller->login($authUtilsMock);

        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals('Mocked login page', $response->getContent());
    }

    public function testLogoutThrowsLogicException(): void
    {
        $controller = new SecurityController();

        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('This method can be blank - it will be intercepted by the logout key on your firewall.');

        $controller->logout();
    }
}
