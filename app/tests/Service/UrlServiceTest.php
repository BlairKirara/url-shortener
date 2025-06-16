<?php

/**
 * Class UrlServiceTest.
 *
 * Unit tests for UrlService.
 */

namespace App\Tests\Service;

use App\Entity\Tag;
use App\Entity\Url;
use App\Entity\User;
use App\Repository\GuestUserRepository;
use App\Repository\UrlRepository;
use App\Service\TagServiceInterface;
use App\Service\UrlService;
use Doctrine\ORM\QueryBuilder;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Security;

/**
 * Class UrlServiceTest.
 *
 * This class provides unit tests for UrlService.
 */
class UrlServiceTest extends TestCase
{
    /**
     * URL repository.
     *
     * @var UrlRepository
     */
    private UrlRepository $urlRepository;

    /**
     * Tag service.
     *
     * @var TagServiceInterface
     */
    private TagServiceInterface $tagService;

    /**
     * Paginator.
     *
     * @var PaginatorInterface
     */
    private PaginatorInterface $paginator;

    /**
     * Security component.
     *
     * @var Security
     */
    private Security $security;

    /**
     * Guest user repository.
     *
     * @var GuestUserRepository
     */
    private GuestUserRepository $guestUserRepository;

    /**
     * URL service.
     *
     * @var UrlService
     */
    private UrlService $urlService;

    /**
     * Pagination.
     *
     * @var PaginationInterface
     */
    private PaginationInterface $pagination;

    /**
     * Set up test environment.
     *
     * @return void
     */
    protected function setUp(): void
    {
        $this->urlRepository = $this->getMockBuilder(UrlRepository::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['queryByAuthor', 'queryAll', 'save', 'delete'])
            ->addMethods(['findOneByShortName'])
            ->getMock();

        $this->tagService = $this->getMockBuilder(TagServiceInterface::class)
            ->onlyMethods(['getPaginatedList', 'save', 'delete', 'findOneByName'])
            ->addMethods(['findOneById'])
            ->getMock();

        $this->paginator = $this->createMock(PaginatorInterface::class);
        $this->security = $this->createMock(Security::class);
        $this->guestUserRepository = $this->createMock(GuestUserRepository::class);
        $this->pagination = $this->createMock(PaginationInterface::class);

        $this->urlService = new UrlService(
            $this->paginator,
            $this->tagService,
            $this->urlRepository,
            $this->security,
            $this->guestUserRepository
        );
    }

    /**
     * Tests retrieving a paginated list of URLs for a user.
     *
     * @return void
     */
    public function testGetPaginatedList(): void
    {
        $page = 1;
        $user = $this->createMock(User::class);
        $filters = [];
        $queryBuilder = $this->createMock(QueryBuilder::class);

        $this->urlRepository->expects($this->once())
            ->method('queryByAuthor')
            ->with($this->equalTo($user), $this->equalTo([]))
            ->willReturn($queryBuilder);

        $this->paginator->expects($this->once())
            ->method('paginate')
            ->with(
                $queryBuilder,
                $page,
                UrlRepository::PAGINATOR_ITEMS_PER_PAGE
            )
            ->willReturn($this->pagination);

        $result = $this->urlService->getPaginatedList($page, $user, $filters);

        $this->assertSame($this->pagination, $result);
    }

    /**
     * Tests retrieving a paginated list of URLs for a user with tag filter.
     *
     * @return void
     */
    public function testGetPaginatedListWithTagFilter(): void
    {
        $page = 1;
        $user = $this->createMock(User::class);
        $tag = $this->createMock(Tag::class);
        $filters = ['tag_id' => 1];
        $queryBuilder = $this->createMock(QueryBuilder::class);

        $this->tagService->expects($this->once())
            ->method('findOneById')
            ->with($this->equalTo(1))
            ->willReturn($tag);

        $this->urlRepository->expects($this->once())
            ->method('queryByAuthor')
            ->with($this->equalTo($user), $this->equalTo(['tag' => $tag]))
            ->willReturn($queryBuilder);

        $this->paginator->expects($this->once())
            ->method('paginate')
            ->with(
                $queryBuilder,
                $page,
                UrlRepository::PAGINATOR_ITEMS_PER_PAGE
            )
            ->willReturn($this->pagination);

        $result = $this->urlService->getPaginatedList($page, $user, $filters);

        $this->assertSame($this->pagination, $result);
    }

