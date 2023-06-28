<?php
/**
 * Url Visited service.
 */

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


    public function countAllVisitsForUrl(int $page): PaginationInterface
    {
        return $this->paginator->paginate(
            $this->urlDataRepository->countAllVisitsForUrl(),
            $page,
            UrlDataRepository::PAGINATOR_ITEMS_PER_PAGE
        );
    }


    public function deleteAllVisitsForUrl(int $id): void
    {
        $this->urlDataRepository->deleteAllVisitsForUrl($id);
    }
}
