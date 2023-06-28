<?php
/*
 * Url visited controller.
 */

namespace App\Controller;

use App\Service\UrlDataServiceInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class UrlDataController.
 */
#[Route('/popular')]
class UrlDataController extends AbstractController
{

    private UrlDataServiceInterface $urlDataService;


    public function __construct(UrlDataServiceInterface $urlDataService)
    {
        $this->urlDataService = $urlDataService;
    }

    /**
     * Most visited action.
     *
     * @param Request $request HTTP request
     *
     * @return Response HTTP response
     */
    #[Route(name: 'popular_index', methods: 'GET')]
    public function mostVisited(Request $request): Response
    {
        $pagination = $this->urlDataService->countAllVisitsForUrl(
            $request->query->getInt('page', 1)
        );

        return $this->render('url_visited/most_visited.html.twig', ['pagination' => $pagination]);
    }
}
