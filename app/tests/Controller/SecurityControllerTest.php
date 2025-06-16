<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use App\Controller\SecurityController;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityControllerTest extends WebTestCase
{
    public function testLoginPageRendersWithExpectedVariables(): void
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/login');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('form');
        $this->assertSelectorExists('input[name="email"]');
    }

    public function testLogoutRouteIsIntercepted(): void
    {
        $client = static::createClient();

        $client->request('GET', '/logout');
        $this->assertNotEquals(200, $client->getResponse()->getStatusCode());
    }

    public function testLogoutThrowsLogicException(): void
    {
        $this->expectException(\LogicException::class);
        $controller = new SecurityController();
        $controller->logout();
    }
}