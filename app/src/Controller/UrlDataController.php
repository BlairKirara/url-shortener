<?php
/**
 * Url data controller.
 */

namespace App\Controller;

use App\Service\UrlDataServiceInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class UrlDataController.
 * Controller for managing URL visits.
 */
#[Route('/visits')]
class UrlDataController extends AbstractController
{
    private UrlDataServiceInterface $urlDataService;

    /**
     * Constructor.
     * Injects the UrlDataServiceInterface dependency.
     *
     * @param UrlDataServiceInterface $urlDataService the URL data service
     */
    public function __construct(UrlDataServiceInterface $urlDataService)
    {
        $this->urlDataService = $urlDataService;
    }

    /**
     * Displays the visits count for URLs.
     *
     * @param Request $request the HTTP request object
     *
     * @return Response the response object
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
