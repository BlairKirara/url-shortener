<?php

namespace App\Controller;

use App\Service\UrlDataServiceInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


#[Route('/visits')]
class UrlDataController extends AbstractController
{

    private UrlDataServiceInterface $urlDataService;


    /**
     * @param UrlDataServiceInterface $urlDataService
     */
    public function __construct(UrlDataServiceInterface $urlDataService)
    {
        $this->urlDataService = $urlDataService;
    }

    /**
     * @param Request $request
     * @return Response
     */
    #[Route(name: 'visits_index', methods: 'GET')]
    public function visitsCount(Request $request): Response
    {
        $pagination = $this->urlDataService->countVisits(
            $request->query->getInt('page', 1)
        );

        return $this->render('url/url_visits.html.twig', ['pagination' => $pagination]);
    }
}
