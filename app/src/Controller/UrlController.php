<?php

namespace App\Controller;

use App\Entity\Url;
use App\Entity\UrlData;
use App\Entity\User;
use App\Entity\GuestUser;
use App\Form\Type\UrlBlockType;
use App\Form\Type\UrlType;
use App\Service\GuestUserServiceInterface;
use App\Service\UrlDataServiceInterface;
use App\Service\UrlServiceInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route('/url')]
class UrlController extends AbstractController
{
    private UrlServiceInterface $urlService;

    private UrlDataServiceInterface $urlDataService;


    private GuestUserServiceInterface $guestUserService;

    private TranslatorInterface $translator;

    public function __construct(UrlServiceInterface $urlService, UrlDataServiceInterface $urlDataService, TranslatorInterface $translator, GuestUserServiceInterface $guestUserService)
    {
        $this->urlService = $urlService;
        $this->urlDataService = $urlDataService;
        $this->translator = $translator;
        $this->guestUserService = $guestUserService;
    }// end __construct()

    #[Route(
        name: 'url_index',
        methods: 'GET'
    )]
    public function index(Request $request): Response
    {
        $filters = $this->getFilters($request);
        /*
            @var User $user
        */
        $user = $this->getUser();
        $pagination = $this->urlService->getPaginatedList(
            $request->query->getInt('page', 1),
            $user,
            $filters
        );

        return $this->render('url/index.html.twig', ['pagination' => $pagination]);
    }// end index()

    #[Route(
        '/list',
        name: 'list',
        methods: 'GET'
    )]
    public function list(Request $request): Response
    {
        $filters = $this->getFilters($request);
        $pagination = $this->urlService->getPaginatedListForAll(
            $request->query->getInt('page', 1),
            $filters
        );

        return $this->render('url/list.html.twig', ['pagination' => $pagination]);
    }// end list()

    #[Route('/{id}', name: 'url_show', requirements: ['id' => '[1-9]\d*'], methods: 'GET')]
    public function show(Url $url): Response
    {
        return $this->render('url/show.html.twig', ['url' => $url]);
    }// end show()

    #[Route(
        '/short/{shortName}',
        name: 'app_url_redirecttourl',
        methods: ['GET'],
    )]
    public function redirectToUrl(string $shortName): Response
    {
        $url = $this->urlService->findOneByShortName($shortName);

        if (!$url) {
            $this->addFlash('warning', $this->translator->trans('message.url_does_not_exist'));
        }
        else if (!$url->isIsBlocked()) {
            $urlData = new UrlData();
            $urlData->setVisitTime(new \DateTimeImmutable());
            $urlData->setUrl($url);

            $this->urlDataService->save($urlData);

            return new RedirectResponse($url->getLongName());
        } else if ($url->isIsBlocked() && $url->getBlockTime() > new \DateTimeImmutable()) {
            $this->addFlash('warning', $this->translator->trans('message.blocked_url'));

            return $this->redirectToRoute('list');
        }

        return $this->redirectToRoute('list');
    }

    #[Route(
        '/create',
        name: 'url_create',
        methods: 'GET|POST',
    )]
    public function create(Request $request): Response
    {
        /*
            @var User $user
        */
        $user = $this->getUser();
        $url = new Url();
        $url->setUsers($user);
        $form = $this->createForm(
            UrlType::class,
            $url,
            ['action' => $this->generateUrl('url_create')]
        );
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            if (!$this->getUser()) {
                $email = $form->get('email')->getData();
                $guestUser = new GuestUser();
                $guestUser->setEmail($email);
                $this->guestUserService->save($guestUser);
                $url->setGuestUser($guestUser);
            }

            $this->urlService->save($url);

            $this->addFlash('success', $this->translator->trans('message.created'));

            return $this->redirectToRoute('list');
        }

        return $this->render(
            'url/create.html.twig',
            ['form' => $form->createView()]
        );
    }// end create()

    #[Route('/{id}/delete', name: 'url_delete', requirements: ['id' => '[1-9]\d*'], methods: 'GET|DELETE')]
    #[IsGranted('DELETE', subject: 'url')]
    public function delete(Request $request, Url $url): Response
    {
        $form = $this->createForm(
            FormType::class,
            $url,
            [
                'method' => 'DELETE',
                'action' => $this->generateUrl(
                    'url_delete',
                    ['id' => $url->getId()]
                ),
            ]
        );
        $form->handleRequest($request);
        if ($request->isMethod('DELETE') && !$form->isSubmitted()) {
            $form->submit($request->request->get($form->getName()));
        }

        if ($form->isSubmitted() && $form->isValid()) {
            $this->urlDataService->deleteUrlVisits($url->getId());
            $this->urlService->delete($url);
            $this->addFlash('success', $this->translator->trans('message.deleted'));

            return $this->redirectToRoute('url_index');
        }

        return $this->render(
            'url/delete.html.twig',
            [
                'form' => $form->createView(),
                'url' => $url,
            ]
        );
    }// end delete()

    #[Route('/{id}/edit', name: 'url_edit', requirements: ['id' => '[1-9]\d*'], methods: 'GET|PUT')]
    #[IsGranted('EDIT', subject: 'url')]
    public function edit(Request $request, Url $url): Response
    {
        if ($url->isIsBlocked()) {
            $this->denyAccessUnlessGranted('ROLE_ADMIN');
        }

        $form = $this->createForm(
            UrlType::class,
            $url,
            [
                'method' => 'PUT',
                'action' => $this->generateUrl(
                    'url_edit',
                    ['id' => $url->getId()]
                ),
            ]
        );
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->urlService->save($url);
            $this->addFlash('success', $this->translator->trans('message.updated'));

            return $this->redirectToRoute('url_index');
        }

        return $this->render(
            'url/edit.html.twig',
            [
                'form' => $form->createView(),
                'url' => $url,
            ]
        );
    }// end edit()

    #[Route('/{id}/block', name: 'url_block', requirements: ['id' => '[1-9]\d*'], methods: 'GET|POST')]
    #[IsGranted('ROLE_ADMIN')]
    public function block(Request $request, Url $url): Response
    {
        $form = $this->createForm(
            UrlBlockType::class,
            $url,
            [
                'method' => 'POST',
                'action' => $this->generateUrl(
                    'url_block',
                    ['id' => $url->getId()]
                ),
            ]
        );
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $url->setIsBlocked(true);
            $this->urlService->save($url);
            $this->addFlash('success', $this->translator->trans('message.blocked'));

            return $this->redirectToRoute('list');
        }

        return $this->render(
            'url/block.html.twig',
            [
                'form' => $form->createView(),
                'url' => $url,
            ]
        );
    }// end block()

    #[Route('/{id}/unblock', name: 'url_unblock', requirements: ['id' => '[1-9]\d*'], methods: 'GET|POST')]
    #[IsGranted('ROLE_ADMIN')]
    public function unblock(Request $request, Url $url): Response
    {
        if ($url->getBlockTime() < new \DateTimeImmutable()) {
            $url->setIsBlocked(false);
            $url->setBlockTime(null);
            $this->urlService->save($url);
            $this->addFlash('success', $this->translator->trans('message.unblocked'));

            return $this->redirectToRoute('list');
        }

        $form = $this->createForm(
            FormType::class,
            $url,
            [
                'method' => 'POST',
                'action' => $this->generateUrl(
                    'url_unblock',
                    ['id' => $url->getId()]
                ),
            ]
        );
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $url->setBlockTime(null);
            $url->setIsBlocked(false);
            $this->urlService->save($url);
            $this->addFlash('success', $this->translator->trans('message.unblocked'));

            return $this->redirectToRoute('list');
        }

        return $this->render(
            'url/unblock.html.twig',
            [
                'form' => $form->createView(),
                'url' => $url,
            ]
        );
    }// end unblock()

    private function getFilters(Request $request): array
    {
        $filters = [];
        $filters['tag_id'] = $request->query->getInt('filters_tag_id');

        return $filters;
    }// end getFilters()
}// end class
