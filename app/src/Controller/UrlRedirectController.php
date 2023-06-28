<?php


namespace App\Controller;

use App\Entity\UrlData;
use App\Service\UrlServiceInterface;
use App\Service\UrlDataServiceInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;


#[Route('/short')]
class UrlRedirectController extends AbstractController
{

    private UrlServiceInterface $urlService;


    private TranslatorInterface $translator;


    private UrlDataServiceInterface $urlDataService;


    public function __construct(UrlServiceInterface $urlService, TranslatorInterface $translator, UrlDataServiceInterface $urlDataService)
    {
        $this->urlService = $urlService;
        $this->translator = $translator;
        $this->urlDataService = $urlDataService;
    }


    #[Route(
        '/{shortName}',
        name: 'url_redirect_index',
        methods: ['GET'],
    )]
    public function index(string $shortName): Response
    {
        $url = $this->urlService->findOneByShortName($shortName);

        if (!$url) {
            throw $this->createNotFoundException($this->translator->trans('message.url_not_found'));
        }

        if ($url->isIsBlocked() && $url->getBlockExpiration() < new \DateTimeImmutable()) {
            $url->setIsBlocked(false);
            $url->setBlockExpiration(null);
            $this->urlService->save($url);

            return new RedirectResponse($url->getLongName());
        }
        if ($url->isIsBlocked() && $url->getBlockExpiration() > new \DateTimeImmutable()) {
            $this->addFlash('warning', $this->translator->trans('message.blocked_url'));

            if ($this->isGranted('ROLE_ADMIN')) {
                return new RedirectResponse($url->getLongName());
            }

            return $this->redirectToRoute('url_list');
        }
        if (!$url->isIsBlocked()) {
            $urlData = new UrlData();
            $urlData->setVisitTime(new \DateTimeImmutable());
            $urlData->setUrl($url);

            $this->urlDataService->save($urlData);

            return new RedirectResponse($url->getLongName());
        }

        return $this->redirectToRoute('url_list');
    }
}
