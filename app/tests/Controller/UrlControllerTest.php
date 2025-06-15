<?php

namespace App\Tests\Controller;

use App\Controller\UrlController;
use App\Entity\GuestUser;
use App\Entity\Url;
use App\Entity\UrlData;
use App\Entity\User;
use App\Form\Type\UrlBlockType;
use App\Form\Type\UrlType;
use App\Service\GuestUserServiceInterface;
use App\Service\UrlDataServiceInterface;
use App\Service\UrlServiceInterface;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use Knp\Component\Pager\Pagination\PaginationInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Class UrlControllerTest.
 *
 * Tests for the UrlController.
 */
class UrlControllerTest extends TestCase
{
    /**
     * URL service.
     */
    private UrlServiceInterface $urlService;

    /**
     * URL data service.
     */
    private UrlDataServiceInterface $urlDataService;

    /**
     * Translator.
     */
    private TranslatorInterface $translator;

    /**
     * Entity manager.
     */
    private EntityManagerInterface $entityManager;

    /**
     * Guest user service.
     */
    private GuestUserServiceInterface $guestUserService;

    /**
     * URL controller.
     */
    private UrlController $urlController;

    /**
     * Security.
     */
    private Security $security;

    /**
     * Authorization checker.
     */
    private AuthorizationCheckerInterface $authorizationChecker;

    /**
     * Form factory.
     */
    private FormFactoryInterface $formFactory;

    /**
     * URL generator.
     */
    private UrlGeneratorInterface $urlGenerator;

    /**
     * Session.
     */
    private Session $session;

    /**
     * Flash bag.
     */
    private FlashBagInterface $flashBag;

    /**
     * Set up test environment.
     */
    protected function setUp(): void
    {
        $this->urlService = $this->createMock(UrlServiceInterface::class);
        $this->urlDataService = $this->createMock(UrlDataServiceInterface::class);
        $this->translator = $this->createMock(TranslatorInterface::class);
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->guestUserService = $this->createMock(GuestUserServiceInterface::class);
        $this->security = $this->createMock(Security::class);
        $this->authorizationChecker = $this->createMock(AuthorizationCheckerInterface::class);
        $this->formFactory = $this->createMock(FormFactoryInterface::class);
        $this->urlGenerator = $this->createMock(UrlGeneratorInterface::class);
        
        // Set up session and flash bag
        $this->flashBag = $this->createMock(FlashBagInterface::class);
        $this->session = $this->createMock(Session::class);
        $this->session->method('getFlashBag')->willReturn($this->flashBag);

        // Create controller instance
        $this->urlController = new UrlController(
            $this->urlService,
            $this->urlDataService,
            $this->translator,
            $this->entityManager,
            $this->guestUserService
        );

        // Set required properties on controller using reflection
        $reflection = new \ReflectionClass($this->urlController);
        
        $containerProperty = $reflection->getProperty('container');
        $containerProperty->setAccessible(true);
        
        $container = $this->getMockBuilder('Symfony\Component\DependencyInjection\ContainerInterface')
            ->disableOriginalConstructor()
            ->getMock();
        
        $container->method('get')
            ->willReturnCallback(function ($serviceName) {
                switch ($serviceName) {
                    case 'security.authorization_checker':
                        return $this->authorizationChecker;
                    case 'form.factory':
                        return $this->formFactory;
                    case 'router':
                        return $this->urlGenerator;
                    case 'session':
                        return $this->session;
                    case 'security.helper':
                        return $this->security;
                    default:
                        return null;
                }
            });
        
        $containerProperty->setValue($this->urlController, $container);
    }

    /**
     * Test index method.
     */
    public function testIndex(): void
    {
        // Given
        $user = $this->createMock(User::class);
        $pagination = $this->createMock(PaginationInterface::class);
        $request = new Request();
        $request->query->set('page', 1);
        $request->query->set('filters_tag_id', 2);

        // Set up security to return a user
        $this->security->method('getUser')->willReturn($user);

        // Set up URL service to return pagination
        $this->urlService->expects($this->once())
            ->method('getPaginatedList')
            ->with(1, $user, ['tag_id' => 2])
            ->willReturn($pagination);

        // Mock render method
        $this->urlController = $this->getMockBuilder(UrlController::class)
            ->setConstructorArgs([
                $this->urlService,
                $this->urlDataService,
                $this->translator,
                $this->entityManager,
                $this->guestUserService
            ])
            ->onlyMethods(['render', 'getUser'])
            ->getMock();

        $this->urlController->method('getUser')->willReturn($user);

        $this->urlController->expects($this->once())
            ->method('render')
            ->with(
                'url/index.html.twig',
                ['pagination' => $pagination]
            )
            ->willReturn($this->createMock(Response::class));

        // When
        $result = $this->urlController->index($request);

        // Then
        $this->assertInstanceOf(Response::class, $result);
    }

