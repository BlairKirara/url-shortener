<?php
namespace App\Service;

use App\Entity\UrlData;
use Knp\Component\Pager\Pagination\PaginationInterface;

/**
 * Interface UrlDataServiceInterface.
 */
interface UrlDataServiceInterface
{

    public function save(UrlData $urlData): void;


    public function countAllVisitsForUrl(int $page): PaginationInterface;


    public function deleteAllVisitsForUrl(int $id): void;
}
