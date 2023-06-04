<?php
/**
 * Tags controller.
 */

namespace App\Controller;

use App\Entity\Tags;
use App\Service\TagsServiceInterface;
use App\Form\Type\TagsType;
use Symfony\Contracts\Translation\TranslatorInterface;
use App\Service\UrlServiceInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class TagsController.
 */
#[Route('/tags')]
class TagsController extends AbstractController
{
    /**
     * Tags service.
     */
    private TagsServiceInterface $tagsService;

    /**
     * Translator.
     *
     * @var TranslatorInterface
     */
    private TranslatorInterface $translator;

/**
* Constructor.
*
* @param TagsServiceInterface $urlService Tags service
* @param TranslatorInterface      $translator  Translator
*/
    public function __construct(TagsServiceInterface $tagsService, TranslatorInterface $translator)
    {
        $this->tagsService = $tagsService;
        $this->translator = $translator;
    }

    /**
     * Index action.
     *
     * @param Request $request HTTP Request
     *
     * @return Response HTTP response
     */
    #[Route(name: 'tags_index', methods: 'GET')]
    public function index(Request $request): Response
    {
        $pagination = $this->tagsService->getPaginatedList(
            $request->query->getInt('page', 1)
        );

        return $this->render('tags/index.html.twig', ['pagination' => $pagination]);
    }

    /**
     * Show action.
     *
     * @param Tags $tags Tags
     *
     * @return Response HTTP response
     */
    #[Route(
        '/{id}',
        name: 'tags_show',
        requirements: ['id' => '[1-9]\d*'],
        methods: 'GET'
    )]
    public function show(Tags $tags): Response
    {
        return $this->render('tags/show.html.twig', ['tags' => $tags]);
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
        name: 'tags_create',
        methods: 'GET|POST',
    )]
    public function create(Request $request): Response
    {
        $tags = new Tags();
        $form = $this->createForm(TagsType::class, $tags);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->tagsService->save($tags);

            $this->addFlash(
                'success',
                $this->translator->trans('message.created_successfully')
            );

            return $this->redirectToRoute('tags_index');
        }

        return $this->render(
            'tags/create.html.twig',
            ['form' => $form->createView()]
        );
    }

    /**
     * Edit action.
     *
     * @param Request  $request  HTTP request
     * @param Tags $tags Tags entity
     *
     * @return Response HTTP response
     */
    #[Route('/{id}/edit', name: 'tags_edit', requirements: ['id' => '[1-9]\d*'], methods: 'GET|PUT')]
    public function edit(Request $request, Tags $tags): Response
    {
        $form = $this->createForm(
            TagsType::class,
            $tags,
            [
                'method' => 'PUT',
                'action' => $this->generateUrl('tags_edit', ['id' => $tags->getId()]),
            ]
        );
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->tagsService->save($tags);

            $this->addFlash(
                'success',
                $this->translator->trans('message.edited_successfully')
            );

            return $this->redirectToRoute('tags_index');
        }

        return $this->render(
            'tags/edit.html.twig',
            [
                'form' => $form->createView(),
                'tags' => $tags,
            ]
        );
    }

    /**
     * Delete action.
     *
     * @param Request  $request  HTTP request
     * @param Tags $tags Tags entity
     *
     * @return Response HTTP response
     */
    #[Route('/{id}/delete', name: 'tags_delete', requirements: ['id' => '[1-9]\d*'], methods: 'GET|DELETE')]
    public function delete(Request $request, Tags $tags): Response
    {
        $form = $this->createForm(TagsType::class, $tags, [
            'method' => 'DELETE',
            'action' => $this->generateUrl('tags_delete', ['id' => $tags->getId()]),
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->tagsService->delete($tags);

            $this->addFlash(
                'success',
                $this->translator->trans('message.deleted_successfully')
            );

            return $this->redirectToRoute('tags_index');
        }

        return $this->render(
            'tags/delete.html.twig',
            [
                'form' => $form->createView(),
                'tags' => $tags,
            ]
        );
    }
}
