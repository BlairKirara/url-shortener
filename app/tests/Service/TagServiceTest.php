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

    /**
     * Test repository queryAll method
     */
    public function testQueryAllMethodInRepository(): void
    {
        // Create a real repository instance with a mocked ManagerRegistry
        $managerRegistry = $this->createMock(\Doctrine\Persistence\ManagerRegistry::class);
        $entityManager = $this->createMock(\Doctrine\ORM\EntityManager::class);
        $queryBuilder = $this->createMock(QueryBuilder::class);

        // Configure the mocks for proper chain calling
        $managerRegistry->expects($this->any())
            ->method('getManagerForClass')
            ->willReturn($entityManager);

        // Create a partial mock of the repository
        $repository = $this->getMockBuilder(TagRepository::class)
            ->setConstructorArgs([$managerRegistry])
            ->onlyMethods(['createQueryBuilder'])
            ->getMock();

        // Set up the query builder expectations
        $repository->expects($this->once())
            ->method('createQueryBuilder')
            ->with('tag')
            ->willReturn($queryBuilder);

        $queryBuilder->expects($this->once())
            ->method('select')
            ->with('partial tag.{id, name}')
            ->willReturnSelf();

        $queryBuilder->expects($this->once())
            ->method('orderBy')
            ->with('tag.id', 'ASC')
            ->willReturnSelf();

        // Execute the repository method
        $result = $repository->queryAll();

        // Assert result is the query builder
        $this->assertSame($queryBuilder, $result);
    }

    /**
     * Test the behavior of getOrCreateQueryBuilder indirectly through queryAll
     * when createQueryBuilder returns a new query builder
     */
    public function testGetOrCreateQueryBuilderMethod(): void
    {
        $managerRegistry = $this->createMock(\Doctrine\Persistence\ManagerRegistry::class);
        $entityManager = $this->createMock(\Doctrine\ORM\EntityManager::class);
        $queryBuilder = $this->createMock(QueryBuilder::class);

        $managerRegistry->expects($this->any())
            ->method('getManagerForClass')
            ->willReturn($entityManager);

        // Create repository with real implementation but mock createQueryBuilder
        $repository = $this->getMockBuilder(TagRepository::class)
            ->setConstructorArgs([$managerRegistry])
            ->onlyMethods(['createQueryBuilder'])
            ->getMock();

        // Set up expectations for createQueryBuilder
        $repository->expects($this->once())
            ->method('createQueryBuilder')
            ->with('tag')
            ->willReturn($queryBuilder);

        $queryBuilder->expects($this->once())
            ->method('select')
            ->willReturnSelf();

        $queryBuilder->expects($this->once())
            ->method('orderBy')
            ->willReturnSelf();

        // Call queryAll which uses getOrCreateQueryBuilder internally
        $result = $repository->queryAll();

        // Assert result is the query builder
        $this->assertSame($queryBuilder, $result);
    }

    /**
     * Test the behavior of getOrCreateQueryBuilder method with an existing query builder
     */
    public function testGetOrCreateQueryBuilderWithExistingBuilder(): void
    {
        // Setup
        $managerRegistry = $this->createMock(\Doctrine\Persistence\ManagerRegistry::class);
        $existingQueryBuilder = $this->createMock(QueryBuilder::class);

        // Create repository
        $repository = new TagRepository($managerRegistry);

        // Use reflection to access the private method
        $reflectionMethod = new \ReflectionMethod(TagRepository::class, 'getOrCreateQueryBuilder');
        $reflectionMethod->setAccessible(true);

        // Call the private method with an existing query builder
        $result = $reflectionMethod->invoke($repository, $existingQueryBuilder);

        // Should return the same instance that was passed
        $this->assertSame($existingQueryBuilder, $result);
    }

    /**
     * Test the behavior of getOrCreateQueryBuilder method when creating a new query builder
     */
    public function testGetOrCreateQueryBuilderCreatesNewBuilder(): void
    {
        // Setup
        $managerRegistry = $this->createMock(\Doctrine\Persistence\ManagerRegistry::class);
        $entityManager = $this->createMock(\Doctrine\ORM\EntityManager::class);
        $newQueryBuilder = $this->createMock(QueryBuilder::class);

        $managerRegistry->expects($this->any())
            ->method('getManagerForClass')
            ->willReturn($entityManager);

        // Create repository with partial mock
        $repository = $this->getMockBuilder(TagRepository::class)
            ->setConstructorArgs([$managerRegistry])
            ->onlyMethods(['createQueryBuilder'])
            ->getMock();

        // Setup expectation for createQueryBuilder
        $repository->expects($this->once())
            ->method('createQueryBuilder')
            ->with('tag')
            ->willReturn($newQueryBuilder);

        // Use reflection to access the private method
        $reflectionMethod = new \ReflectionMethod(TagRepository::class, 'getOrCreateQueryBuilder');
        $reflectionMethod->setAccessible(true);

        // Call the private method with null (should create a new query builder)
        $result = $reflectionMethod->invoke($repository, null);

        // Should return the mocked query builder
        $this->assertSame($newQueryBuilder, $result);
    }

    /**
     * Test repository save method
     */
    public function testSaveMethodInRepository(): void
    {
        $tag = new Tag();
        $tag->setName('Repository Test Tag');

        $entityManager = $this->createMock(\Doctrine\ORM\EntityManager::class);
        $managerRegistry = $this->createMock(\Doctrine\Persistence\ManagerRegistry::class);

        $managerRegistry->expects($this->any())
            ->method('getManagerForClass')
            ->willReturn($entityManager);

        // Create repository with mocked dependencies
        $repository = new TagRepository($managerRegistry);

        // Set the entity manager through reflection
        $reflection = new \ReflectionProperty(TagRepository::class, '_em');
        $reflection->setAccessible(true);
        $reflection->setValue($repository, $entityManager);

        // Expect persist and flush to be called
        $entityManager->expects($this->once())
            ->method('persist')
            ->with($this->equalTo($tag));

        $entityManager->expects($this->once())
            ->method('flush');

        // Call the save method
        $repository->save($tag);
    }

    /**
     * Test repository delete method
     */
    public function testDeleteMethodInRepository(): void
    {
        $tag = new Tag();
        $tag->setName('Repository Delete Test Tag');

        $entityManager = $this->createMock(\Doctrine\ORM\EntityManager::class);
        $managerRegistry = $this->createMock(\Doctrine\Persistence\ManagerRegistry::class);

        $managerRegistry->expects($this->any())
            ->method('getManagerForClass')
            ->willReturn($entityManager);

        // Create repository with mocked dependencies
        $repository = new TagRepository($managerRegistry);

        // Set the entity manager through reflection
        $reflection = new \ReflectionProperty(TagRepository::class, '_em');
        $reflection->setAccessible(true);
        $reflection->setValue($repository, $entityManager);

        // Expect remove and flush to be called
        $entityManager->expects($this->once())
            ->method('remove')
            ->with($this->equalTo($tag));

        $entityManager->expects($this->once())
            ->method('flush');

        // Call the delete method
        $repository->delete($tag);
    }
}