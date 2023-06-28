<?php

namespace App\Service;

use App\Entity\Tag;
use Knp\Component\Pager\Pagination\PaginationInterface;


interface TagServiceInterface
{

    public function getPaginatedList(int $page): PaginationInterface;


    public function save(Tag $tag): void;


    public function delete(Tag $tag): void;


    public function findOneByName(string $name): ?Tag;
}
