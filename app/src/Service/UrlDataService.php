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
    private UrlDataRepository $urlDataRepository;

    private PaginatorInterface $paginator;

    /**
     * Constructor.
     */
    public function __construct(UrlDataRepository $urlDataRepository, PaginatorInterface $paginator)
    {
        $this->urlDataRepository = $urlDataRepository;
        $this->paginator = $paginator;
    }

    public function save(UrlData $urlData): void
    {
        $this->urlDataRepository->save($urlData);
    }

    public function countVisits(int $page): PaginationInterface
    {
        return $this->paginator->paginate(
            $this->urlDataRepository->countVisits(),
            $page,
            UrlDataRepository::PAGINATOR_ITEMS_PER_PAGE
        );
    }

    public function deleteUrlVisits(int $id): void
    {
        $this->urlDataRepository->deleteUrlVisits($id);
    }
}
