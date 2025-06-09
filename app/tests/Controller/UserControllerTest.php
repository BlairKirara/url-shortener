<?php

namespace App\Tests\Controller;

use App\Controller\UserController;
use App\Entity\User;
use App\Form\Type\UserEmailType;
use App\Form\Type\UserPasswordType;
use App\Service\UserServiceInterface;
use Knp\Component\Pager\Pagination\PaginationInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class UserControllerTest extends TestCase
{
    private $userService;
    private $translator;
    private $passwordHasher;
    private $controller;

    protected function setUp(): void
    {
        $this->userService = $this->createMock(UserServiceInterface::class);
        $this->translator = $this->createMock(TranslatorInterface::class);
        $this->passwordHasher = $this->createMock(UserPasswordHasherInterface::class);

        $this->controller = $this->getMockBuilder(UserController::class)
            ->setConstructorArgs([$this->userService, $this->translator, $this->passwordHasher])
            ->onlyMethods(['createForm', 'addFlash', 'redirectToRoute', 'render'])
            ->getMock();
    }

    public function testIndexReturnsPaginationResponse()
    {
        $pagination = $this->createMock(PaginationInterface::class);

        $request = new Request(['page' => 3]);

        $this->userService->expects($this->once())
            ->method('getPaginatedList')
            ->with(3)
            ->willReturn($pagination);

        $this->controller->expects($this->once())
            ->method('render')
            ->with('user/index.html.twig', ['pagination' => $pagination])
            ->willReturn(new Response());

        $response = $this->controller->index($request);
        $this->assertInstanceOf(Response::class, $response);
    }

    public function testShowRendersUser()
    {
        $user = new User();

        $this->controller->expects($this->once())
            ->method('render')
            ->with('user/show.html.twig', ['user' => $user])
            ->willReturn(new Response());

        $response = $this->controller->show($user);
        $this->assertInstanceOf(Response::class, $response);
    }

    public function testEditPasswordSuccessfully()
    {
        $user = $this->createMock(User::class);
        $request = new Request();

        $form = $this->createMock(FormInterface::class);

        $form->expects($this->once())->method('handleRequest')->with($request);
        $form->expects($this->once())->method('isSubmitted')->willReturn(true);
        $form->expects($this->once())->method('isValid')->willReturn(true);

        $user->expects($this->once())->method('getPassword')->willReturn('raw-password');

        $this->passwordHasher->expects($this->once())
            ->method('hashPassword')
            ->with($user, 'raw-password')
            ->willReturn('hashed-password');

        $user->expects($this->once())->method('setPassword')->with('hashed-password');
        $this->userService->expects($this->once())->method('save')->with($user);

        $this->controller->expects($this->once())->method('addFlash')->with('success', $this->anything());
        $this->controller->expects($this->once())->method('redirectToRoute')->with('app_homepage')->willReturn(new RedirectResponse('/'));

        $this->controller->expects($this->once())
            ->method('createForm')
            ->with(UserPasswordType::class, $user, ['method' => 'PUT'])
            ->willReturn($form);

        $response = $this->controller->edit($request, $user);
        $this->assertInstanceOf(RedirectResponse::class, $response);
    }


    public function testEditPasswordFormNotSubmittedOrInvalid()
    {
        $user = $this->createMock(User::class);
        $request = new Request();

        $form = $this->createMock(FormInterface::class);
        $formView = $this->createMock(FormView::class);

        $form->expects($this->once())->method('handleRequest')->with($request);
        $form->expects($this->once())->method('isSubmitted')->willReturn(false);
        $form->expects($this->never())->method('isValid');
        $form->expects($this->once())->method('createView')->willReturn($formView);

        $this->controller->expects($this->once())
            ->method('createForm')
            ->with(UserPasswordType::class, $user, ['method' => 'PUT'])
            ->willReturn($form);

        $this->controller->expects($this->once())
            ->method('render')
            ->with('user/edit.html.twig', ['form' => $formView, 'user' => $user])
            ->willReturn(new Response());

        $response = $this->controller->edit($request, $user);
        $this->assertInstanceOf(Response::class, $response);
    }

    public function testEditEmailSuccessfully()
    {
        $user = $this->createMock(User::class);
        $request = new Request();

        $form = $this->createMock(FormInterface::class);

        $form->expects($this->once())->method('handleRequest')->with($request);
        $form->expects($this->once())->method('isSubmitted')->willReturn(true);
        $form->expects($this->once())->method('isValid')->willReturn(true);

        $this->userService->expects($this->once())->method('save')->with($user);
        $this->controller->expects($this->once())->method('addFlash')->with('success', $this->anything());
        $this->controller->expects($this->once())->method('redirectToRoute')->with('app_homepage')->willReturn(new RedirectResponse('/'));

        $this->controller->expects($this->once())
            ->method('createForm')
            ->with(UserEmailType::class, $user, ['method' => 'PUT'])
            ->willReturn($form);

        $response = $this->controller->editEmail($request, $user);
        $this->assertInstanceOf(RedirectResponse::class, $response);
    }


    public function testEditEmailFormNotSubmittedOrInvalid()
    {
        $user = $this->createMock(User::class);
        $request = new Request();

        $form = $this->createMock(FormInterface::class);
        $formView = $this->createMock(FormView::class);

        $form->expects($this->once())->method('handleRequest')->with($request);
        $form->expects($this->once())->method('isSubmitted')->willReturn(false);
        $form->expects($this->never())->method('isValid');
        $form->expects($this->once())->method('createView')->willReturn($formView);

        $this->controller->expects($this->once())
            ->method('createForm')
            ->with(UserEmailType::class, $user, ['method' => 'PUT'])
            ->willReturn($form);

        $this->controller->expects($this->once())
            ->method('render')
            ->with('user/edit_email.html.twig', ['form' => $formView, 'user' => $user])
            ->willReturn(new Response());

        $response = $this->controller->editEmail($request, $user);
        $this->assertInstanceOf(Response::class, $response);
    }
}
