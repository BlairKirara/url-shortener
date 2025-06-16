<?php

/**
 * Functional tests for TagController.
 */

namespace App\Tests\Controller;

use App\Entity\Tag;
use App\Entity\User;
use App\Repository\TagRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Class TagControllerTest.
 */
class TagControllerTest extends WebTestCase
{
    /**
     * Ensures that an admin user exists.
     *
     * @return User The admin user entity
     */
    private function ensureAdminUserExists(): User
    {
        $userRepository = static::getContainer()->get(UserRepository::class);
        $user = $userRepository->findOneBy(['email' => 'admin@example.com']);
        if (!$user) {
            $user = new User();
            $user->setEmail('admin@example.com');
            $user->setRoles(['ROLE_ADMIN']);
            $passwordHasher = static::getContainer()->get('security.user_password_hasher');
            $hashedPassword = $passwordHasher->hashPassword($user, 'adminpass');
            $user->setPassword($hashedPassword);
            $em = static::getContainer()->get('doctrine')->getManager();
            $em->persist($user);
            $em->flush();
        }
        return $user;
    }

    /**
     * Logs in as admin user.
     *
     * @param $client The test client
     *
     * @return void
     */
    private function loginAsAdmin($client): void
    {
        $testAdmin = $this->ensureAdminUserExists();
        $client->loginUser($testAdmin);
    }

    /**
     * Ensures that a tag exists.
     *
     * @return Tag The tag entity
     */
    private function ensureTagExists(): Tag
    {
        $tagRepository = static::getContainer()->get(TagRepository::class);
        $tag = $tagRepository->findOneBy([]);
        if (!$tag) {
            $tag = new Tag();
            $tag->setName('TestTag');
            $em = static::getContainer()->get('doctrine')->getManager();
            $em->persist($tag);
            $em->flush();
        }
        return $tag;
    }

    /**
     * Tests that the tag index page loads.
     *
     * @return void
     */
    public function testIndexPageLoads(): void
    {
        $client = static::createClient();
        $this->ensureTagExists();
        $client->request('GET', '/tag');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('table');
    }

    /**
     * Tests creating a tag as admin.
     *
     * @return void
     */
    public function testCreateTagAsAdmin(): void
    {
        $client = static::createClient();
        $this->loginAsAdmin($client);

        $crawler = $client->request('GET', '/tag/create');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('form');

        $form = $crawler->filter('form')->form([
            'tag[name]' => 'TestTag2',
        ]);
        $client->submit($form);

        $this->assertResponseRedirects('/tag');
        $client->followRedirect();
        $this->assertSelectorTextContains('body', 'TestTag2');
    }

    /**
     * Tests showing a tag as admin.
     *
     * @return void
     */
    public function testShowTagAsAdmin(): void
    {
        $client = static::createClient();
        $this->loginAsAdmin($client);

        $tag = $this->ensureTagExists();

        $client->request('GET', '/tag/' . $tag->getId());
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('body', $tag->getName());
    }

    /**
     * Tests editing a tag as admin.
     *
     * @return void
     */
    public function testEditTagAsAdmin(): void
    {
        $client = static::createClient();
        $this->loginAsAdmin($client);

        $tag = $this->ensureTagExists();

        $crawler = $client->request('GET', '/tag/' . $tag->getId() . '/edit');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('form');

        $form = $crawler->filter('form')->form([
            'tag[name]' => 'UpdatedTag',
        ]);
        $client->submit($form);

        $this->assertResponseRedirects('/tag');
        $client->followRedirect();
        $this->assertSelectorTextContains('body', 'UpdatedTag');
    }

    /**
     * Tests deleting a tag as admin.
     *
     * @return void
     */
    public function testDeleteTagAsAdmin(): void
    {
        $client = static::createClient();
        $this->loginAsAdmin($client);

        $tag = $this->ensureTagExists();

        $crawler = $client->request('GET', '/tag/' . $tag->getId() . '/delete');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('form');

        $form = $crawler->filter('form')->form();
        $client->submit($form);

        $this->assertResponseRedirects('/tag');
    }

    /**
     * Tests that admin routes require login.
     *
     * @return void
     */
    public function testAdminRoutesRequireLogin(): void
    {
        $client = static::createClient();

        $tag = $this->ensureTagExists();
        $tagId = $tag->getId();

        $client->request('GET', '/tag/' . $tagId . '/edit');
        $this->assertResponseRedirects('/login');

        $client->request('GET', '/tag/' . $tagId . '/delete');
        $this->assertResponseRedirects('/login');

        $client->request('GET', '/tag/' . $tagId);
        $this->assertResponseRedirects('/login');
    }
}