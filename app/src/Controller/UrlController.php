<?php

namespace App\Controller;

use App\Entity\Url;
use App\Repository\UrlRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Knp\Component\Pager\PaginatorInterface;


#[Route('/url')]
class UrlController extends AbstractController
{


    #[Route(name: 'url_index', methods: 'GET')]
    public function index(Request $request, UrlRepository $urlRepository, PaginatorInterface $paginator): Response
    {
        $pagination = $paginator->paginate(
            $urlRepository->queryAll(),
            $request->query->getInt('page', 1),
            UrlRepository::PAGINATOR_ITEMS_PER_PAGE
        );
        return $this->render(
            'url/index.html.twig',
            ['pagination' => $pagination]
        );

    }

    #[Route(
        '/{id}',
        name: 'url_show',
        requirements: ['id' => '[1-9]\d*'],
        methods: 'GET',
    )]
    public function show(Url $url): Response
    {
        return $this->render(
            'url/show.html.twig',
            ['url' => $url]
        );
    }

}