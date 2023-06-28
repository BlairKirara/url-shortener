<?php

namespace App\Service;

use App\Entity\UrlData;
use App\Repository\UrlDataRepository;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;

/**
 * Class urlDataService.
 */
class UrlDataService implements UrlDataServiceInterface
{
    /**
     * @var UrlDataRepository
     */
    private UrlDataRepository $urlDataRepository;

    /**
     * @var PaginatorInterface
     */
    private PaginatorInterface $paginator;

    /**
     * Constructor.
     *
     * @param UrlDataRepository $urlDataRepository
     * @param PaginatorInterface $paginator
     */
    public function __construct(UrlDataRepository $urlDataRepository, PaginatorInterface $paginator)
    {
        $this->urlDataRepository = $urlDataRepository;
        $this->paginator = $paginator;
    }

    /**
     * @param UrlData $urlData
     * @return void
     */
    public function save(UrlData $urlData): void
    {
        $this->urlDataRepository->save($urlData);
    }

    /**
     * @param int $page
     * @return PaginationInterface
     */
    public function countVisits(int $page): PaginationInterface
    {
        return $this->paginator->paginate(
            $this->urlDataRepository->countVisits(),
            $page,
            UrlDataRepository::PAGINATOR_ITEMS_PER_PAGE
        );
    }

    /**
     * @param int $id
     * @return void
     */
    public function deleteUrlVisits(int $id): void
    {
        $this->urlDataRepository->deleteUrlVisits($id);
    }
}
