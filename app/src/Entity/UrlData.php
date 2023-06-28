<?php

namespace App\Entity;

use App\Repository\UrlDataRepository;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * Class UrlData.
 */
#[ORM\Entity(repositoryClass: UrlDataRepository::class)]
#[ORM\Table(name: 'url_data')]
class UrlData
{
    /**
     * @var int|null
     */
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    /**
     * @var \DateTimeImmutable|null
     */
    #[ORM\Column(type: 'datetime_immutable')]
    #[Gedmo\Timestampable(on: 'create')]
    private ?\DateTimeImmutable $visitTime = null;

    /**
     * @var Url|null
     */
    #[ORM\ManyToOne(targetEntity: Url::class, fetch: 'EXTRA_LAZY')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Url $url = null;

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return \DateTimeImmutable|null
     */
    public function getVisitTime(): ?\DateTimeImmutable
    {
        return $this->visitTime;
    }

    /**
     * @param \DateTimeImmutable|null $visitTime
     * @return void
     */
    public function setVisitTime(?\DateTimeImmutable $visitTime): void
    {
        $this->visitTime = $visitTime;
    }

    /**
     * @return Url|null
     */
    public function getUrl(): ?Url
    {
        return $this->url;
    }

    /**
     * @param Url|null $url
     * @return void
     */
    public function setUrl(?Url $url): void
    {
        $this->url = $url;
    }
}
