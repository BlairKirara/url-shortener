<?php

/**
 * Url service.
 */

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
 *
 * This class provides URL-related services.
 */
class UrlService implements UrlServiceInterface
{
    /**
     * Constructor.
     *
     * @param PaginatorInterface  $paginator           The paginator
     * @param TagServiceInterface $tagService          The tag service
     * @param UrlRepository       $urlRepository       The URL repository
     * @param Security            $security            The security component
     * @param GuestUserRepository $guestUserRepository The guest user repository
     */
    public function __construct(private readonly PaginatorInterface $paginator, private readonly TagServiceInterface $tagService, private readonly UrlRepository $urlRepository, private readonly Security $security, private readonly GuestUserRepository $guestUserRepository)
    {
    }

    /**
     * Retrieves a paginated list of URLs for a specific user.
     *
     * @param int       $page    The page number
     * @param User|null $users   The user object
     * @param array     $filters The filters to apply
     *
     * @return PaginationInterface The paginated list of URLs
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
     * Retrieves a paginated list of URLs for all users.
     *
     * @param int   $page    The page number
     * @param array $filters The filters to apply
     *
     * @return PaginationInterface The paginated list of URLs
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
     * Generates a shortened URL.
     *
     * @param int $length The length of the shortened URL
     *
     * @return string The generated shortened URL
     *
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
     * Saves a URL.
     *
     * @param Url $url The URL to save
     *
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
     * Deletes a URL.
     *
     * @param Url $url The URL to delete
     */
    public function delete(Url $url): void
    {
        $this->urlRepository->delete($url);
    }

    /**
     * Finds a URL by its short name.
     *
     * @param string $shortName The short name of the URL
     *
     * @return Url|null The URL object if found, null otherwise
     */
    public function findOneByShortName(string $shortName): ?Url
    {
        return $this->urlRepository->findOneByShortName(['shortName' => $shortName]);
    }

    /**
     * Prepares the filters for querying URLs.
     *
     * @param array $filters The filters to prepare
     *
     * @return array The prepared filters
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
