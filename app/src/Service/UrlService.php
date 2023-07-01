<?php

namespace App\Service;

use App\Entity\Url;
use App\Entity\User;
use App\Repository\GuestUserRepository;
use App\Repository\UrlRepository;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\Security\Core\Security;

/**
 * Class UrlService.
 */
class UrlService implements UrlServiceInterface
{
    private PaginatorInterface $paginator;

    private TagServiceInterface $tagService;

    private UrlRepository $urlRepository;

    private Security $security;

    private GuestUserRepository $guestUserRepository;

    /**
     * Constructor.
     */
    public function __construct(PaginatorInterface $paginator, TagServiceInterface $tagService, UrlRepository $urlRepository, Security $security, GuestUserRepository $guestUserRepository)
    {
        $this->paginator = $paginator;
        $this->tagService = $tagService;
        $this->urlRepository = $urlRepository;
        $this->security = $security;
        $this->guestUserRepository = $guestUserRepository;
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

    public function getPaginatedListForAll(int $page, array $filters = []): PaginationInterface
    {
        $filters = $this->prepareFilters($filters);

        return $this->paginator->paginate(
            $this->urlRepository->queryAll($filters),
            $page,
            UrlRepository::PAGINATOR_ITEMS_PER_PAGE
        );
    }

    /**
     * @throws \Exception
     */
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

    /**
     * @throws \Exception
     */
    public function save(Url $url): void
    {
        if (null === $url->getId()) {
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
        return $this->urlRepository->findOneByShortName(['shortName' => $shortName]);
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
