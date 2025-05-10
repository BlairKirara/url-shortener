<?php

/**
 * User controller.
 */

namespace App\Controller;

use App\Entity\User;
use App\Form\Type\UserEmailType;
use App\Form\Type\UserPasswordType;
use App\Service\UserServiceInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/**
 * Class UserController.
 */
#[Route('/user')]
class UserController extends AbstractController
{
    /**
     * Constructor.
     *
     * @param UserServiceInterface        $userService    the user service
     * @param TranslatorInterface         $translator     the translator
     * @param UserPasswordHasherInterface $passwordHasher the password hasher
     */
    public function __construct(private readonly UserServiceInterface $userService, private readonly TranslatorInterface $translator, private readonly UserPasswordHasherInterface $passwordHasher)
    {
    }
    /**
     * User index page.
     *
     * @param Request $request the request object
     *
     * @return Response the response object
     *
     *
     * @IsGranted("ROLE_ADMIN")
     */
    #[Route(name: 'user_index', methods: 'GET')]
    public function index(Request $request): Response
    {
        $pagination = $this->userService->getPaginatedList($request->query->getInt('page', 1));

        return $this->render(
            'user/index.html.twig',
            ['pagination' => $pagination]
        );
    }
    /**
     * Show user details.
     *
     * @param User $user the user entity
     *
     * @return Response the response object
     *
     *
     * @IsGranted("VIEW", subject="user")
     */
    #[Route(path: '/{id}', name: 'user_show', requirements: ['id' => '[1-9]\d*'], methods: ['GET'])]
    public function show(User $user): Response
    {
        return $this->render(
            'user/show.html.twig',
            ['user' => $user]
        );
    }
    /**
     * Edit user password.
     *
     * @param Request $request the request object
     * @param User    $user    the user entity
     *
     * @return Response the response object
     *
     *
     * @IsGranted("EDIT_USER", subject="user")
     */
    #[Route(path: '/{id}/edit/password', name: 'user_edit', requirements: ['id' => '[1-9]\d*'], methods: ['GET', 'PUT'])]
    public function edit(Request $request, User $user): Response
    {
        $form = $this->createForm(UserPasswordType::class, $user, ['method' => 'PUT']);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $password = $this->passwordHasher->hashPassword($user, $user->getPassword());
            $user->setPassword($password);
            $this->userService->save($user);
            $this->addFlash('success', $this->translator->trans('message.updated'));

            return $this->redirectToRoute('app_homepage');
        }

        return $this->render(
            'user/edit.html.twig',
            [
                'form' => $form->createView(),
                'user' => $user,
            ]
        );
    }
    /**
     * Edit user email.
     *
     * @param Request $request the request object
     * @param User    $user    the user entity
     *
     * @return Response the response object
     *
     *
     * @IsGranted("EDIT_USER", subject="user")
     */
    #[Route(path: '/{id}/edit/email', name: 'user_edit_email', requirements: ['id' => '[1-9]\d*'], methods: ['GET', 'PUT'])]
    public function editEmail(Request $request, User $user): Response
    {
        $form = $this->createForm(UserEmailType::class, $user, ['method' => 'PUT']);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->userService->save($user);
            $this->addFlash('success', $this->translator->trans('message.updated'));

            return $this->redirectToRoute('app_homepage');
        }

        return $this->render(
            'user/edit_email.html.twig',
            [
                'form' => $form->createView(),
                'user' => $user,
            ]
        );
    }
}
