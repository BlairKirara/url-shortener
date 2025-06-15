<?php

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
 * Tests for the UrlDataService.
 */
class UrlDataServiceTest extends TestCase
{
    /**
     * URL data repository.
     */
    private UrlDataRepository $urlDataRepository;

    /**
     * Paginator.
     */
    private PaginatorInterface $paginator;

    /**
     * URL data service.
     */
    private UrlDataService $urlDataService;

    /**
     * Pagination.
     */
    private PaginationInterface $pagination;

    /**
     * Set up test environment.
     */
    protected function setUp(): void
    {
        // Create mock with properly specified methods
        $this->urlDataRepository = $this->getMockBuilder(UrlDataRepository::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['save', 'countVisits', 'deleteUrlVisits'])
            ->getMock();
        
        $this->paginator = $this->createMock(PaginatorInterface::class);
        $this->pagination = $this->createMock(PaginationInterface::class);
        $this->urlDataService = new UrlDataService($this->urlDataRepository, $this->paginator);
    }

    /**
     * Test save method.
     */
    public function testSave(): void
    {
        // Given
        $urlData = new UrlData();
        
        // Expect save method to be called once with the correct parameter
        $this->urlDataRepository->expects($this->once())
            ->method('save')
            ->with($this->equalTo($urlData));

        // When
        $this->urlDataService->save($urlData);
    }

    /**
     * Test countVisits method.
     */
    public function testCountVisits(): void
    {
        // Given
        $page = 1;
        $visitsArray = []; // Empty array for testing purposes

        // Expect countVisits method to be called once and return an array
        $this->urlDataRepository->expects($this->once())
            ->method('countVisits')
            ->willReturn($visitsArray);

        // Expect paginate method to be called once with the correct parameters
        $this->paginator->expects($this->once())
            ->method('paginate')
            ->with(
                $visitsArray,
                $page,
                UrlDataRepository::PAGINATOR_ITEMS_PER_PAGE
            )
            ->willReturn($this->pagination);

        // When
        $result = $this->urlDataService->countVisits($page);

        // Then
        $this->assertSame($this->pagination, $result);
    }

    /**
     * Test deleteUrlVisits method.
     */
    public function testDeleteUrlVisits(): void
    {
        // Given
        $urlId = 1;

        // Expect deleteUrlVisits method to be called once with the correct parameter
        $this->urlDataRepository->expects($this->once())
            ->method('deleteUrlVisits')
            ->with($this->equalTo($urlId));

        // When
        $this->urlDataService->deleteUrlVisits($urlId);
    }

    /**
     * Tear down test environment.
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