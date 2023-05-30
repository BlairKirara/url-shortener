<?php
/**
 * Front controller.
 */

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class FrontController.
 */
class FrontController extends AbstractController
{
    /**
     * Index action.
     *
     * @return Response HTTP response
     */
    #[Route('/front', name: 'front_index', methods: 'GET')]
    public function index(): Response
    {
        return new Response('Front Page');
    }
}
