<?php
/**
 * Url service.
 */

namespace App\Service;

use App\Entity\Url;
use App\Repository\UrlRepository;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;

class UrlService implements UrlServiceInterface
{
    private UrlRepository $urlRepository;
    private PaginatorInterface $paginator;

    public function __construct(UrlRepository $urlRepository, PaginatorInterface $paginator)
    {
        $this->urlRepository = $urlRepository;
        $this->paginator = $paginator;
    }

    public function getPaginatedList(int $page): PaginationInterface
    {
        return $this->paginator->paginate(
            $this->urlRepository->queryAll(),
            $page,
            UrlRepository::PAGINATOR_ITEMS_PER_PAGE
        );
    }

    /**
     * Generate short url.
     *
     * @return string Short url
     */
    public function shortenUrl(): string
    {
        do {
            $shortName = substr(md5(uniqid(rand(), true)), 0, 6);
        } while (null !== $this->urlRepository->findOneBy(['short_name' => $shortName]));

        return $shortName;
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
}
