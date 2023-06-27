<?php

namespace App\Service;
use App\Repository\UrlDataRepository;
use App\Entity\UrlData;

class UrlDataService implements UrlDataServiceInterface
{
    private UrlDataRepository $urlDataRepository;

    public function __construct(urlDataRepository $urlDataRepository)
    {
        $this->urlDataRepository = $urlDataRepository;
    }

    public function save(UrlData $urlData): void
    {
        $this->urlDataRepository->save($urlData);
    }


}