    /**
     * Tests retrieving a paginated list of URLs for all users.
     *
     * @return void
     */
    public function testGetPaginatedListForAll(): void
    {
        $page = 1;
        $filters = [];
        $queryBuilder = $this->createMock(QueryBuilder::class);

        $this->urlRepository->expects($this->once())
            ->method('queryAll')
            ->with($this->equalTo([]))
            ->willReturn($queryBuilder);

        $this->paginator->expects($this->once())
            ->method('paginate')
            ->with(
                $queryBuilder,
                $page,
                UrlRepository::PAGINATOR_ITEMS_PER_PAGE
            )
            ->willReturn($this->pagination);

        $result = $this->urlService->getPaginatedListForAll($page, $filters);

        $this->assertSame($this->pagination, $result);
    }

    /**
     * Tests generating a shortened URL.
     *
     * @return void
     */
    public function testShortenUrl(): void
    {
        $result = $this->urlService->shortenUrl(8);

        $this->assertIsString($result);
        $this->assertEquals(8, strlen($result));
        $this->assertMatchesRegularExpression('/^[0-9a-zA-Z]+$/', $result);
    }

    /**
     * Tests saving a new URL.
     *
     * @return void
     */
    public function testSaveNewUrl(): void
    {
        $url = new Url();
        $url->setLongName('https://example.com');

        $this->urlRepository->expects($this->once())
            ->method('save')
            ->with($this->callback(function ($savedUrl) {
                return $savedUrl instanceof Url
                    && $savedUrl->getLongName() === 'https://example.com'
                    && $savedUrl->getShortName() !== null
                    && $savedUrl->isIsBlocked() === false;
            }));

        $this->urlService->save($url);

        $this->assertFalse($url->isIsBlocked());
        $this->assertNotNull($url->getShortName());
    }

    /**
     * Tests saving an existing URL.
     *
     * @return void
     */
    public function testSaveExistingUrl(): void
    {
        $url = $this->getMockBuilder(Url::class)
            ->onlyMethods(['getId'])
            ->getMock();

        $url->expects($this->once())
            ->method('getId')
            ->willReturn(1);

        $url->setLongName('https://example.com');
        $url->setShortName('abc123');
        $url->setIsBlocked(true);

        $this->urlRepository->expects($this->once())
            ->method('save')
            ->with($this->equalTo($url));

        $this->urlService->save($url);

        $this->assertEquals('abc123', $url->getShortName());
        $this->assertTrue($url->isIsBlocked());
    }

    /**
     * Tests deleting a URL.
     *
     * @return void
     */
    public function testDelete(): void
    {
        $url = new Url();
        $url->setLongName('https://example.com');
        $url->setShortName('abc123');

        $this->urlRepository->expects($this->once())
            ->method('delete')
            ->with($this->equalTo($url));

        $this->urlService->delete($url);
    }

    /**
     * Tests finding a URL by its short name.
     *
     * @return void
     */
    public function testFindOneByShortName(): void
    {
        $shortName = 'abc123';
        $url = new Url();
        $url->setLongName('https://example.com');
        $url->setShortName($shortName);

        $this->urlRepository->expects($this->once())
            ->method('findOneByShortName')
            ->with($this->equalTo(['shortName' => $shortName]))
            ->willReturn($url);

        $result = $this->urlService->findOneByShortName($shortName);

        $this->assertSame($url, $result);
    }

    /**
     * Tear down test environment.
     *
     * @return void
     */
    protected function tearDown(): void
    {
        unset(
            $this->urlRepository,
            $this->tagService,
            $this->paginator,
            $this->security,
            $this->guestUserRepository,
            $this->pagination,
            $this->urlService
        );
    }
}