<?php

/**
 * Class UrlDataServiceTest.
 *
 * Unit tests for UrlDataService.
 */

namespace App\Tests\Service;

use App\Entity\UrlData;
use App\Repository\UrlDataRepository;
use App\Service\UrlDataService;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;
use PHPUnit\Framework\TestCase;

/**
 * Class UrlDataServiceTest.
 *
 * This class provides unit tests for UrlDataService.
 */
class UrlDataServiceTest extends TestCase
{
    /**
     * URL data repository mock.
     *
     * @var UrlDataRepository
     */
    private UrlDataRepository $urlDataRepository;

    /**
     * Paginator mock.
     *
     * @var PaginatorInterface
     */
    private PaginatorInterface $paginator;

    /**
     * Pagination mock.
     *
     * @var PaginationInterface
     */
    private PaginationInterface $pagination;

    /**
     * URL data service.
     *
     * @var UrlDataService
     */
    private UrlDataService $urlDataService;

    /**
     * Set up test environment.
     *
     * @return void
     */
    protected function setUp(): void
    {
        $this->urlDataRepository = $this->getMockBuilder(UrlDataRepository::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['save', 'countVisits', 'deleteUrlVisits'])
            ->getMock();

        $this->paginator = $this->createMock(PaginatorInterface::class);
        $this->pagination = $this->createMock(PaginationInterface::class);
        $this->urlDataService = new UrlDataService($this->urlDataRepository, $this->paginator);
    }

    /**
     * Tests saving URL data.
     *
     * @return void
     */
    public function testSave(): void
    {
        $urlData = new UrlData();

        $this->urlDataRepository->expects($this->once())
            ->method('save')
            ->with($this->equalTo($urlData));

        $this->urlDataService->save($urlData);
    }

    /**
     * Tests counting visits and paginating results.
     *
     * @return void
     */
    public function testCountVisits(): void
    {
        $page = 1;
        $visitsArray = [];

        $this->urlDataRepository->expects($this->once())
            ->method('countVisits')
            ->willReturn($visitsArray);

        $this->paginator->expects($this->once())
            ->method('paginate')
            ->with(
                $visitsArray,
                $page,
                UrlDataRepository::PAGINATOR_ITEMS_PER_PAGE
            )
            ->willReturn($this->pagination);

        $result = $this->urlDataService->countVisits($page);

        $this->assertSame($this->pagination, $result);
    }

    /**
     * Tests deleting URL visits by ID.
     *
     * @return void
     */
    public function testDeleteUrlVisits(): void
    {
        $urlId = 1;

        $this->urlDataRepository->expects($this->once())
            ->method('deleteUrlVisits')
            ->with($this->equalTo($urlId));

        $this->urlDataService->deleteUrlVisits($urlId);
    }

    /**
     * Clean up after tests.
     *
     * @return void
     */
    protected function tearDown(): void
    {
        unset(
            $this->urlDataRepository,
            $this->paginator,
            $this->pagination,
            $this->urlDataService
        );
    }
}