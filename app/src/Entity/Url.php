<?php

namespace App\Entity;

use App\Repository\UrlRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: UrlRepository::class)]
#[ORM\Table(name: 'urls')]
class Url
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 191)]
    private ?string $short_name = null;

    #[ORM\Column(length: 255)]
    private ?string $long_name = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $create_time = null;

    #[ORM\Column]
    private ?bool $is_blocked = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $block_time = null;

    // ...
    /**
     * Tags.
     *
     * @var ArrayCollection<int, Tags>
     */
    #[Assert\Valid]
    #[ORM\ManyToMany(targetEntity: Tags::class, fetch: 'EXTRA_LAZY', orphanRemoval: true)]
    #[ORM\JoinTable(name: 'urls_tags')]
    private $tags;


    #[ORM\ManyToOne(targetEntity: User::class, fetch: 'EXTRA_LAZY')]
    #[ORM\JoinColumn(nullable: true)]
    #[Assert\Type(User::class)]
    private ?User $user;

    #[ORM\ManyToOne]
    private ?GuestUser $guest_user;

    public function __construct()
    {
        $this->tags = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getShortName(): ?string
    {
        return $this->short_name;
    }

    /**
     * @return $this
     */
    public function setShortName(string $short_name): self
    {
        $this->short_name = $short_name;

        return $this;
    }

    public function getLongName(): ?string
    {
        return $this->long_name;
    }

    /**
     * @return $this
     */
    public function setLongName(string $long_name): self
    {
        $this->long_name = $long_name;

        return $this;
    }

    public function getCreateTime(): ?\DateTimeInterface
    {
        return $this->create_time;
    }

    /**
     * @return $this
     */
    public function setCreateTime(\DateTimeInterface $create_time): self
    {
        $this->create_time = $create_time;

        return $this;
    }

    public function isIsBlocked(): ?bool
    {
        return $this->is_blocked;
    }

    /**
     * @return $this
     */
    public function setIsBlocked(bool $is_blocked): self
    {
        $this->is_blocked = $is_blocked;

        return $this;
    }

    public function getBlockTime(): ?\DateTimeInterface
    {
        return $this->block_time;
    }

    /**
     * @return $this
     */
    public function setBlockTime(?\DateTimeInterface $block_time): self
    {
        $this->block_time = $block_time;

        return $this;
    }

    /**
     * @return Collection<int, Tags>
     */
    public function getTags(): Collection
    {
        return $this->tags;
    }

    public function addTag(Tags $tag): self
    {
        if (!$this->tags->contains($tag)) {
            $this->tags->add($tag);
        }

        return $this;
    }

    public function removeTag(Tags $tag): self
    {
        $this->tags->removeElement($tag);

        return $this;
    }

    public function getUserId(): ?User
    {
        return $this->user_id;
    }

    public function setUserId(?User $user_id): self
    {
        $this->user_id = $user_id;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getGuestUser(): ?GuestUser
    {
        return $this->guest_user;
    }

    public function setGuestUser(?GuestUser $guest_user): self
    {
        $this->guest_user = $guest_user;

        return $this;
    }
}
