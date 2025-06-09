<?php

namespace App\Tests\Controller;

use App\Controller\TagController;
use App\Entity\Tag;
use App\Form\Type\TagType;
use App\Service\TagServiceInterface;
use Knp\Component\Pager\Pagination\PaginationInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class TagControllerTest extends TestCase
{
    private TagServiceInterface $tagService;
    private TranslatorInterface $translator;
    private TagController $controller;

    protected function setUp(): void
    {
        $this->tagService = $this->createMock(TagServiceInterface::class);
        $this->translator = $this->createMock(TranslatorInterface::class);

        $this->controller = $this->getMockBuilder(TagController::class)
            ->setConstructorArgs([$this->tagService, $this->translator])
            ->onlyMethods(['createForm', 'addFlash', 'redirectToRoute', 'render', 'generateUrl'])
            ->getMock();
    }

    public function testIndexRendersTemplateWithPagination(): void
    {
        $pagination = $this->createMock(PaginationInterface::class);

        $this->tagService->method('getPaginatedList')->willReturn($pagination);

        $this->controller->expects($this->once())
            ->method('render')
            ->with('tag/index.html.twig', ['pagination' => $pagination])
            ->willReturn(new Response('pagination'));

        $request = new Request();
        $response = $this->controller->index($request);

        $this->assertInstanceOf(Response::class, $response);
        $this->assertStringContainsString('pagination', $response->getContent());
    }

    public function testCreateHandlesValidForm(): void
    {
        $tag = new Tag();
        $form = $this->createMock(FormInterface::class);

        $this->controller->expects($this->once())
            ->method('createForm')
            ->with(TagType::class, $tag)
            ->willReturn($form);

        $form->expects($this->once())->method('handleRequest');
        $form->expects($this->once())->method('isSubmitted')->willReturn(true);
        $form->expects($this->once())->method('isValid')->willReturn(true);

        $this->tagService->expects($this->once())->method('save')->with($tag);

        $this->translator->expects($this->once())->method('trans')->with('message.created')->willReturn('Created');

        $this->controller->expects($this->once())->method('addFlash')->with('success', 'Created');
        $this->controller->expects($this->once())->method('redirectToRoute')->with('tag_index')->willReturn(new RedirectResponse('/tag'));

        $response = $this->controller->create(new Request());

        $this->assertInstanceOf(RedirectResponse::class, $response);
    }

    public function testCreateHandlesInvalidForm(): void
    {
        $tag = new Tag();
        $form = $this->createMock(FormInterface::class);
        $formView = $this->createMock(FormView::class);

        $this->controller->expects($this->once())
            ->method('createForm')
            ->with(TagType::class, $tag)
            ->willReturn($form);

        $form->expects($this->once())->method('handleRequest');
        $form->expects($this->once())->method('isSubmitted')->willReturn(true);
        $form->expects($this->once())->method('isValid')->willReturn(false);
        $form->expects($this->once())->method('createView')->willReturn($formView);

        $this->controller->expects($this->once())
            ->method('render')
            ->with('tag/create.html.twig', ['form' => $formView])
            ->willReturn(new Response('form invalid'));

        $response = $this->controller->create(new Request());

        $this->assertInstanceOf(Response::class, $response);
        $this->assertStringContainsString('form invalid', $response->getContent());
    }

    public function testDeleteHandlesValidForm(): void
    {
        $tag = $this->createMock(Tag::class);
        $tag->method('getId')->willReturn(123);

        $form = $this->createMock(FormInterface::class);
        $formView = $this->createMock(FormView::class);

        $this->controller->expects($this->once())
            ->method('generateUrl')
            ->with('tag_delete', ['id' => 123])
            ->willReturn('/tag/123/delete');

        $this->controller->expects($this->once())
            ->method('createForm')
            ->with(FormType::class, $tag, ['method' => 'DELETE', 'action' => '/tag/123/delete'])
            ->willReturn($form);

        $form->expects($this->once())->method('handleRequest');
        $form->expects($this->once())->method('isSubmitted')->willReturn(true);
        $form->expects($this->once())->method('isValid')->willReturn(true);

        $this->tagService->expects($this->once())->method('delete')->with($tag);
        $this->translator->expects($this->once())->method('trans')->with('message.deleted')->willReturn('Deleted');
        $this->controller->expects($this->once())->method('addFlash')->with('success', 'Deleted');
        $this->controller->expects($this->once())->method('redirectToRoute')->with('tag_index')->willReturn(new RedirectResponse('/tag'));

        $response = $this->controller->delete(new Request(), $tag);
        $this->assertInstanceOf(RedirectResponse::class, $response);
    }

    public function testEditHandlesValidForm(): void
    {
        $tag = $this->createMock(Tag::class);
        $tag->method('getId')->willReturn(456);

        $form = $this->createMock(FormInterface::class);

        $this->controller->expects($this->once())
            ->method('generateUrl')
            ->with('tag_edit', ['id' => 456])
            ->willReturn('/tag/456/edit');

        $this->controller->expects($this->once())
            ->method('createForm')
            ->with(TagType::class, $tag, ['method' => 'PUT', 'action' => '/tag/456/edit'])
            ->willReturn($form);

        $form->expects($this->once())->method('handleRequest');
        $form->expects($this->once())->method('isSubmitted')->willReturn(true);
        $form->expects($this->once())->method('isValid')->willReturn(true);

        $this->tagService->expects($this->once())->method('save')->with($tag);
        $this->translator->expects($this->once())->method('trans')->with('message.updated')->willReturn('Updated');
        $this->controller->expects($this->once())->method('addFlash')->with('success', 'Updated');
        $this->controller->expects($this->once())->method('redirectToRoute')->with('tag_index')->willReturn(new RedirectResponse('/tag'));

        $response = $this->controller->edit(new Request(), $tag);
        $this->assertInstanceOf(RedirectResponse::class, $response);
    }

    public function testEditHandlesInvalidForm(): void
    {
        $tag = $this->createMock(Tag::class);
        $tag->method('getId')->willReturn(789);

        $form = $this->createMock(FormInterface::class);
        $formView = $this->createMock(FormView::class);

        $this->controller->expects($this->once())
            ->method('generateUrl')
            ->with('tag_edit', ['id' => 789])
            ->willReturn('/tag/789/edit');

        $this->controller->expects($this->once())
            ->method('createForm')
            ->willReturn($form);

        $form->expects($this->once())->method('handleRequest');
        $form->expects($this->once())->method('isSubmitted')->willReturn(true);
        $form->expects($this->once())->method('isValid')->willReturn(false);
        $form->expects($this->once())->method('createView')->willReturn($formView);

        $this->controller->expects($this->once())
            ->method('render')
            ->with('tag/edit.html.twig', ['form' => $formView, 'tag' => $tag])
            ->willReturn(new Response('invalid edit'));

        $response = $this->controller->edit(new Request(), $tag);
        $this->assertInstanceOf(Response::class, $response);
        $this->assertStringContainsString('invalid edit', $response->getContent());
    }

    public function testShowRendersTag(): void
    {
        $tag = new Tag();

        $this->controller->expects($this->once())
            ->method('render')
            ->with('tag/show.html.twig', ['tag' => $tag])
            ->willReturn(new Response('show tag'));

        $response = $this->controller->show($tag);
        $this->assertInstanceOf(Response::class, $response);
        $this->assertStringContainsString('show tag', $response->getContent());
    }
}
