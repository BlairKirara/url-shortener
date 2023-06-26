<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/url_data')]
class UrlDataCotroller extends AbstractController
{
    public function index(): Response
    {
        return $this->render('url_data/index.html.twig');
    }
}
