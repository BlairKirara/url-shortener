<?php

namespace App\Service;

use App\Entity\UrlData;
use App\Repository\UrlDataRepository;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;

class UrlDataService implements UrlDataServiceInterface
{
    private UrlDataRepository $urlDataRepository;
    private PaginatorInterface $paginator;

    public function __construct(UrlDataRepository $urlDataRepository, PaginatorInterface $paginator)
    {
        $this->urlDataRepository = $urlDataRepository;
        $this->paginator = $paginator;
    }

    public function save(UrlData $urlData): void
    {
        $this->urlDataRepository->save($urlData);
    }

    public function countUrlData(int $page): PaginationInterface
    {
        $visits = $this->urlDataRepository->countUrlData();
        $itemsPerPage = UrlDataRepository::PAGINATOR_ITEMS_PER_PAGE;

        return $this->paginator->paginate($visits, $page, $itemsPerPage);
    }

    public function deleteUrlData(int $id): void
    {
        $this->urlDataRepository->deleteUrlData($id);
    }
}