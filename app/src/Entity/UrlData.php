<?php


namespace App\Entity;

use App\Repository\UrlDataRepository;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;


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


    public function getId(): ?int
    {
        return $this->id;
    }


    public function getVisitTime(): ?\DateTimeImmutable
    {
        return $this->visitTime;
    }


    public function setVisitTime(?\DateTimeImmutable $visitTime): void
    {
        $this->visitTime = $visitTime;
    }


    public function getUrl(): ?Url
    {
        return $this->url;
    }


    public function setUrl(?Url $url): void
    {
        $this->url = $url;
    }
}
