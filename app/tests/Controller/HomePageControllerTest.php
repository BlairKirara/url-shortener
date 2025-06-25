<?php

/**
 * Functional tests for HomePageController.
 */

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Class HomePageControllerTest.
 */
class HomePageControllerTest extends WebTestCase
{
    /**
     * Tests that the homepage returns a 200 HTTP status code.
     */
    public function testHomepageStatusCodeIs200(): void
    {
        $client = static::createClient();
        $client->request('GET', '/');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
    }
}
