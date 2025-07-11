<?php

/**
 * UrlController test.
 */

namespace App\Tests\Controller;

use App\Entity\Url;
use App\Entity\User;
use App\Entity\Tag;
use App\Repository\UrlRepository;
use App\Repository\UserRepository;
use App\Repository\TagRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Class UrlControllerTest.
 */
class UrlControllerTest extends WebTestCase
{
    /**
     * Symfony client.
     *
     * @var \Symfony\Bundle\FrameworkBundle\KernelBrowser
     */
    private $client;

    /**
     * Url repository.
     *
     * @var UrlRepository
     */
    private $urlRepository;

    /**
     * User repository.
     *
     * @var UserRepository
     */
    private $userRepository;

    /**
     * Tag repository.
     *
     * @var TagRepository
     */
    private $tagRepository;

    /**
     * Doctrine entity manager.
     *
     * @var \Doctrine\ORM\EntityManagerInterface
     */
    private $entityManager;

    /**
     * Password hasher.
     *
     * @var \Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface
     */
    private $passwordHasher;

    /**
     * Set up test environment.
     */
    protected function setUp(): void
    {
        $this->client = static::createClient();
        $container = static::getContainer();
        $this->urlRepository = $container->get(UrlRepository::class);
        $this->userRepository = $container->get(UserRepository::class);
        $this->tagRepository = $container->get(TagRepository::class);
        $this->entityManager = $container->get('doctrine.orm.entity_manager');
        $this->passwordHasher = $container->get('security.user_password_hasher');
        $this->createTestData();
    }

    /**
     * Test index page.
     */
    public function testIndex(): void
    {
        $this->loginAsUser();
        $this->client->request('GET', '/url');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('.container');
    }

    /**
     * Test list page.
     */
    public function testList(): void
    {
        $this->client->request('GET', '/url/list');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('.container');
    }

    /**
     * Test list page with tag filter.
     */
    public function testListWithTagFilter(): void
    {
        $tag = $this->tagRepository->findOneBy([]);
        $this->assertNotNull($tag, 'No tags in database');
        $this->client->request('GET', '/url/list?filters_tag_id='.$tag->getId());
        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('.container');
    }

    /**
     * Test show URL details.
     */
    public function testShow(): void
    {
        $url = $this->urlRepository->findOneBy(['isBlocked' => false]);
        $this->assertNotNull($url, 'No unblocked URLs in database');
        $this->client->request('GET', '/url/'.$url->getId());
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('body', $url->getLongName());
    }

    /**
     * Test redirect to long URL.
     */
    public function testRedirectToUrl(): void
    {
        $url = $this->urlRepository->findOneBy(['isBlocked' => false]);
        $this->assertNotNull($url, 'No unblocked URLs in database');
        $shortName = $url->getShortName();
        $this->client->request('GET', '/url/short/'.$shortName);
        $this->assertResponseRedirects($url->getLongName());
    }

    /**
     * Test redirect to blocked URL.
     */
    public function testRedirectToBlockedUrl(): void
    {
        $url = $this->prepareBlockedUrl();
        $this->assertNotNull($url, 'No blocked URL available for testing');
        $shortName = $url->getShortName();
        $this->client->request('GET', '/url/short/'.$shortName);
        $this->assertResponseRedirects('/url/list');
    }

    /**
     * Test create URL as user.
     */
    public function testCreate(): void
    {
        $this->loginAsUser();
        $this->client->request('GET', '/url/create');
        $this->assertResponseIsSuccessful();
        $tag = $this->tagRepository->findOneBy([]);
        $this->assertNotNull($tag, 'No tags in database');
        $this->client->submitForm('Zapisz', [
            'Url[longName]' => 'https://test.example.com',
            'Url[tags]' => $tag->getName(),
        ]);
        $this->assertResponseRedirects('/url/list');
        $this->client->followRedirect();
        $this->assertSelectorExists('.alert-success');
        $url = $this->urlRepository->findOneBy(['longName' => 'https://test.example.com']);
        $this->assertNotNull($url);
    }

