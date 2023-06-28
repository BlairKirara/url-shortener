<?php

namespace App\Entity;

use App\Repository\UrlRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class Url.
 */
#[ORM\Entity(repositoryClass: UrlRepository::class)]
#[ORM\Table(name: 'urls')]
class Url
{
    /**
     * @var int|null
     */
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    /**
     * @var string|null
     */
    #[ORM\Column(type: 'string', length: 255)]
    #[Assert\NotBlank]
    #[Assert\Url]
    private ?string $longName = null;

    /**
     * @var string|null
     */
    #[ORM\Column(type: 'string', length: 255)]
    private ?string $shortName = null;

    /**
     * @var \DateTimeImmutable|null
     */
    #[ORM\Column(type: 'datetime_immutable')]
    #[Assert\Type(\DateTimeImmutable::class)]
    #[Gedmo\Timestampable(on: 'create')]
    private ?\DateTimeImmutable $createTime = null;

    /**
     * @var bool|null
     */
    #[ORM\Column(type: 'boolean')]
    private ?bool $isBlocked = null;

    /**
     * @var \DateTimeImmutable|null
     */
    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    #[Assert\Type(\DateTimeImmutable::class)]
    private ?\DateTimeImmutable $blockTime = null;

    /**
     * @var ArrayCollection
     */
    #[Assert\Valid]
    #[ORM\ManyToMany(targetEntity: Tag::class, fetch: 'EXTRA_LAZY', orphanRemoval: true)]
    #[ORM\JoinTable(name: 'urls_tags')]
    private $tags;

    /**
     * @var User|null
     */
    #[ORM\ManyToOne(targetEntity: User::class, fetch: 'EXTRA_LAZY')]
    #[ORM\JoinColumn(name: 'user_id', nullable: true)]
    #[Assert\Type(User::class)]
    private ?User $users;

    /**
     * @var GuestUser|null
     */
    #[ORM\ManyToOne(targetEntity: GuestUser::class, fetch: 'EXTRA_LAZY')]
    #[ORM\JoinColumn(name: 'guest_user_id', nullable: true)]
    private ?GuestUser $guestUser = null;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->tags = new ArrayCollection();
    }

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return string|null
     */
    public function getLongName(): ?string
    {
        return $this->longName;
    }

    /**
     * @param string|null $longName
     * @return void
     */
    public function setLongName(?string $longName): void
    {
        $this->longName = $longName;
    }

    /**
     * @return string|null
     */
    public function getShortName(): ?string
    {
        return $this->shortName;
    }

    /**
     * @param string|null $shortName
     * @return void
     */
    public function setShortName(?string $shortName): void
    {
        $this->shortName = $shortName;
    }

    /**
     * @return \DateTimeImmutable|null
     */
    public function getCreateTime(): ?\DateTimeImmutable
    {
        return $this->createTime;
    }

    /**
     * @param \DateTimeImmutable|null $createTime
     * @return void
     */
    public function setCreateTime(?\DateTimeImmutable $createTime): void
    {
        $this->createTime = $createTime;
    }

    /**
     * @return bool|null
     */
    public function isIsBlocked(): ?bool
    {
        return $this->isBlocked;
    }

    /**
     * @param bool|null $isBlocked
     * @return void
     */
    public function setIsBlocked(?bool $isBlocked): void
    {
        $this->isBlocked = $isBlocked;
    }

    /**
     * @return \DateTimeImmutable|null
     */
    public function getBlockTime(): ?\DateTimeImmutable
    {
        return $this->blockTime;
    }

    /**
     * @param \DateTimeImmutable|null $blockTime
     * @return void
     */
    public function setBlockTime(?\DateTimeImmutable $blockTime): void
    {
        $this->blockTime = $blockTime;
    }

    /**
     * @return Collection
     */
    public function getTags(): Collection
    {
        return $this->tags;
    }

    /**
     * @param Tag $tag
     * @return void
     */
    public function addTag(Tag $tag): void
    {
        if (!$this->tags->contains($tag)) {
            $this->tags[] = $tag;
        }
    }

    /**
     * @param Tag $tag
     * @return void
     */
    public function removeTag(Tag $tag): void
    {
        $this->tags->removeElement($tag);
    }

    /**
     * @return User|null
     */
    public function getUsers(): ?User
    {
        return $this->users;
    }

    /**
     * @param User|null $users
     * @return void
     */
    public function setUsers(?User $users): void
    {
        $this->users = $users;
    }

    /**
     * @return GuestUser|null
     */
    public function getGuestUser(): ?GuestUser
    {
        return $this->guestUser;
    }

    /**
     * @param GuestUser|null $guestUser
     * @return void
     */
    public function setGuestUser(?GuestUser $guestUser): void
    {
        $this->guestUser = $guestUser;
    }
}
