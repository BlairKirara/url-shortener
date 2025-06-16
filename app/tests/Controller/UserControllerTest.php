<?php

/**
 * Class UserControllerTest.
 *
 * Functional tests for UserController.
 */

namespace App\Tests\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Class UserControllerTest.
 */
class UserControllerTest extends WebTestCase
{
    /**
     * Symfony client.
     *
     * @var \Symfony\Bundle\FrameworkBundle\KernelBrowser
     */
    private $client;

    /**
     * User repository.
     *
     * @var UserRepository
     */
    private $userRepository;

    /**
     * Password hasher.
     *
     * @var \Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface
     */
    private $passwordHasher;

    /**
     * Doctrine entity manager.
     *
     * @var \Doctrine\ORM\EntityManagerInterface
     */
    private $entityManager;

    /**
     * Set up test environment.
     *
     * @return void
     */
    protected function setUp(): void
    {
        $this->client = static::createClient();
        $container = static::getContainer();
        $this->userRepository = $container->get(UserRepository::class);
        $this->passwordHasher = $container->get('security.user_password_hasher');
        $this->entityManager = $container->get('doctrine')->getManager();
    }

    /**
     * Log in as admin user.
     *
     * @return User the admin user
     */
    private function loginAsAdmin(): User
    {
        $admin = $this->userRepository->findOneBy(['email' => 'admin@example.com']);
        if (!$admin) {
            $admin = new User();
            $admin->setEmail('admin@example.com');
            $admin->setRoles(['ROLE_ADMIN']);
            $admin->setPassword($this->passwordHasher->hashPassword($admin, 'adminpass'));
            $this->entityManager->persist($admin);
            $this->entityManager->flush();
        }
        $this->client->loginUser($admin);
        return $admin;
    }

    /**
     * Log in as regular user.
     *
     * @return User the regular user
     */
    private function loginAsUser(): User
    {
        $user = $this->userRepository->findOneBy(['email' => 'user@example.com']);
        if (!$user) {
            $user = new User();
            $user->setEmail('user@example.com');
            $user->setRoles(['ROLE_USER']);
            $user->setPassword($this->passwordHasher->hashPassword($user, 'userpass'));
            $this->entityManager->persist($user);
            $this->entityManager->flush();
        }
        $this->client->loginUser($user);
        return $user;
    }

    /**
     * Test user index page as admin.
     *
     * @return void
     */
    public function testIndexAsAdmin(): void
    {
        $this->loginAsAdmin();
        $this->client->request('GET', '/user');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('table');
    }

    /**
     * Test user index page as regular user (should be forbidden).
     *
     * @return void
     */
    public function testIndexAsUserForbidden(): void
    {
        $this->loginAsUser();
        $this->client->request('GET', '/user');
        $this->assertResponseStatusCodeSame(403);
    }

    /**
     * Test showing user details.
     *
     * @return void
     */
    public function testShowUser(): void
    {
        $admin = $this->loginAsAdmin();
        $this->client->request('GET', '/user/' . $admin->getId());
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('body', $admin->getEmail());
    }

    /**
     * Test rendering the edit password form.
     *
     * @return void
     */
    public function testEditPasswordFormRenders(): void
    {
        $admin = $this->loginAsAdmin();
        $this->client->request('GET', '/user/' . $admin->getId() . '/edit/password');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('form');
    }

    /**
     * Test submitting the edit password form.
     *
     * @return void
     */
    public function testEditPasswordFormSubmit(): void
    {
        $admin = $this->loginAsAdmin();
        $crawler = $this->client->request('GET', '/user/' . $admin->getId() . '/edit/password');
        $this->assertResponseIsSuccessful();

        $form = $crawler->filter('form')->form([
            'user_password[password][first]' => 'newpass123',
            'user_password[password][second]' => 'newpass123',
        ]);
        $this->client->submit($form);

        $this->assertResponseRedirects('/');
        $this->client->followRedirect();
        $this->assertSelectorExists('.alert-success');
    }

    /**
     * Test rendering the edit email form.
     *
     * @return void
     */
    public function testEditEmailFormRenders(): void
    {
        $admin = $this->loginAsAdmin();
        $this->client->request('GET', '/user/' . $admin->getId() . '/edit/email');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('form');
    }

    /**
     * Test submitting the edit email form.
     *
     * @return void
     */
    public function testEditEmailFormSubmit(): void
    {
        $admin = $this->loginAsAdmin();
        $crawler = $this->client->request('GET', '/user/' . $admin->getId() . '/edit/email');
        $this->assertResponseIsSuccessful();

        $form = $crawler->filter('form')->form([
            'user_email[email]' => 'admin2@example.com',
        ]);
        $this->client->submit($form);

        $this->assertResponseRedirects('/');
        $this->client->followRedirect();
        $this->assertSelectorExists('.alert-success');
    }
}