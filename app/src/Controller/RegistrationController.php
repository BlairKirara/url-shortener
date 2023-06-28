<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\Type\RegistrationType;
use App\Service\UserServiceInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class RegistrationController extends AbstractController
{

    private TranslatorInterface $translator;
    private UserServiceInterface $userService;

    /**
     * @param UserServiceInterface $userService
     * @param TranslatorInterface $translator
     */
    public function __construct(UserServiceInterface $userService, TranslatorInterface $translator)
    {
        $this->translator = $translator;
        $this->userService = $userService;
    }// end __construct()

    /**
     * @param Request $request
     * @return Response
     */
    #[Route(
        path: '/register',
        name: 'app_register',
        methods: [
            'GET',
            'POST',
        ],
    )]
    public function register(Request $request): Response
    {
        $user = new User();
        $form = $this->createForm(RegistrationType::class, $user, ['method' => Request::METHOD_POST]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->userService->save($user);

            $this->addFlash('success', $this->translator->trans('message.registered_successfully'));

            return $this->redirectToRoute('app_login');
        }

        return $this->render(
            'registration/index.html.twig',
            ['form' => $form->createView()]
        );
    }// end register()
}// end class
