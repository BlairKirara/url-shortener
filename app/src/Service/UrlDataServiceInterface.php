<?php
namespace App\Service;

use App\Entity\UrlData;
use Knp\Component\Pager\Pagination\PaginationInterface;


interface UrlDataServiceInterface
{

    public function save(UrlData $urlData): void;


    public function countVisits(int $page): PaginationInterface;


    public function deleteUrlVisits(int $id): void;
}
