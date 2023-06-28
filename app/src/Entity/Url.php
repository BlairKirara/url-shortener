<?php

namespace App\Entity;

use App\Repository\UrlRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;


#[ORM\Entity(repositoryClass: UrlRepository::class)]
#[ORM\Table(name: 'urls')]
class Url
{

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;


    #[ORM\Column(type: 'string', length: 255)]
    #[Assert\NotBlank]
    #[Assert\Url]
    #[Assert\Length(min: 3, max: 255)]
    private ?string $longName = null;


    #[ORM\Column(type: 'string', length: 255)]
    private ?string $shortName = null;


    #[ORM\Column(type: 'datetime_immutable')]
    #[Assert\Type(\DateTimeImmutable::class)]
    #[Gedmo\Timestampable(on: 'create')]
    private ?\DateTimeImmutable $createTime = null;


    #[ORM\Column(type: 'boolean')]
    private ?bool $isBlocked = null;


    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    #[Assert\Type(\DateTimeImmutable::class)]
    private ?\DateTimeImmutable $blockTime = null;


    #[Assert\Valid]
    #[ORM\ManyToMany(targetEntity: Tag::class, fetch: 'EXTRA_LAZY', orphanRemoval: true)]
    #[ORM\JoinTable(name: 'urls_tags')]
    private $tags;


    #[ORM\ManyToOne(targetEntity: User::class, fetch: 'EXTRA_LAZY')]
    #[ORM\JoinColumn(nullable: true)]
    #[Assert\Type(User::class)]
    private ?User $users;


    #[ORM\ManyToOne(targetEntity: GuestUser::class, fetch: 'EXTRA_LAZY')]
    #[ORM\JoinColumn(name: 'guest_users_id', nullable: true)]
    private ?GuestUser $guestUser = null;


    public function __construct()
    {
        $this->tags = new ArrayCollection();
    }


    public function getId(): ?int
    {
        return $this->id;
    }


    public function getLongName(): ?string
    {
        return $this->longName;
    }


    public function setLongName(?string $longName): void
    {
        $this->longName = $longName;
    }


    public function getShortName(): ?string
    {
        return $this->shortName;
    }


    public function setShortName(?string $shortName): void
    {
        $this->shortName = $shortName;
    }


    public function getCreateTime(): ?\DateTimeImmutable
    {
        return $this->createTime;
    }


    public function setCreateTime(?\DateTimeImmutable $createTime): void
    {
        $this->createTime = $createTime;
    }


    public function isIsBlocked(): ?bool
    {
        return $this->isBlocked;
    }


    public function setIsBlocked(?bool $isBlocked): void
    {
        $this->isBlocked = $isBlocked;
    }


    public function getBlockTime(): ?\DateTimeImmutable
    {
        return $this->blockTime;
    }


    public function setBlockTime(?\DateTimeImmutable $blockTime): void
    {
        $this->blockTime = $blockTime;
    }


    public function getTags(): Collection
    {
        return $this->tags;
    }


    public function addTag(Tag $tag): void
    {
        if (!$this->tags->contains($tag)) {
            $this->tags[] = $tag;
        }
    }


    public function removeTag(Tag $tag): void
    {
        $this->tags->removeElement($tag);
    }


    public function getUsers(): ?User
    {
        return $this->users;
    }


    public function setUsers(?User $users): void
    {
        $this->users = $users;
    }


    public function getGuestUser(): ?GuestUser
    {
        return $this->guestUser;
    }


    public function setGuestUser(?GuestUser $guestUser): void
    {
        $this->guestUser = $guestUser;
    }
}
