<?php

namespace App\Tests\Controller;

use App\Controller\RegistrationController;
use App\Entity\User;
use App\Form\Type\RegistrationType;
use App\Service\UserServiceInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class RegistrationControllerTest extends TestCase
{
    private UserServiceInterface $userServiceMock;
    private TranslatorInterface $translatorMock;
    private RegistrationController $controller;

    protected function setUp(): void
    {
        $this->userServiceMock = $this->createMock(UserServiceInterface::class);
        $this->translatorMock = $this->createMock(TranslatorInterface::class);

        $this->controller = $this->getMockBuilder(RegistrationController::class)
            ->setConstructorArgs([$this->userServiceMock, $this->translatorMock])
            ->onlyMethods(['createForm', 'addFlash', 'redirectToRoute', 'render'])
            ->getMock();
    }

    public function testRegisterFailsWithInvalidData(): void
    {
        $formMock = $this->createMock(FormInterface::class);
        $formViewMock = $this->createMock(FormView::class);

        $this->controller->expects($this->once())
            ->method('createForm')
            ->willReturn($formMock);

        $formMock->expects($this->once())
            ->method('handleRequest');

        $formMock->expects($this->once())
            ->method('isSubmitted')
            ->willReturn(true);

        $formMock->expects($this->once())
            ->method('isValid')
            ->willReturn(false);

        $formMock->expects($this->once())
            ->method('createView')
            ->willReturn($formViewMock);

        $this->controller->expects($this->once())
            ->method('render')
            ->with('registration/index.html.twig', ['form' => $formViewMock])
            ->willReturn(new Response('form with errors'));

        $response = $this->controller->register(new Request([], []));

        $this->assertInstanceOf(Response::class, $response);
        $this->assertStringContainsString('form with errors', $response->getContent());
    }

    public function testRegisterSucceedsWithValidData(): void
    {
        $user = new User();

        $formMock = $this->createMock(FormInterface::class);

        $this->controller->expects($this->once())
            ->method('createForm')
            ->willReturn($formMock);

        $formMock->expects($this->once())
            ->method('handleRequest');

        $formMock->expects($this->once())
            ->method('isSubmitted')
            ->willReturn(true);

        $formMock->expects($this->once())
            ->method('isValid')
            ->willReturn(true);

        $this->userServiceMock->expects($this->once())
            ->method('save')
            ->with($this->isInstanceOf(User::class));

        $this->translatorMock->expects($this->once())
            ->method('trans')
            ->with('message.registered')
            ->willReturn('Registered successfully');

        $this->controller->expects($this->once())
            ->method('addFlash')
            ->with('success', 'Registered successfully');

        $this->controller->expects($this->once())
            ->method('redirectToRoute')
            ->with('app_login')
            ->willReturn(new RedirectResponse('/login'));

        $response = $this->controller->register(new Request([], [
            'user' => [
                'email' => 'jan@example.com',
                'password' => [
                    'first' => 'password123',
                    'second' => 'password123',
                ],
            ],
        ]));

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertEquals(302, $response->getStatusCode());
    }

}
