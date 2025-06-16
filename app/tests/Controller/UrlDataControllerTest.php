<?php

namespace App\Tests\Controller;

use App\Controller\UrlDataController;
use App\Service\UrlDataServiceInterface;
use Knp\Component\Pager\Pagination\PaginationInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Twig\Environment as TwigEnvironment;

class UrlDataControllerTest extends TestCase
{
    private UrlDataServiceInterface $urlDataService;
    private UrlDataController $controller;

    protected function setUp(): void
    {
        $this->urlDataService = $this->createMock(UrlDataServiceInterface::class);
        $this->controller = new UrlDataController($this->urlDataService);

        $twig = $this->createMock(TwigEnvironment::class);

        $twig->method('render')
            ->willReturn('<html>dummy content</html>');

        $container = $this->createMock(ContainerInterface::class);

        $container->method('has')
            ->willReturnCallback(fn($service) => $service === 'twig');

        $container->method('get')
            ->willReturnCallback(fn($service) => $service === 'twig' ? $twig : null);

        $this->controller->setContainer($container);
    }

    public function testVisitsCountReturnsResponseWithPagination()
    {
        $page = 2;

        $paginationMock = $this->createMock(PaginationInterface::class);

        $this->urlDataService
            ->expects($this->once())
            ->method('countVisits')
            ->with($page)
            ->willReturn($paginationMock);

        $request = new Request(['page' => $page]);

        $response = $this->controller->visitsCount($request);

        $this->assertInstanceOf(Response::class, $response);
        $this->assertStringContainsString('dummy content', $response->getContent());
    }

    public function testVisitsCountDefaultsToPageOne()
    {
        $paginationMock = $this->createMock(PaginationInterface::class);

        $this->urlDataService
            ->expects($this->once())
            ->method('countVisits')
            ->with(1)
            ->willReturn($paginationMock);

        $request = new Request(); // no page parameter

        $response = $this->controller->visitsCount($request);

        $this->assertInstanceOf(Response::class, $response);
    }


}
