<?php

/**
 * Class TagServiceTest.
 *
 * Unit tests for TagService.
 */

namespace App\Tests\Service;

use App\Entity\Tag;
use App\Repository\TagRepository;
use App\Service\TagService;
use Doctrine\ORM\QueryBuilder;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;
use PHPUnit\Framework\TestCase;

/**
 * Class TagServiceTest.
 *
 * This class provides unit tests for TagService.
 */
class TagServiceTest extends TestCase
{
    /**
     * Tag repository mock.
     *
     * @var TagRepository
     */
    private TagRepository $tagRepository;

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
     * Tag service.
     *
     * @var TagService
     */
    private TagService $tagService;

    /**
     * Set up test environment.
     *
     * @return void
     */
    protected function setUp(): void
    {
        $this->tagRepository = $this->getMockBuilder(TagRepository::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['save', 'delete', 'queryAll'])
            ->addMethods(['findOneByName', 'findOneById'])
            ->getMock();

        $this->paginator = $this->createMock(PaginatorInterface::class);
        $this->pagination = $this->createMock(PaginationInterface::class);
        $this->tagService = new TagService($this->tagRepository, $this->paginator);
    }

    /**
     * Test retrieving a paginated list of tags.
     *
     * @return void
     */
    public function testGetPaginatedList(): void
    {
        $page = 1;
        $queryBuilder = $this->createMock(QueryBuilder::class);

        $this->tagRepository->expects($this->once())
            ->method('queryAll')
            ->willReturn($queryBuilder);

        $this->paginator->expects($this->once())
            ->method('paginate')
            ->with(
                $queryBuilder,
                $page,
                TagRepository::PAGINATOR_ITEMS_PER_PAGE
            )
            ->willReturn($this->pagination);

        $result = $this->tagService->getPaginatedList($page);

        $this->assertSame($this->pagination, $result);
    }

    /**
     * Test saving a tag.
     *
     * @return void
     */
    public function testSave(): void
    {
        $tag = new Tag();
        $tag->setName('Test Tag');

        $this->tagRepository->expects($this->once())
            ->method('save')
            ->with($this->equalTo($tag));

        $this->tagService->save($tag);
    }

    /**
     * Test deleting a tag.
     *
     * @return void
     */
    public function testDelete(): void
    {
        $tag = new Tag();
        $tag->setName('Test Tag');

        $this->tagRepository->expects($this->once())
            ->method('delete')
            ->with($this->equalTo($tag));

        $this->tagService->delete($tag);
    }

    /**
     * Test finding a tag by name.
     *
     * @return void
     */
    public function testFindOneByName(): void
    {
        $tagName = 'Test Tag';
        $expectedTag = new Tag();
        $expectedTag->setName($tagName);

        $this->tagRepository->expects($this->once())
            ->method('findOneByName')
            ->with($this->equalTo($tagName))
            ->willReturn($expectedTag);

        $result = $this->tagService->findOneByName($tagName);

        $this->assertSame($expectedTag, $result);
    }

    /**
     * Test finding a tag by name returns null if not found.
     *
     * @return void
     */
    public function testFindOneByNameReturnsNull(): void
    {
        $tagName = 'Nonexistent Tag';

        $this->tagRepository->expects($this->once())
            ->method('findOneByName')
            ->with($this->equalTo($tagName))
            ->willReturn(null);

        $result = $this->tagService->findOneByName($tagName);

        $this->assertNull($result);
    }

    /**
     * Test finding a tag by ID.
     *
     * @return void
     */
    public function testFindOneById(): void
    {
        $tagId = 1;
        $expectedTag = new Tag();
        $expectedTag->setName('Test Tag');

        $this->tagRepository->expects($this->once())
            ->method('findOneById')
            ->with($this->equalTo($tagId))
            ->willReturn($expectedTag);

        $result = $this->tagService->findOneById($tagId);

        $this->assertSame($expectedTag, $result);
    }

    /**
     * Test finding a tag by ID returns null if not found.
     *
     * @return void
     */
    public function testFindOneByIdReturnsNull(): void
    {
        $tagId = 999;

        $this->tagRepository->expects($this->once())
            ->method('findOneById')
            ->with($this->equalTo($tagId))
            ->willReturn(null);

        $result = $this->tagService->findOneById($tagId);

        $this->assertNull($result);
    }

    /**
     * Clean up after tests.
     *
     * @return void
     */
    protected function tearDown(): void
    {
        unset(
            $this->tagRepository,
            $this->paginator,
            $this->pagination,
            $this->tagService
        );
    }
}