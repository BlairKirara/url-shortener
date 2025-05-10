<?php

/**
 * Url data.
 */

namespace App\Entity;

use App\Repository\UrlDataRepository;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * Class UrlData.
 *
 * This class represents the data associated with a URL visit.
 */
#[ORM\Entity(repositoryClass: UrlDataRepository::class)]
#[ORM\Table(name: 'url_data')]
class UrlData
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'datetime_immutable')]
    #[Gedmo\Timestampable(on: 'create')]
    private ?\DateTimeImmutable $visitTime = null;

    #[ORM\ManyToOne(targetEntity: Url::class, fetch: 'EXTRA_LAZY')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Url $url = null;

    /**
     * Get the ID of the URL data.
     *
     * @return int|null The URL data ID
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * Get the visit time of the URL data.
     *
     * @return \DateTimeImmutable|null The visit time of the URL data
     */
    public function getVisitTime(): ?\DateTimeImmutable
    {
        return $this->visitTime;
    }

    /**
     * Set the visit time of the URL data.
     *
     * @param \DateTimeImmutable|null $visitTime The visit time of the URL data
     */
    public function setVisitTime(?\DateTimeImmutable $visitTime): void
    {
        $this->visitTime = $visitTime;
    }

    /**
     * Get the URL associated with the URL data.
     *
     * @return Url|null The URL associated with the URL data
     */
    public function getUrl(): ?Url
    {
        return $this->url;
    }

    /**
     * Set the URL associated with the URL data.
     *
     * @param Url|null $url The URL associated with the URL data
     */
    public function setUrl(?Url $url): void
    {
        $this->url = $url;
    }
}
