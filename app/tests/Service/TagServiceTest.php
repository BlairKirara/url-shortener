<?php

namespace App\Tests\Service;

use App\Entity\Tag;
use App\Repository\TagRepository;
use App\Service\TagService;
use Doctrine\ORM\QueryBuilder;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;
use PHPUnit\Framework\TestCase;

class TagServiceTest extends TestCase
{
    private TagRepository $tagRepository;
    private PaginatorInterface $paginator;
    private TagService $tagService;
    private PaginationInterface $pagination;

    protected function setUp(): void
    {
        // Create mock with properly specified methods
        $this->tagRepository = $this->getMockBuilder(TagRepository::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['save', 'delete', 'queryAll'])
            ->addMethods(['findOneByName', 'findOneById'])
            ->getMock();
        
        $this->paginator = $this->createMock(PaginatorInterface::class);
        $this->pagination = $this->createMock(PaginationInterface::class);
        $this->tagService = new TagService($this->tagRepository, $this->paginator);
    }

    public function testGetPaginatedList(): void
    {
        // Given
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

        // When
        $result = $this->tagService->getPaginatedList($page);

        // Then
        $this->assertSame($this->pagination, $result);
    }

    public function testSave(): void
    {
        // Given
        $tag = new Tag();
        $tag->setName('Test Tag');

        $this->tagRepository->expects($this->once())
            ->method('save')
            ->with($this->equalTo($tag));

        // When
        $this->tagService->save($tag);
    }

    public function testDelete(): void
    {
        // Given
        $tag = new Tag();
        $tag->setName('Test Tag');

        $this->tagRepository->expects($this->once())
            ->method('delete')
            ->with($this->equalTo($tag));

        // When
        $this->tagService->delete($tag);
    }

    public function testFindOneByName(): void
    {
        // Given
        $tagName = 'Test Tag';
        $expectedTag = new Tag();
        $expectedTag->setName($tagName);

        $this->tagRepository->expects($this->once())
            ->method('findOneByName')
            ->with($this->equalTo($tagName))
            ->willReturn($expectedTag);

        // When
        $result = $this->tagService->findOneByName($tagName);

        // Then
        $this->assertSame($expectedTag, $result);
    }

    public function testFindOneByNameReturnsNull(): void
    {
        // Given
        $tagName = 'Nonexistent Tag';

        $this->tagRepository->expects($this->once())
            ->method('findOneByName')
            ->with($this->equalTo($tagName))
            ->willReturn(null);

        // When
        $result = $this->tagService->findOneByName($tagName);

        // Then
        $this->assertNull($result);
    }

    public function testFindOneById(): void
    {
        // Given
        $tagId = 1;
        $expectedTag = new Tag();
        $expectedTag->setName('Test Tag');

        $this->tagRepository->expects($this->once())
            ->method('findOneById')
            ->with($this->equalTo($tagId))
            ->willReturn($expectedTag);

        // When
        $result = $this->tagService->findOneById($tagId);

        // Then
        $this->assertSame($expectedTag, $result);
    }

    public function testFindOneByIdReturnsNull(): void
    {
        // Given
        $tagId = 999;

        $this->tagRepository->expects($this->once())
            ->method('findOneById')
            ->with($this->equalTo($tagId))
            ->willReturn(null);

        // When
        $result = $this->tagService->findOneById($tagId);

        // Then
        $this->assertNull($result);
    }

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