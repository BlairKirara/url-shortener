<?php

/**
 * Functional tests for SecurityController.
 */

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use App\Controller\SecurityController;

/**
 * Class SecurityControllerTest.
 */
class SecurityControllerTest extends WebTestCase
{
    /**
     * Tests that the login page renders with expected variables.
     */
    public function testLoginPageRendersWithExpectedVariables(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/login');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('form');
        $this->assertSelectorExists('input[name="email"]');
    }

    /**
     * Tests that the logout route is intercepted by the firewall.
     */
    public function testLogoutRouteIsIntercepted(): void
    {
        $client = static::createClient();
        $client->request('GET', '/logout');
        $this->assertNotEquals(200, $client->getResponse()->getStatusCode());
    }

    /**
     * Tests that logout throws a LogicException as expected.
     */
    public function testLogoutThrowsLogicException(): void
    {
        $this->expectException(\LogicException::class);
        $controller = new SecurityController();
        $controller->logout();
    }
}
