<?php

namespace App\Entity;

use App\Repository\UrlDataRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: UrlDataRepository::class)]
class UrlData
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;


    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $visit_time = null;

    #[ORM\OneToMany(mappedBy: 'urlData', targetEntity: Url::class)]
    private Collection $url_id;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Url $url = null;

    public function __construct()
    {
        $this->url_id = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getVisitTime(): ?\DateTimeInterface
    {
        return $this->visit_time;
    }

    public function setVisitTime(\DateTimeInterface $visit_time): self
    {
        $this->visit_time = $visit_time;

        return $this;
    }

    /**
     * @return Collection<int, Url>
     */
    public function getUrlId(): Collection
    {
        return $this->url_id;
    }

    public function addUrlId(Url $urlId): self
    {
        if (!$this->url_id->contains($urlId)) {
            $this->url_id->add($urlId);
            $urlId->setUrlData($this);
        }

        return $this;
    }

    public function removeUrlId(Url $urlId): self
    {
        if ($this->url_id->removeElement($urlId)) {
            // set the owning side to null (unless already changed)
            if ($urlId->getUrlData() === $this) {
                $urlId->setUrlData(null);
            }
        }

        return $this;
    }

    public function getUrl(): ?Url
    {
        return $this->url;
    }

    public function setUrl(?Url $url): self
    {
        $this->url = $url;

        return $this;
    }
}
