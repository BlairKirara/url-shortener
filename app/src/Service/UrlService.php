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
    /**
     * @var PaginatorInterface
     */
    private PaginatorInterface $paginator;

    /**
     * @var TagServiceInterface
     */
    private TagServiceInterface $tagService;

    /**
     * @var UrlRepository
     */
    private UrlRepository $urlRepository;

    /**
     * @var Security
     */
    private Security $security;

    /**
     * @var GuestUserRepository
     */
    private GuestUserRepository $guestUserRepository;

    /**
     * Constructor.
     *
     * @param PaginatorInterface $paginator
     * @param TagServiceInterface $tagService
     * @param UrlRepository $urlRepository
     * @param Security $security
     * @param GuestUserRepository $guestUserRepository
     */
    public function __construct(PaginatorInterface $paginator, TagServiceInterface $tagService, UrlRepository $urlRepository, Security $security, GuestUserRepository $guestUserRepository)
    {
        $this->paginator = $paginator;
        $this->tagService = $tagService;
        $this->urlRepository = $urlRepository;
        $this->security = $security;
        $this->guestUserRepository = $guestUserRepository;
    }

    /**
     * @param int $page
     * @param User|null $users
     * @param array $filters
     * @return PaginationInterface
     */
    public function getPaginatedList(int $page, ?User $users, array $filters = []): PaginationInterface
    {
        $filters = $this->prepareFilters($filters);

        return $this->paginator->paginate(
            $this->urlRepository->queryByAuthor($users, $filters),
            $page,
            UrlRepository::PAGINATOR_ITEMS_PER_PAGE
        );
    }

    /**
     * @param int $page
     * @param array $filters
     * @return PaginationInterface
     */
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
     * @param int $length
     * @return string
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
     * @param Url $url
     * @return void
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

    /**
     * @param Url $url
     * @return void
     */
    public function delete(Url $url): void
    {
        $this->urlRepository->delete($url);
    }

    /**
     * @param string $shortName
     * @return Url|null
     */
    public function findOneByShortName(string $shortName): ?Url
    {
        return $this->urlRepository->findOneByShortName(['shortName' => $shortName]);
    }

    /**
     * @param array $filters
     * @return array
     */
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
