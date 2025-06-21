<?php

/**
 * Functional tests for RegistrationController.
 */

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Class RegistrationControllerTest.
 */
class RegistrationControllerTest extends WebTestCase
{
    /**
     * Test if the registration form renders correctly.
     */
    public function testRegisterFormRenders(): void
    {
        $client = static::createClient();
        $client->request('GET', '/register');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('form');
        $this->assertSelectorExists('input[name="user[email]"]');
        $this->assertSelectorExists('input[name="user[password][first]"]');
        $this->assertSelectorExists('input[name="user[password][second]"]');
    }

    /**
     * Test registration fails with invalid data.
     */
    public function testRegisterFailsWithInvalidData(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/register');

        $form = $crawler->filter('form')->form([
            'user[email]' => '',
            'user[password][first]' => 'short',
            'user[password][second]' => 'short',
        ]);
        $client->submit($form);

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('.form-error-message, .invalid-feedback');
    }

    /**
     * Test registration succeeds with valid data.
     */
    public function testRegisterSucceedsWithValidData(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/register');

        $form = $crawler->filter('form')->form([
            'user[email]' => 'testuser'.uniqid().'@example.com',
            'user[password][first]' => 'password123',
            'user[password][second]' => 'password123',
        ]);
        $client->submit($form);

        $this->assertResponseRedirects('/login');
        $client->followRedirect();
        $this->assertSelectorExists('.alert-success');
    }
}
