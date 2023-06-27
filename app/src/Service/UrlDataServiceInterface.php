<?php

namespace App\Service;
use App\Entity\UrlData;

interface UrlDataServiceInterface
{
    public function save(UrlData $urlData): void;

//    public function countUrlVisits(int $page): PaginationInterface;

//    public function deleteUrlVisits(int $id): void;

}