    /**
     * Test create URL as guest.
     */
    public function testCreateAsGuest(): void
    {
        $this->client->request('GET', '/url/create');
        $this->assertResponseIsSuccessful();
        $tag = $this->tagRepository->findOneBy([]);
        $this->assertNotNull($tag, 'No tags in database');
        $this->client->submitForm('Zapisz', [
            'Url[email]' => 'guest@example.com',
            'Url[longName]' => 'https://guest.example.com',
            'Url[tags]' => $tag->getName(),
        ]);
        $this->assertResponseRedirects('/url/list');
        $this->client->followRedirect();
        $this->assertSelectorExists('.alert-success');
        $url = $this->urlRepository->findOneBy(['longName' => 'https://guest.example.com']);
        $this->assertNotNull($url);
    }

    /**
     * Test delete URL.
     */
    public function testDelete(): void
    {
        $this->loginAsUser();
        $user = $this->userRepository->findOneByEmail('user@example.com');
        $url = $this->urlRepository->findOneBy(['users' => $user]);
        $this->assertNotNull($url, 'No URLs for test user');
        $urlId = $url->getId();
        $crawler = $this->client->request('GET', '/url/'.$urlId.'/delete');
        $this->assertResponseIsSuccessful();
        $form = $crawler->filter('form[name="form"]')->form();
        $this->client->submit($form);
        $this->assertResponseRedirects('/url');
        $this->client->followRedirect();
        $this->assertSelectorExists('.alert-success');
        $deletedUrl = $this->urlRepository->find($urlId);
        $this->assertNull($deletedUrl);
    }

    /**
     * Test delete blocked URL.
     */
    public function testDeleteBlockedUrl(): void
    {
        $this->loginAsUser();
        $user = $this->userRepository->findOneByEmail('user@example.com');
        $url = new Url();
        $url->setLongName('https://blocked-url-test.com');
        $url->setShortName('blocked-'.uniqid());
        $url->setIsBlocked(true);
        $url->setBlockTime(new \DateTimeImmutable('+1 day'));
        $url->setUsers($user);
        $this->entityManager->persist($url);
        $this->entityManager->flush();

        $this->client->request('GET', '/url/'.$url->getId().'/delete');
        $this->assertResponseRedirects('/url');
        $this->client->followRedirect();
        $this->assertSelectorExists('.alert-warning');
    }

    /**
     * Test edit URL.
     */
    public function testEdit(): void
    {
        $this->loginAsUser();
        $user = $this->userRepository->findOneByEmail('user@example.com');
        $this->assertNotNull($user, 'Test user not found');
        $url = $this->urlRepository->findOneBy(['users' => $user]);
        if (!$url) {
            $url = new Url();
            $url->setLongName('https://user-test-url.com');
            $url->setShortName('user-test-'.uniqid());
            $url->setIsBlocked(false);
            $url->setUsers($user);
            $this->entityManager->persist($url);
            $this->entityManager->flush();
        }
        $this->assertNotNull($url, 'No URLs for test user');
        $this->client->request('GET', '/url/'.$url->getId().'/edit');
        $this->assertResponseIsSuccessful();
        $this->client->submitForm('Edytuj', [
            'Url[longName]' => 'https://updated.example.com',
            'Url[tags]' => 'updated-tag',
        ]);
        $this->assertResponseRedirects('/url');
        $this->client->followRedirect();
        $this->assertSelectorExists('.alert-success');
        $updatedUrl = $this->urlRepository->find($url->getId());
        $this->assertEquals('https://updated.example.com', $updatedUrl->getLongName());
    }

