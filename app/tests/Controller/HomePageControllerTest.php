<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Functional tests for HomePageController.
 */
class HomePageControllerTest extends WebTestCase
{
    /**
     * Test that the homepage returns a 200 HTTP status code.
     */
    public function testHomepageStatusCodeIs200(): void
    {
        $client = static::createClient();
        $client->request('GET', '/');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
    }

}
