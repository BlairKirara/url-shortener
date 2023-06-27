<?php
/**
 * Url service.
 */

namespace App\Service;

use App\Entity\Url;
use App\Entity\User;
use App\Repository\UrlRepository;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;

class UrlService implements UrlServiceInterface
{
    private UrlRepository $urlRepository;
    private PaginatorInterface $paginator;

    private TagServiceInterface $tagService;

    public function __construct(UrlRepository $urlRepository, PaginatorInterface $paginator, TagServiceInterface $tagService)
    {
        $this->urlRepository = $urlRepository;
        $this->paginator = $paginator;
        $this->tagService = $tagService;
    }

    public function getPaginatedList(int $page, User|\App\Service\User $user, array $filters = []): PaginationInterface
    {
        $filters = $this->prepareFilters($filters);

        return $this->paginator->paginate(
            $this->urlRepository->queryByAuthor($user, $filters),
            $page,
            UrlRepository::PAGINATOR_ITEMS_PER_PAGE
        );
    }

    /**
     * Generate short URL.
     *
     * @param int $length Length of the short URL
     *
     * @return string Short URL
     */
    public function shortenUrl(int $length = 6): string
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $shortUrl = '';

        for ($i = 0; $i < $length; ++$i) {
            $randomIndex = random_int(0, strlen($characters) - 1);
            $shortUrl .= $characters[$randomIndex];
        }

        return $shortUrl;
    }

    /**
    Check if the generated short URL is unique.
    @param string $shortName Short URL to check
    @return bool True if unique, False otherwise
     */
    private function isShortNameUnique(string $shortName): bool
    {
        return null === $this->urlRepository->findOneBy(['short_name' => $shortName]);
    }
    // enduwu

    /**
     * Save entity.
     *
     * @param Url $url Url entity
     */
    public function save(Url $url): void
    {
        if (null == $url->getId()) {
            $url->setCreateTime(new \DateTimeImmutable());
            $url->setShortName($this->shortenUrl());
            $url->setIsBlocked(false);
        }

        $this->urlRepository->save($url);
    }

    /**
     * Delete entity.
     *
     * @param Url $url Url entity
     */
    public function delete(Url $url): void
    {
        $this->urlRepository->delete($url);
    }

    /**
     * Find one by short name.
     *
     * @param string $shortName Short name
     *
     * @return Url|null Url entity
     */
    public function findOneByShortName(string $shortName): ?Url
    {
        return $this->urlRepository->findOneBy(['short_name' => $shortName]);
    }

    /**
     * Find by id.
     *
     * @param int $id Url id
     *
     * @return Url|null Url entity
     *
     * @throws NonUniqueResultException
     */
    public function findOneById(int $id): ?Url
    {
        return $this->urlRepository->findOneById($id);
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
