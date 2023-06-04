<?php

namespace App\Controller;

use App\Entity\Url;
use App\Repository\UrlRepository;
use App\Service\UrlServiceInterface;
use App\Form\Type\UrlType;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/url')]
class UrlController extends AbstractController
{
    /**
     * Url repository.
     */
    private UrlServiceInterface $urlService;

    /**
     * Constructor.
     */
    public function __construct(UrlServiceInterface $urlService)
    {
        $this->urlService = $urlService;
    }

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

    /**
     * Create action.
     *
     * @param Request $request HTTP request
     *
     * @return Response HTTP response
     */
    #[Route(
        '/create',
        name: 'url_create',
        methods: 'GET|POST',
    )]
    public function create(Request $request): Response
    {
        $url = new Url();
        $form = $this->createForm(UrlType::class, $url);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->urlService->save($url);

            $this->addFlash(
                'success',
                $this->translator->trans('message.created_successfully')
            );

            return $this->redirectToRoute('url_index');
        }

        return $this->render(
            'url/create.html.twig',
            ['form' => $form->createView()]
        );
    }
}