    /**
     * Test block URL.
     */
    public function testBlock(): void
    {
        $this->loginAsAdmin();
        $url = $this->urlRepository->findOneBy(['isBlocked' => false]);
        $this->assertNotNull($url, 'No unblocked URLs in database');
        $crawler = $this->client->request('GET', '/url/'.$url->getId().'/block');
        $this->assertResponseIsSuccessful();
        $futureDate = new \DateTime('tomorrow');
        $form = $crawler->filter('form[name="BlockUrl"]')->form();
        $form['BlockUrl[blockTime][date][day]'] = $futureDate->format('j');
        $form['BlockUrl[blockTime][date][month]'] = $futureDate->format('n');
        $form['BlockUrl[blockTime][date][year]'] = $futureDate->format('Y');
        $form['BlockUrl[blockTime][time][hour]'] = $futureDate->format('G');
        $form['BlockUrl[blockTime][time][minute]'] = (int) $futureDate->format('i');
        $this->client->submit($form);
        $this->assertResponseRedirects('/url/list');
        $this->client->followRedirect();
        $this->assertSelectorExists('.alert-success');
    }

    /**
     * Test unblock URL.
     */
    public function testUnblock(): void
    {
        $this->loginAsAdmin();
        $url = $this->urlRepository->findOneBy(['isBlocked' => true]);
        if (!$url) {
            $url = $this->prepareBlockedUrl();
        }
        $this->assertNotNull($url, 'No blocked URL available for testing');
        $urlId = $url->getId();
        $crawler = $this->client->request('GET', '/url/'.$urlId.'/unblock');
        $this->assertResponseIsSuccessful();
        $form = $crawler->filter('form[name="form"]')->form();
        $this->client->submit($form);
        $this->assertResponseRedirects('/url/list');
        $this->client->followRedirect();
        $this->assertSelectorExists('.alert-success');
        $updatedUrl = $this->urlRepository->find($urlId);
        $this->assertFalse($updatedUrl->isIsBlocked());
        $this->assertNull($updatedUrl->getBlockTime());
    }

    /**
     * Test auto-unblock URL.
     */
    public function testAutoUnblock(): void
    {
        $this->loginAsAdmin();
        $url = $this->urlRepository->findOneBy(['isBlocked' => false]);
        $this->assertNotNull($url, 'No unblocked URL available for testing');
        $urlId = $url->getId();
        $url->setIsBlocked(true);
        $url->setBlockTime(new \DateTimeImmutable('-1 day'));
        $this->entityManager->persist($url);
        $this->entityManager->flush();
        $this->client->request('GET', '/url/'.$urlId.'/unblock');
        $this->assertResponseRedirects('/url/list');
        $this->client->followRedirect();
        $this->assertSelectorExists('.alert-success');
        $updatedUrl = $this->urlRepository->find($urlId);
        $this->assertFalse($updatedUrl->isIsBlocked());
        $this->assertNull($updatedUrl->getBlockTime());
    }

    /**
     * Test redirect to non-existent short URL.
     */
    public function testNonExistentShortUrl(): void
    {
        $this->client->request('GET', '/url/short/non-existent-url');
        $this->assertResponseRedirects('/url/list');
        $this->client->followRedirect();
        $this->assertSelectorExists('.alert-warning');
    }

    /**
     * Test edit blocked URL as user.
     */
    public function testEditBlockedUrlAsUser(): void
    {
        $this->loginAsUser();
        $user = $this->userRepository->findOneByEmail('user@example.com');
        $url = new Url();
        $url->setLongName('https://blocked-edit-test.com');
        $url->setShortName('blocked-edit-'.uniqid());
        $url->setIsBlocked(true);
        $url->setBlockTime(new \DateTimeImmutable('+1 day'));
        $url->setUsers($user);
        $this->entityManager->persist($url);
        $this->entityManager->flush();

        $this->client->request('GET', '/url/'.$url->getId().'/edit');
        $this->assertResponseRedirects('/url');
        $this->client->followRedirect();
        $this->assertSelectorExists('.alert-warning');
    }

    /**
     * Test unauthorized delete.
     */
    public function testUnauthorizedDelete(): void
    {
        $this->loginAsUser();
        $anotherUser = $this->userRepository->findOneByEmail('another@example.com');
        $url = $this->urlRepository->findOneBy(['users' => $anotherUser]);
        $this->assertNotNull($url, 'No URLs for another user');
        $this->client->request('GET', '/url/'.$url->getId().'/delete');
        $this->assertResponseStatusCodeSame(403);
    }

