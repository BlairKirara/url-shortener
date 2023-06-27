<?php

namespace App\Controller;

use App\Entity\Url;
use App\Entity\UrlData;
use App\Service\UrlDataServiceInterface;
use App\Service\UrlServiceInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route('/short')]
class UrlRedirectController extends AbstractController
{
    private UrlServiceInterface $urlService;

    private UrlDataServiceInterface $urlDataService;
    private TranslatorInterface $translator;

    public function __construct(UrlServiceInterface $urlService, UrlDataServiceInterface $urlDataService, TranslatorInterface $translator)
    {
        $this->urlService = $urlService;
        $this->urlDataService = $urlDataService;
        $this->translator = $translator;
    }

    #[Route(
        '/{shortName}',
        name: 'url_redirect_index',
        methods: ['GET'],
    )]
    public function index(string $shortName): Response
    {
        $url = $this->urlService->findOneByShortName($shortName);



            $urlData = new UrlData();
            $urlData->setVisitTime(new \DateTimeImmutable());
            $urlData->setUrl($url);

            $this->urlDataService->save($urlData);
            return new RedirectResponse($url->getLongName());



    }
}
