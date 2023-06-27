<?php
/**
 * UrlData controller.
 */

namespace App\Controller;

use App\Entity\UrlData;
use App\Repository\UrlDataRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class UrlDataController.
 */
#[Route('/visits')]
class UrlDataController extends AbstractController
{
    /**
     * Index action.
     *
     * @param Request            $request        HTTP Request
     * @param UrlDataRepository     $urlDataRepository UrlData repository
     * @param PaginatorInterface $paginator      Paginator
     *
     * @return Response HTTP response
     */
    #[Route(name: 'url_data_index', methods: 'GET')]
    public function index(Request $request, UrlDataRepository $urlDataRepository, PaginatorInterface $paginator): Response
    {
        $pagination = $paginator->paginate(
            $urlDataRepository->queryAll(),
            $request->query->getInt('page', 1),
            UrlDataRepository::PAGINATOR_ITEMS_PER_PAGE
        );

        return $this->render('url_data/index.html.twig', ['pagination' => $pagination]);
    }

    /**
     * Show action.
     *
     * @param UrlData $url_data UrlData entity
     *
     * @return Response HTTP response
     */
    #[Route(
        '/{id}',
        name: 'url_data_show',
        requirements: ['id' => '[1-9]\d*'],
        methods: 'GET',
    )]
    public function show(UrlData $url_data): Response
    {
        return $this->render(
            'url_data/show.html.twig',
            ['url_data' => $url_data]
        );
    }
}
