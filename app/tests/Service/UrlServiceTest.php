<?php

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
 * Tests for the UrlService.
 */
class UrlServiceTest extends TestCase
{
    /**
     * URL repository.
     */
    private UrlRepository $urlRepository;

    /**
     * Tag service.
     */
    private TagServiceInterface $tagService;

    /**
     * Paginator.
     */
    private PaginatorInterface $paginator;

    /**
     * Security.
     */
    private Security $security;

    /**
     * Guest user repository.
     */
    private GuestUserRepository $guestUserRepository;

    /**
     * URL service.
     */
    private UrlService $urlService;

    /**
     * Pagination.
     */
    private PaginationInterface $pagination;

    /**
     * Set up test environment.
     */
    protected function setUp(): void
    {
        $this->urlRepository = $this->getMockBuilder(UrlRepository::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['queryByAuthor', 'queryAll', 'save', 'delete'])
            ->addMethods(['findOneByShortName'])
            ->getMock();

        // Create tag service mock with necessary methods
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
     * Test getPaginatedList method.
     */
    public function testGetPaginatedList(): void
    {
        // Given
        $page = 1;
        $user = $this->createMock(User::class);
        $filters = [];
        $queryBuilder = $this->createMock(QueryBuilder::class);

        // Expect queryByAuthor method to be called once with the correct parameters
        $this->urlRepository->expects($this->once())
            ->method('queryByAuthor')
            ->with($this->equalTo($user), $this->equalTo([]))
            ->willReturn($queryBuilder);

        // Expect paginate method to be called once with the correct parameters
        $this->paginator->expects($this->once())
            ->method('paginate')
            ->with(
                $queryBuilder,
                $page,
                UrlRepository::PAGINATOR_ITEMS_PER_PAGE
            )
            ->willReturn($this->pagination);

        // When
        $result = $this->urlService->getPaginatedList($page, $user, $filters);

        // Then
        $this->assertSame($this->pagination, $result);
    }

    /**
     * Test getPaginatedList method with tag filter.
     */
    public function testGetPaginatedListWithTagFilter(): void
    {
        // Given
        $page = 1;
        $user = $this->createMock(User::class);
        $tag = $this->createMock(Tag::class);
        $filters = ['tag_id' => 1];
        $queryBuilder = $this->createMock(QueryBuilder::class);

        // Set up the tagService mock
        $this->tagService->expects($this->once())
            ->method('findOneById')
            ->with($this->equalTo(1))
            ->willReturn($tag);

        // Expect queryByAuthor method to be called once with the correct parameters
        $this->urlRepository->expects($this->once())
            ->method('queryByAuthor')
            ->with($this->equalTo($user), $this->equalTo(['tag' => $tag]))
            ->willReturn($queryBuilder);

        // Expect paginate method to be called once with the correct parameters
        $this->paginator->expects($this->once())
            ->method('paginate')
            ->with(
                $queryBuilder,
                $page,
                UrlRepository::PAGINATOR_ITEMS_PER_PAGE
            )
            ->willReturn($this->pagination);

        // When
        $result = $this->urlService->getPaginatedList($page, $user, $filters);

        // Then
        $this->assertSame($this->pagination, $result);
    }

    /**
     * Test getPaginatedListForAll method.
     */
    public function testGetPaginatedListForAll(): void
    {
        // Given
        $page = 1;
        $filters = [];
        $queryBuilder = $this->createMock(QueryBuilder::class);

        // Expect queryAll method to be called once with the correct parameters
        $this->urlRepository->expects($this->once())
            ->method('queryAll')
            ->with($this->equalTo([]))
            ->willReturn($queryBuilder);

        // Expect paginate method to be called once with the correct parameters
        $this->paginator->expects($this->once())
            ->method('paginate')
            ->with(
                $queryBuilder,
                $page,
                UrlRepository::PAGINATOR_ITEMS_PER_PAGE
            )
            ->willReturn($this->pagination);

        // When
        $result = $this->urlService->getPaginatedListForAll($page, $filters);

        // Then
        $this->assertSame($this->pagination, $result);
    }

    /**
     * Test shortenUrl method.
     */
    public function testShortenUrl(): void
    {
        // When
        $result = $this->urlService->shortenUrl(8);

        // Then
        $this->assertIsString($result);
        $this->assertEquals(8, strlen($result));
        $this->assertMatchesRegularExpression('/^[0-9a-zA-Z]+$/', $result);
    }

    /**
     * Test save method for a new URL.
     */
    public function testSaveNewUrl(): void
    {
        // Given
        $url = new Url();
        $url->setLongName('https://example.com');

        // Expect save method to be called once with the correct parameter
        $this->urlRepository->expects($this->once())
            ->method('save')
            ->with($this->callback(function ($savedUrl) {
                return $savedUrl instanceof Url
                    && $savedUrl->getLongName() === 'https://example.com'
                    && $savedUrl->getShortName() !== null
                    && $savedUrl->isIsBlocked() === false;
            }));

        // When
        $this->urlService->save($url);

        // Then
        $this->assertFalse($url->isIsBlocked());
        $this->assertNotNull($url->getShortName());
    }

    /**
     * Test save method for an existing URL.
     */
    public function testSaveExistingUrl(): void
    {
        // Given
        $url = $this->getMockBuilder(Url::class)
            ->onlyMethods(['getId'])
            ->getMock();
        
        $url->expects($this->once())
            ->method('getId')
            ->willReturn(1);
            
        $url->setLongName('https://example.com');
        $url->setShortName('abc123');
        $url->setIsBlocked(true);

        // Expect save method to be called once with the correct parameter
        $this->urlRepository->expects($this->once())
            ->method('save')
            ->with($this->equalTo($url));

        // When
        $this->urlService->save($url);

        // Then
        $this->assertEquals('abc123', $url->getShortName());
        $this->assertTrue($url->isIsBlocked());
    }

    /**
     * Test delete method.
     */
    public function testDelete(): void
    {
        // Given
        $url = new Url();
        $url->setLongName('https://example.com');
        $url->setShortName('abc123');

        // Expect delete method to be called once with the correct parameter
        $this->urlRepository->expects($this->once())
            ->method('delete')
            ->with($this->equalTo($url));

        // When
        $this->urlService->delete($url);
    }

    /**
     * Test findOneByShortName method.
     */
    public function testFindOneByShortName(): void
    {
        // Given
        $shortName = 'abc123';
        $url = new Url();
        $url->setLongName('https://example.com');
        $url->setShortName($shortName);

        // Expect findOneByShortName method to be called once with the correct parameter
        $this->urlRepository->expects($this->once())
            ->method('findOneByShortName')
            ->with($this->equalTo(['shortName' => $shortName]))
            ->willReturn($url);

        // When
        $result = $this->urlService->findOneByShortName($shortName);

        // Then
        $this->assertSame($url, $result);
    }

    /**
     * Tear down test environment.
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