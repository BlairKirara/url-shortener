<?php
/**
 * Url service.
 */

namespace App\Service;

use App\Entity\Url;
use App\Entity\User;
use App\Repository\GuestUserRepository;
use App\Repository\UrlRepository;
use Doctrine\ORM\NonUniqueResultException;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Security;


class UrlService implements UrlServiceInterface
{

    private PaginatorInterface $paginator;


    private TagServiceInterface $tagService;


    private UrlRepository $urlRepository;


    private Security $security;


    private GuestUserRepository $guestUserRepository;


    private RequestStack $requestStack;


    public function __construct(PaginatorInterface $paginator, TagServiceInterface $tagService, UrlRepository $urlRepository, Security $security, GuestUserRepository $guestUserRepository, RequestStack $requestStack)
    {
        $this->paginator = $paginator;
        $this->tagService = $tagService;
        $this->urlRepository = $urlRepository;
        $this->security = $security;
        $this->guestUserRepository = $guestUserRepository;
        $this->requestStack = $requestStack;
    }


    public function getPaginatedList(int $page, ?User $users, array $filters = []): PaginationInterface
    {
        $filters = $this->prepareFilters($filters);

        return $this->paginator->paginate(
            $this->urlRepository->queryByAuthor($users, $filters),
            $page,
            UrlRepository::PAGINATOR_ITEMS_PER_PAGE
        );
    }


    public function getPaginatedListForEveryUser(int $page, array $filters = []): PaginationInterface
    {
        $filters = $this->prepareFilters($filters);

        if ($this->security->isGranted('ROLE_ADMIN')) {
            return $this->paginator->paginate(
                $this->urlRepository->queryAll($filters),
                $page,
                UrlRepository::PAGINATOR_ITEMS_PER_PAGE
            );
        }

        return $this->paginator->paginate(
            $this->urlRepository->queryNotBlocked($filters),
            $page,
            UrlRepository::PAGINATOR_ITEMS_PER_PAGE
        );
    }

    public function shortenUrl(int $length = 6): string
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $shortName = '';

        for ($i = 0; $i < $length; ++$i) {
            $randomIndex = random_int(0, strlen($characters) - 1);
            $shortName .= $characters[$randomIndex];
        }

        return $shortName;
    }


    public function save(Url $url): void
    {
        if (null === $url->getId()) {
            if (!$this->security->isGranted('ROLE_USER')) {
                $email = $this->requestStack->getCurrentRequest()->getSession()->get('email');
                $user = $this->guestUserRepository->findOneBy(['email' => $email]);
                $url->setGuestUser($user);
                $this->requestStack->getCurrentRequest()->getSession()->remove('email');
            }
            $url->setShortName($this->shortenUrl());
            $url->setIsBlocked(false);
        }
        $this->urlRepository->save($url);
    }


    public function delete(Url $url): void
    {
        $this->urlRepository->delete($url);
    }


    public function findOneByShortName(string $shortName): ?Url
    {
        return $this->urlRepository->findOneBy(['shortName' => $shortName]);
    }


    private function prepareFilters(array $filters): array
    {
        $resultFilters = [];

        if (!empty($filters['tag_id'])) {
            $tag = $this->tagService->findOneById($filters['tag_id']);
            if (null !== $tag) {
                $resultFilters['tag'] = $tag;
            }
        }

        return $resultFilters;
    }
}
