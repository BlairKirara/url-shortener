<?php

namespace App\Tests\Controller;

use App\Entity\Url;
use App\Entity\User;
use App\Entity\Tag;
use App\Entity\GuestUser;
use App\Repository\UrlRepository;
use App\Repository\UserRepository;
use App\Repository\TagRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class UrlControllerTest extends WebTestCase
{
    private $client;
    private $urlRepository;
    private $userRepository;
    private $tagRepository;
    private $entityManager;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $container = static::getContainer();
        $this->urlRepository = $container->get(UrlRepository::class);
        $this->userRepository = $container->get(UserRepository::class);
        $this->tagRepository = $container->get(TagRepository::class);
        $this->entityManager = $container->get('doctrine.orm.entity_manager');

        // Create test data if needed
        $this->createTestData();
    }

    private function createTestData(): void
    {
        // Check if we need to create test data
        if ($this->urlRepository->count([]) > 0) {
            return; // Data already exists
        }

        // Create admin and user with explicit flush after each
        $admin = $this->userRepository->findOneByEmail('admin@example.com');
        if (!$admin) {
            $admin = new User();
            $admin->setEmail('admin@example.com');
            $admin->setPassword('$2y$13$hK3.LXmXx1dC3eJca3S2CO3ReozDNnZQbYOlqpV/LRqzDNJxlDU4m'); // hashed 'admin'
            $admin->setRoles(['ROLE_ADMIN']);
            $this->entityManager->persist($admin);
            $this->entityManager->flush(); // Explicitly flush after creating admin
        }

        $user = $this->userRepository->findOneByEmail('user@example.com');
        if (!$user) {
            $user = new User();
            $user->setEmail('user@example.com');
            $user->setPassword('$2y$13$KuJnAI.jPEQv6q.QjmJ.xOCAjA9e2sGGAS8jPUefJhW9B5Z4tGhXa'); // hashed 'user'
            $user->setRoles(['ROLE_USER']);
            $this->entityManager->persist($user);
            $this->entityManager->flush(); // Explicitly flush after creating user
        }

        $anotherUser = $this->userRepository->findOneByEmail('another@example.com');
        if (!$anotherUser) {
            $anotherUser = new User();
            $anotherUser->setEmail('another@example.com');
            $anotherUser->setPassword('$2y$13$KuJnAI.jPEQv6q.QjmJ.xOCAjA9e2sGGAS8jPUefJhW9B5Z4tGhXa'); // hashed 'user'
            $anotherUser->setRoles(['ROLE_USER']);
            $this->entityManager->persist($anotherUser);
            $this->entityManager->flush(); // Explicitly flush after creating another user
        }

        // Re-fetch users to ensure we have the persisted versions
        $admin = $this->userRepository->findOneByEmail('admin@example.com');
        $user = $this->userRepository->findOneByEmail('user@example.com');
        $anotherUser = $this->userRepository->findOneByEmail('another@example.com');

        // Create tag
        $tag = new Tag();
        $tag->setName('test-tag');
        $this->entityManager->persist($tag);
        $this->entityManager->flush();

        // Create non-blocked URL
        $url1 = new Url();
        $url1->setLongName('https://example.com');
        $url1->setShortName('test123');
        $url1->setIsBlocked(false);
        $url1->setUsers($user);
        $url1->addTag($tag);
        $this->entityManager->persist($url1);

        // Create URL for another user
        $url2 = new Url();
        $url2->setLongName('https://another-example.com');
        $url2->setShortName('another123');
        $url2->setIsBlocked(false);
        $url2->setUsers($anotherUser);
        $this->entityManager->persist($url2);

        // Create blocked URL
        $url3 = new Url();
        $url3->setLongName('https://blocked-example.com');
        $url3->setShortName('blocked123');
        $url3->setIsBlocked(true);
        $url3->setBlockTime(new \DateTimeImmutable('+1 day'));
        $url3->setUsers($admin);
        $this->entityManager->persist($url3);

        $this->entityManager->flush();
    }

    private function loginAsAdmin(): void
    {
        $admin = $this->userRepository->findOneByEmail('admin@example.com');

        if (!$admin) {
            // Create admin if it doesn't exist
            $admin = new User();
            $admin->setEmail('admin@example.com');
            $admin->setPassword('$2y$13$hK3.LXmXx1dC3eJca3S2CO3ReozDNnZQbYOlqpV/LRqzDNJxlDU4m');
            $admin->setRoles(['ROLE_ADMIN']);
            $this->entityManager->persist($admin);
            $this->entityManager->flush();

            // Re-fetch the admin to get the persisted version
            $admin = $this->userRepository->findOneByEmail('admin@example.com');
        }

        $this->client->loginUser($admin);
    }

    private function loginAsUser(): void
    {
        $user = $this->userRepository->findOneByEmail('user@example.com');

        if (!$user) {
            // Create user if it doesn't exist
            $user = new User();
            $user->setEmail('user@example.com');
            $user->setPassword('$2y$13$KuJnAI.jPEQv6q.QjmJ.xOCAjA9e2sGGAS8jPUefJhW9B5Z4tGhXa');
            $user->setRoles(['ROLE_USER']);
            $this->entityManager->persist($user);
            $this->entityManager->flush();

            // Re-fetch the user to get the persisted version
            $user = $this->userRepository->findOneByEmail('user@example.com');
        }

        $this->client->loginUser($user);
    }

    public function testIndex(): void
    {
        $this->loginAsUser();

        $this->client->request('GET', '/url');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('.container');
    }

    public function testList(): void
    {
        $this->client->request('GET', '/url/list');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('.container');
    }

    public function testShow(): void
    {
        $url = $this->urlRepository->findOneBy(['isBlocked' => false]);
        $this->assertNotNull($url, 'No unblocked URLs in database');

        $this->client->request('GET', '/url/' . $url->getId());
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('body', $url->getLongName());
    }

    public function testRedirectToUrl(): void
    {
        // Find an unblocked URL
        $url = $this->urlRepository->findOneBy(['isBlocked' => false]);
        $this->assertNotNull($url, 'No unblocked URLs in database');

        $shortName = $url->getShortName();
        $this->client->request('GET', '/url/short/' . $shortName);

        $this->assertResponseRedirects($url->getLongName());
    }

    public function testRedirectToBlockedUrl(): void
    {
        // Create or find a blocked URL
        $url = $this->prepareBlockedUrl();
        $this->assertNotNull($url, 'No blocked URL available for testing');

        $shortName = $url->getShortName();
        $this->client->request('GET', '/url/short/' . $shortName);

        // Should redirect to list with warning flash
        $this->assertResponseRedirects('/url/list');
    }

    public function testCreate(): void
    {
        $this->loginAsUser();

        $this->client->request('GET', '/url/create');
        $this->assertResponseIsSuccessful();

        $tag = $this->tagRepository->findOneBy([]);
        $this->assertNotNull($tag, 'No tags in database');

        // Use the correct button name "Zapisz" (based on the form type's block prefix "Url")
        $this->client->submitForm('Zapisz', [
            'Url[longName]' => 'https://test.example.com',
            'Url[tags]' => $tag->getName()
        ]);

        $this->assertResponseRedirects('/url/list');
        $this->client->followRedirect();
        $this->assertSelectorExists('.alert-success');

        $url = $this->urlRepository->findOneBy(['longName' => 'https://test.example.com']);
        $this->assertNotNull($url);
    }

    public function testCreateAsGuest(): void
    {
        $this->client->request('GET', '/url/create');
        $this->assertResponseIsSuccessful();

        $tag = $this->tagRepository->findOneBy([]);
        $this->assertNotNull($tag, 'No tags in database');

        // Include email field for guest users
        $this->client->submitForm('Zapisz', [
            'Url[email]' => 'guest@example.com',
            'Url[longName]' => 'https://guest.example.com',
            'Url[tags]' => $tag->getName()
        ]);

        $this->assertResponseRedirects('/url/list');
        $this->client->followRedirect();
        $this->assertSelectorExists('.alert-success');

        $url = $this->urlRepository->findOneBy(['longName' => 'https://guest.example.com']);
        $this->assertNotNull($url);
    }

    public function testDelete(): void
    {
        $this->loginAsUser();

        $user = $this->userRepository->findOneByEmail('user@example.com');
        $url = $this->urlRepository->findOneBy(['users' => $user]);
        $this->assertNotNull($url, 'No URLs for test user');

        $urlId = $url->getId();

        // Get the crawler so we can find the form
        $crawler = $this->client->request('GET', '/url/' . $urlId . '/delete');
        $this->assertResponseIsSuccessful();

        // Find form and submit it
        $form = $crawler->filter('form[name="form"]')->form();
        $this->client->submit($form);

        $this->assertResponseRedirects('/url');
        $this->client->followRedirect();
        $this->assertSelectorExists('.alert-success');

        // Verify the URL was deleted
        $deletedUrl = $this->urlRepository->find($urlId);
        $this->assertNull($deletedUrl);
    }

    public function testEdit(): void
    {
        $this->loginAsUser();

        // Find or create a URL for this user
        $user = $this->userRepository->findOneByEmail('user@example.com');
        $this->assertNotNull($user, 'Test user not found');

        $url = $this->urlRepository->findOneBy(['users' => $user]);

        if (!$url) {
            // Create a URL for this user if none exists
            $url = new Url();
            $url->setLongName('https://user-test-url.com');
            $url->setShortName('user-test-' . uniqid());
            $url->setIsBlocked(false);
            $url->setUsers($user);
            $this->entityManager->persist($url);
            $this->entityManager->flush();
        }

        $this->assertNotNull($url, 'No URLs for test user');

        $this->client->request('GET', '/url/' . $url->getId() . '/edit');
        $this->assertResponseIsSuccessful();

        $this->client->submitForm('Edytuj', [
            'Url[longName]' => 'https://updated.example.com',
            'Url[tags]' => 'updated-tag'
        ]);

        $this->assertResponseRedirects('/url');
        $this->client->followRedirect();
        $this->assertSelectorExists('.alert-success');

        $updatedUrl = $this->urlRepository->find($url->getId());
        $this->assertEquals('https://updated.example.com', $updatedUrl->getLongName());
    }

    public function testBlock(): void
    {
        $this->loginAsAdmin();

        $url = $this->urlRepository->findOneBy(['isBlocked' => false]);
        $this->assertNotNull($url, 'No unblocked URLs in database');

        // Get the crawler so we can find the form
        $crawler = $this->client->request('GET', '/url/' . $url->getId() . '/block');
        $this->assertResponseIsSuccessful();

        $futureDate = new \DateTime('tomorrow');

        // Find the form by name and fill in fields directly
        $form = $crawler->filter('form[name="BlockUrl"]')->form();
        $form['BlockUrl[blockTime][date][day]'] = $futureDate->format('j');
        $form['BlockUrl[blockTime][date][month]'] = $futureDate->format('n');
        $form['BlockUrl[blockTime][date][year]'] = $futureDate->format('Y');
        $form['BlockUrl[blockTime][time][hour]'] = $futureDate->format('G');
        $form['BlockUrl[blockTime][time][minute]'] = (int)$futureDate->format('i');

        // Submit the form directly without referencing a button
        $this->client->submit($form);

        $this->assertResponseRedirects('/url/list');
        $this->client->followRedirect();
        $this->assertSelectorExists('.alert-success');
    }

    private function prepareBlockedUrl(): ?Url
    {
        $url = $this->urlRepository->findOneBy(['isBlocked' => true]);

        if (!$url) {
            // If no blocked URL exists, find any URL and block it
            $url = $this->urlRepository->findOneBy([]);
            if ($url) {
                $url->setIsBlocked(true);
                $url->setBlockTime(new \DateTimeImmutable('+1 day'));
                $this->entityManager->flush();
            }
        }

        return $url;
    }

    public function testUnblock(): void
    {
        $this->loginAsAdmin();

        // Create a blocked URL with a future block time
        $url = $this->urlRepository->findOneBy(['isBlocked' => true]);
        if (!$url) {
            $url = $this->prepareBlockedUrl();
        }
        $this->assertNotNull($url, 'No blocked URL available for testing');

        $urlId = $url->getId(); // Store ID for later retrieval

        // Get the crawler so we can find the form
        $crawler = $this->client->request('GET', '/url/' . $urlId . '/unblock');
        $this->assertResponseIsSuccessful();

        // Find form and submit it
        $form = $crawler->filter('form[name="form"]')->form();
        $this->client->submit($form);

        $this->assertResponseRedirects('/url/list');
        $this->client->followRedirect();
        $this->assertSelectorExists('.alert-success');

        // Fetch the entity fresh from the database instead of refreshing
        $updatedUrl = $this->urlRepository->find($urlId);
        $this->assertFalse($updatedUrl->isIsBlocked());
        $this->assertNull($updatedUrl->getBlockTime());
    }

    public function testAutoUnblock(): void
    {
        $this->loginAsAdmin();

        // Find an unblocked URL and modify it
        $url = $this->urlRepository->findOneBy(['isBlocked' => false]);
        $this->assertNotNull($url, 'No unblocked URL available for testing');

        $urlId = $url->getId(); // Store ID for later

        // Update the URL directly through the entity manager
        $url->setIsBlocked(true);
        $url->setBlockTime(new \DateTimeImmutable('-1 day'));  // Past date
        $this->entityManager->persist($url); // Ensure it's managed
        $this->entityManager->flush();

        // Request unblock page
        $this->client->request('GET', '/url/' . $urlId . '/unblock');

        // Should automatically unblock and redirect
        $this->assertResponseRedirects('/url/list');
        $this->client->followRedirect();
        $this->assertSelectorExists('.alert-success');

        // Fetch fresh from database instead of refreshing
        $updatedUrl = $this->urlRepository->find($urlId);
        $this->assertFalse($updatedUrl->isIsBlocked());
        $this->assertNull($updatedUrl->getBlockTime());
    }

    public function testNonExistentShortUrl(): void
    {
        $this->client->request('GET', '/url/short/non-existent-url');

        // Should redirect to list with warning flash
        $this->assertResponseRedirects('/url/list');
        $this->client->followRedirect();
        $this->assertSelectorExists('.alert-warning');
    }

    public function testEditBlockedUrlAsUser(): void
    {
        $this->loginAsUser();

        // Find a blocked URL
        $url = $this->urlRepository->findOneBy(['isBlocked' => true]);
        $this->assertNotNull($url, 'No blocked URL available for testing');

        // Try to edit as regular user
        $this->client->request('GET', '/url/' . $url->getId() . '/edit');

        // Should get access denied (403) instead of redirect
        $this->assertResponseStatusCodeSame(403);
    }

    public function testUnauthorizedDelete(): void
    {
        $this->loginAsUser();

        // Find a URL belonging to another user
        $anotherUser = $this->userRepository->findOneByEmail('another@example.com');
        $url = $this->urlRepository->findOneBy(['users' => $anotherUser]);
        $this->assertNotNull($url, 'No URLs for another user');

        // Try to delete as unauthorized user
        $this->client->request('GET', '/url/' . $url->getId() . '/delete');

        // Should get access denied
        $this->assertResponseStatusCodeSame(403);
    }

}