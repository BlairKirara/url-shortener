<?php
/**
 * Home Controller.
 */

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class HomePageController.
 */
class HomePageController extends AbstractController
{
    /**
     * @return Response
     */
    #[Route(path: '/', name: 'app_homepage')]
    public function index(): Response
    {
        /* @var TYPE_NAME $this */
        return $this->render('home/index.html.twig');
    }
}