    /**
     * Test list method.
     */
    public function testList(): void
    {
        // Given
        $pagination = $this->createMock(PaginationInterface::class);
        $request = new Request();
        $request->query->set('page', 1);
        $request->query->set('filters_tag_id', 2);

        // Set up URL service to return pagination
        $this->urlService->expects($this->once())
            ->method('getPaginatedListForAll')
            ->with(1, ['tag_id' => 2])
            ->willReturn($pagination);

        // Mock render method
        $this->urlController = $this->getMockBuilder(UrlController::class)
            ->setConstructorArgs([
                $this->urlService,
                $this->urlDataService,
                $this->translator,
                $this->entityManager,
                $this->guestUserService
            ])
            ->onlyMethods(['render'])
            ->getMock();

        $this->urlController->expects($this->once())
            ->method('render')
            ->with(
                'url/list.html.twig',
                ['pagination' => $pagination]
            )
            ->willReturn($this->createMock(Response::class));

        // When
        $result = $this->urlController->list($request);

        // Then
        $this->assertInstanceOf(Response::class, $result);
    }

    /**
     * Test show method.
     */
    public function testShow(): void
    {
        // Given
        $url = new Url();
        $url->setLongName('https://example.com');
        $url->setShortName('abc123');

        // Mock render method
        $this->urlController = $this->getMockBuilder(UrlController::class)
            ->setConstructorArgs([
                $this->urlService,
                $this->urlDataService,
                $this->translator,
                $this->entityManager,
                $this->guestUserService
            ])
            ->onlyMethods(['render'])
            ->getMock();

        $this->urlController->expects($this->once())
            ->method('render')
            ->with(
                'url/show.html.twig',
                ['url' => $url]
            )
            ->willReturn($this->createMock(Response::class));

        // When
        $result = $this->urlController->show($url);

        // Then
        $this->assertInstanceOf(Response::class, $result);
    }

    /**
     * Test redirectToUrl method with valid, unblocked URL.
     */
    public function testRedirectToUrlWithValidUnblockedUrl(): void
    {
        // Given
        $shortName = 'abc123';
        $url = new Url();
        $url->setLongName('https://example.com');
        $url->setShortName($shortName);
        $url->setIsBlocked(false);

        // Set up URL service to return a URL
        $this->urlService->expects($this->once())
            ->method('findOneByShortName')
            ->with($shortName)
            ->willReturn($url);

        // Expect URL data service to save a visit
        $this->urlDataService->expects($this->once())
            ->method('save')
            ->with($this->callback(function ($urlData) use ($url) {
                return $urlData instanceof UrlData && $urlData->getUrl() === $url;
            }));

        // Mock generateUrl method to prevent null reference
        $this->urlController = $this->getMockBuilder(UrlController::class)
            ->setConstructorArgs([
                $this->urlService,
                $this->urlDataService,
                $this->translator,
                $this->entityManager,
                $this->guestUserService
            ])
            ->onlyMethods(['generateUrl'])
            ->getMock();
            
        $this->urlController->method('generateUrl')
            ->willReturn('/some/url');

        // When
        $result = $this->urlController->redirectToUrl($shortName);

        // Then
        $this->assertInstanceOf(RedirectResponse::class, $result);
        $this->assertEquals('https://example.com', $result->getTargetUrl());
    }

    /**
     * Test redirectToUrl method with blocked URL.
     */
    public function testRedirectToUrlWithBlockedUrl(): void
    {
        // Given
        $shortName = 'abc123';
        $url = new Url();
        $url->setLongName('https://example.com');
        $url->setShortName($shortName);
        $url->setIsBlocked(true);
        $url->setBlockTime(new \DateTimeImmutable('+1 day')); // Block time in the future

        // Set up URL service to return a URL
        $this->urlService->expects($this->once())
            ->method('findOneByShortName')
            ->with($shortName)
            ->willReturn($url);

        // Expect translator to translate the blocked message
        $this->translator->expects($this->once())
            ->method('trans')
            ->with('message.blocked_url')
            ->willReturn('This URL is blocked');

        // Mock the controller with specific methods we need to control
        $this->urlController = $this->getMockBuilder(UrlController::class)
            ->setConstructorArgs([
                $this->urlService,
                $this->urlDataService,
                $this->translator,
                $this->entityManager,
                $this->guestUserService
            ])
            ->onlyMethods(['redirectToRoute', 'addFlash'])
            ->getMock();
            
        // Set expectations for addFlash to be called
        $this->urlController->expects($this->once())
            ->method('addFlash')
            ->with('warning', 'This URL is blocked');
            
        // Set expectations for redirectToRoute
        $this->urlController->expects($this->once())
            ->method('redirectToRoute')
            ->with('list')
            ->willReturn(new RedirectResponse('/list'));

        // When
        $result = $this->urlController->redirectToUrl($shortName);

        // Then
        $this->assertInstanceOf(RedirectResponse::class, $result);
        $this->assertEquals('/list', $result->getTargetUrl());
    }

