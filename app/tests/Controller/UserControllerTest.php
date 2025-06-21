<?php

/**
 * User controller functional test.
 */

namespace App\Tests\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/**
 * Class UserControllerTest.
 */
class UserControllerTest extends WebTestCase
{
    /**
     * Symfony client.
     */
    private KernelBrowser $client;

    /**
     * User repository.
     */
    private UserRepository $userRepository;

    /**
     * Password hasher.
     */
    private UserPasswordHasherInterface $passwordHasher;

    /**
     * Doctrine entity manager.
     */
    private EntityManagerInterface $entityManager;

    /**
     * Set up test environment.
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
     * Test index page as admin.
     */
    public function testIndexAsAdmin(): void
    {
        $this->loginAsAdmin();
        $this->client->request('GET', '/user');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('table');
    }

    /**
     * Test index page as regular user (forbidden).
     */
    public function testIndexAsUserForbidden(): void
    {
        $this->loginAsUser();
        $this->client->request('GET', '/user');
        $this->assertResponseStatusCodeSame(403);
    }

    /**
     * Test show user details.
     */
    public function testShowUser(): void
    {
        $admin = $this->loginAsAdmin();
        $this->client->request('GET', '/user/'.$admin->getId());
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('body', $admin->getEmail());
    }

    /**
     * Test edit password form renders.
     */
    public function testEditPasswordFormRenders(): void
    {
        $admin = $this->loginAsAdmin();
        $this->client->request('GET', '/user/'.$admin->getId().'/edit/password');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('form');
    }

    /**
     * Test edit password form submit.
     */
    public function testEditPasswordFormSubmit(): void
    {
        $admin = $this->loginAsAdmin();
        $crawler = $this->client->request('GET', '/user/'.$admin->getId().'/edit/password');
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
     * Test edit email form renders.
     */
    public function testEditEmailFormRenders(): void
    {
        $admin = $this->loginAsAdmin();
        $this->client->request('GET', '/user/'.$admin->getId().'/edit/email');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('form');
    }

    /**
     * Test edit email form submit.
     */
    public function testEditEmailFormSubmit(): void
    {
        $admin = $this->loginAsAdmin();
        $crawler = $this->client->request('GET', '/user/'.$admin->getId().'/edit/email');
        $this->assertResponseIsSuccessful();

        $form = $crawler->filter('form')->form([
            'user_email[email]' => 'admin2@example.com',
        ]);
        $this->client->submit($form);

        $this->assertResponseRedirects('/');
        $this->client->followRedirect();
        $this->assertSelectorExists('.alert-success');
    }

    /**
     * Log in as admin user.
     *
     * @return User The admin user
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
     * @return User The regular user
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
}
