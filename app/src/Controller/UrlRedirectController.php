<?php

namespace App\Controller;

use App\Entity\Url;
use App\Service\UrlServiceInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route('/r')]
class UrlRedirectController extends AbstractController
{
    private UrlServiceInterface $urlService;
    private TranslatorInterface $translator;

    public function __construct(UrlServiceInterface $urlService, TranslatorInterface $translator)
    {
        $this->urlService = $urlService;
        $this->translator = $translator;
    }

    #[Route(
        '/{shortUrl}',
        name: 'url_redirect_index',
        methods: ['GET'],
    )]
    public function index(string $shortUrl): Response
    {
        $url = $this->urlService->findOneByShortUrl($shortUrl);

        if (!$url) {
            throw $this->createNotFoundException($this->translator->trans('message.url_not_found'));
        }

        if ($url->isBlocked() && $url->getBlockExpiration() < new \DateTimeImmutable()) {
            $url->setBlocked(false);
            $url->setBlockExpiration(null);
            $this->urlService->save($url);

            return new RedirectResponse($url->getLongUrl());
        }

        if ($url->isBlocked() && $url->getBlockExpiration() > new \DateTimeImmutable()) {
            $this->addFlash('warning', $this->translator->trans('message.blocked_url'));

            return $this->redirectToRoute('url_index');
        }

        if (!$url->isBlocked()) {
            return new RedirectResponse($url->getLongUrl());
        }

        return $this->redirectToRoute('url_index');
    }
}
