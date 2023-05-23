<?php

namespace App\Controller;

use App\Repository\UrlRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
#[Route('/url')]
class UrlController extends AbstractController
{

    #[Route(
    name: 'url_index',
    methods: 'GET'
)]
    public function index(UrlRepository $repository): Response
{
    $urls = $repository->findAll();

    return $this->render(
        'url/index.html.twig',
        ['urls' => $urls]
    );
}

}