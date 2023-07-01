<?php
/**
 * Url data service.
 */

namespace App\Service;

use App\Entity\UrlData;
use App\Repository\UrlDataRepository;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;

/**
 * Class UrlDataService.
 *
 * This class provides services related to URL data.
 */
class UrlDataService implements UrlDataServiceInterface
{
    private UrlDataRepository $urlDataRepository;
    private PaginatorInterface $paginator;

    /**
     * Constructor.
     *
     * @param UrlDataRepository $urlDataRepository The URL data repository
     * @param PaginatorInterface $paginator The paginator
     */
    public function __construct(UrlDataRepository $urlDataRepository, PaginatorInterface $paginator)
    {
        $this->urlDataRepository = $urlDataRepository;
        $this->paginator = $paginator;
    }

    /**
     * Saves URL data.
     *
     * @param UrlData $urlData The URL data to save
     */
    public function save(UrlData $urlData): void
    {
        $this->urlDataRepository->save($urlData);
    }

    /**
     * Counts visits and returns a paginated list.
     *
     * @param int $page The page number
     * @return PaginationInterface The paginated list of visit counts
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
     * Deletes URL visits by ID.
     *
     * @param int $id The ID of the URL visits to delete
     */
    public function deleteUrlVisits(int $id): void
    {
        $this->urlDataRepository->deleteUrlVisits($id);
    }
}
