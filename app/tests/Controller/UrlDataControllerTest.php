<?php

/**
 * Class UrlDataControllerTest.
 *
 * Functional tests for UrlDataController.
 */

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Class UrlDataControllerTest.
 * Tests for URL visits data controller.
 */
class UrlDataControllerTest extends WebTestCase
{
    /**
     * Tests that the visits count page loads successfully.
     */
    public function testVisitsCountPageLoadsSuccessfully(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/visits');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('body');
    }

    /**
     * Tests that the visits count page loads with a page parameter.
     */
    public function testVisitsCountWithPageParameter(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/visits?page=2');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('body');
    }
}