    /**
     * Test delete method.
     */
    public function testDelete(): void
    {
        // Given
        $request = new Request();
        $url = new Url();
        $url->setLongName('https://example.com');
        $url->setShortName('abc123');
        $url->setIsBlocked(false);
        
        // Use reflection to set the ID property
        $reflection = new \ReflectionClass($url);
        $property = $reflection->getProperty('id');
        $property->setAccessible(true);
        $property->setValue($url, 1);
        
        $form = $this->createMock(FormInterface::class);
        $formView = $this->createMock(FormView::class);
        
        // Mock controller methods
        $this->urlController = $this->getMockBuilder(UrlController::class)
            ->setConstructorArgs([
                $this->urlService,
                $this->urlDataService,
                $this->translator,
                $this->entityManager,
                $this->guestUserService
            ])
            ->onlyMethods(['createForm', 'render', 'generateUrl', 'isGranted', 'redirectToRoute', 'addFlash'])
            ->getMock();
    
        // Since the URL is not blocked, we won't check ROLE_ADMIN
        // But we need to account for the check on isIsBlocked, which will evaluate to false
        
        // isGranted is called ONCE in the if condition: if ($url->isIsBlocked() && !$this->isGranted('ROLE_ADMIN'))
        // Since $url->isIsBlocked() is false, the second part (!$this->isGranted('ROLE_ADMIN')) is not evaluated
        // due to short-circuit evaluation in PHP, so isGranted won't be called.
    
        // Let's explicitly set the expectation that isGranted will NOT be called
        $this->urlController->expects($this->never())
            ->method('isGranted')
            ->with('ROLE_ADMIN');
        
        $this->urlController->method('createForm')
            ->willReturn($form);
        
        $this->urlController->method('generateUrl')
            ->willReturn('/url/1/delete');
        
        // Set up form methods
        $form->expects($this->once())
            ->method('handleRequest')
            ->with($request);
        
        $form->expects($this->once())
            ->method('isSubmitted')
            ->willReturn(false);
        
        $form->expects($this->once())
            ->method('createView')
            ->willReturn($formView);
        
        $this->urlController->expects($this->once())
            ->method('render')
            ->with(
                'url/delete.html.twig',
                [
                    'form' => $formView,
                    'url' => $url,
                ]
            )
            ->willReturn($this->createMock(Response::class));

        // When
        $result = $this->urlController->delete($request, $url);

        // Then
        $this->assertInstanceOf(Response::class, $result);
    }

    /**
     * Test create method.
     */
    public function testCreate(): void
    {
        // Given
        $request = new Request();
        $form = $this->createMock(FormInterface::class);
        $formView = $this->createMock(FormView::class);
        
        // Create a User mock
        $user = $this->createMock(User::class);
        
        // Mock controller methods
        $this->urlController = $this->getMockBuilder(UrlController::class)
            ->setConstructorArgs([
                $this->urlService,
                $this->urlDataService,
                $this->translator,
                $this->entityManager,
                $this->guestUserService
            ])
            ->onlyMethods(['getUser', 'createForm', 'render', 'generateUrl'])
            ->getMock();
        
        $this->urlController->method('getUser')
            ->willReturn($user);
        
        // Expect createForm to be called once with the correct parameters
        $this->urlController->expects($this->once())
            ->method('createForm')
            ->with(
                UrlType::class,
                $this->callback(function ($url) use ($user) {
                    return $url instanceof Url && $url->getUsers() === $user;
                }),
                ['action' => '/url/create']
            )
            ->willReturn($form);
        
        $this->urlController->method('generateUrl')
            ->with('url_create')
            ->willReturn('/url/create');
        
        // Set up form methods
        $form->expects($this->once())
            ->method('handleRequest')
            ->with($request);
        
        $form->expects($this->once())
            ->method('isSubmitted')
            ->willReturn(false);
        
        $form->expects($this->once())
            ->method('createView')
            ->willReturn($formView);
        
        $this->urlController->expects($this->once())
            ->method('render')
            ->with(
                'url/create.html.twig',
                ['form' => $formView]
            )
            ->willReturn($this->createMock(Response::class));

        // When
        $result = $this->urlController->create($request);

        // Then
        $this->assertInstanceOf(Response::class, $result);
    }

    /**
     * Tear down test environment.
     */
    protected function tearDown(): void
    {
        unset(
            $this->urlService,
            $this->urlDataService,
            $this->translator,
            $this->entityManager,
            $this->guestUserService,
            $this->urlController,
            $this->security,
            $this->authorizationChecker,
            $this->formFactory,
            $this->urlGenerator,
            $this->session,
            $this->flashBag
        );
    }
}