    /**
     * Create test data for Url, User, and Tag entities.
     */
    private function createTestData(): void
    {
        if ($this->urlRepository->count([]) > 0) {
            return;
        }

        $admin = $this->userRepository->findOneByEmail('admin@example.com');
        if (!$admin) {
            $admin = new User();
            $admin->setEmail('admin@example.com');
            $admin->setRoles(['ROLE_ADMIN']);
            $admin->setPassword($this->passwordHasher->hashPassword($admin, 'admin_pass'));
            $this->entityManager->persist($admin);
            $this->entityManager->flush();
        }

        $user = $this->userRepository->findOneByEmail('user@example.com');
        if (!$user) {
            $user = new User();
            $user->setEmail('user@example.com');
            $user->setRoles(['ROLE_USER']);
            $user->setPassword($this->passwordHasher->hashPassword($user, 'user_pass'));
            $this->entityManager->persist($user);
            $this->entityManager->flush();
        }

        $anotherUser = $this->userRepository->findOneByEmail('another@example.com');
        if (!$anotherUser) {
            $anotherUser = new User();
            $anotherUser->setEmail('another@example.com');
            $anotherUser->setRoles(['ROLE_USER']);
            $anotherUser->setPassword($this->passwordHasher->hashPassword($anotherUser, 'another_pass'));
            $this->entityManager->persist($anotherUser);
            $this->entityManager->flush();
        }

        $admin = $this->userRepository->findOneByEmail('admin@example.com');
        $user = $this->userRepository->findOneByEmail('user@example.com');
        $anotherUser = $this->userRepository->findOneByEmail('another@example.com');

        $tag = new Tag();
        $tag->setName('test-tag');
        $this->entityManager->persist($tag);
        $this->entityManager->flush();

        $url1 = new Url();
        $url1->setLongName('https://example.com');
        $url1->setShortName('test123');
        $url1->setIsBlocked(false);
        $url1->setUsers($user);
        $url1->addTag($tag);
        $this->entityManager->persist($url1);

        $url2 = new Url();
        $url2->setLongName('https://another-example.com');
        $url2->setShortName('another123');
        $url2->setIsBlocked(false);
        $url2->setUsers($anotherUser);
        $this->entityManager->persist($url2);

        $url3 = new Url();
        $url3->setLongName('https://blocked-example.com');
        $url3->setShortName('blocked123');
        $url3->setIsBlocked(true);
        $url3->setBlockTime(new \DateTimeImmutable('+1 day'));
        $url3->setUsers($admin);
        $this->entityManager->persist($url3);

        $this->entityManager->flush();
    }

    /**
     * Log in as admin user.
     */
    private function loginAsAdmin(): void
    {
        $admin = $this->userRepository->findOneByEmail('admin@example.com');
        if (!$admin) {
            $admin = new User();
            $admin->setEmail('admin@example.com');
            $admin->setRoles(['ROLE_ADMIN']);
            $admin->setPassword($this->passwordHasher->hashPassword($admin, 'admin_pass'));
            $this->entityManager->persist($admin);
            $this->entityManager->flush();
            $admin = $this->userRepository->findOneByEmail('admin@example.com');
        }
        $this->client->loginUser($admin);
    }

    /**
     * Log in as regular user.
     */
    private function loginAsUser(): void
    {
        $user = $this->userRepository->findOneByEmail('user@example.com');
        if (!$user) {
            $user = new User();
            $user->setEmail('user@example.com');
            $user->setRoles(['ROLE_USER']);
            $user->setPassword($this->passwordHasher->hashPassword($user, 'user_pass'));
            $this->entityManager->persist($user);
            $this->entityManager->flush();
            $user = $this->userRepository->findOneByEmail('user@example.com');
        }
        $this->client->loginUser($user);
    }

    /**
     * Prepare a blocked URL for testing.
     *
     * @return Url|null The blocked URL entity or null
     */
    private function prepareBlockedUrl(): ?Url
    {
        $url = $this->urlRepository->findOneBy(['isBlocked' => true]);
        if (!$url) {
            $url = $this->urlRepository->findOneBy([]);
        }

        return $url;
    }
}
