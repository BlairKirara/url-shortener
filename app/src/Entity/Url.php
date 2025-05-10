<?php

/**
 * Url.
 */

namespace App\Entity;

use App\Repository\UrlRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class Url.
 *
 * This class represents a URL entity.
 */
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

    /**
     * @var ArrayCollection
     */
    #[Assert\Valid]
    #[ORM\ManyToMany(targetEntity: Tag::class, fetch: 'EXTRA_LAZY', orphanRemoval: true)]
    #[ORM\JoinTable(name: 'urls_tags')]
    private $tags;

    #[ORM\ManyToOne(targetEntity: User::class, fetch: 'EXTRA_LAZY')]
    #[ORM\JoinColumn(name: 'user_id', nullable: true)]
    #[Assert\Type(User::class)]
    private ?User $users = null;

    #[ORM\ManyToOne(targetEntity: GuestUser::class, fetch: 'EXTRA_LAZY')]
    #[ORM\JoinColumn(name: 'guest_user_id', nullable: true)]
    private ?GuestUser $guestUser = null;

    /**
     * Constructor.
     *
     * Initializes the tags collection.
     */
    public function __construct()
    {
        $this->tags = new ArrayCollection();
    }

    /**
     * Get the ID of the URL.
     *
     * @return int|null The URL ID
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * Get the long name of the URL.
     *
     * @return string|null The long name of the URL
     */
    public function getLongName(): ?string
    {
        return $this->longName;
    }

    /**
     * Set the long name of the URL.
     *
     * @param string|null $longName The long name of the URL
     */
    public function setLongName(?string $longName): void
    {
        $this->longName = $longName;
    }

    /**
     * Get the short name of the URL.
     *
     * @return string|null The short name of the URL
     */
    public function getShortName(): ?string
    {
        return $this->shortName;
    }

    /**
     * Set the short name of the URL.
     *
     * @param string|null $shortName The short name of the URL
     */
    public function setShortName(?string $shortName): void
    {
        $this->shortName = $shortName;
    }

    /**
     * Get the create time of the URL.
     *
     * @return \DateTimeImmutable|null The create time of the URL
     */
    public function getCreateTime(): ?\DateTimeImmutable
    {
        return $this->createTime;
    }

    /**
     * Set the create time of the URL.
     *
     * @param \DateTimeImmutable|null $createTime The create time of the URL
     */
    public function setCreateTime(?\DateTimeImmutable $createTime): void
    {
        $this->createTime = $createTime;
    }

    /**
     * Check if the URL is blocked.
     *
     * @return bool|null Whether the URL is blocked
     */
    public function isIsBlocked(): ?bool
    {
        return $this->isBlocked;
    }

    /**
     * Set whether the URL is blocked.
     *
     * @param bool|null $isBlocked Whether the URL is blocked
     */
    public function setIsBlocked(?bool $isBlocked): void
    {
        $this->isBlocked = $isBlocked;
    }

    /**
     * Get the block time of the URL.
     *
     * @return \DateTimeImmutable|null The block time of the URL
     */
    public function getBlockTime(): ?\DateTimeImmutable
    {
        return $this->blockTime;
    }

    /**
     * Set the block time of the URL.
     *
     * @param \DateTimeImmutable|null $blockTime The block time of the URL
     */
    public function setBlockTime(?\DateTimeImmutable $blockTime): void
    {
        $this->blockTime = $blockTime;
    }

    /**
     * Get the collection of tags associated with the URL.
     *
     * @return Collection The collection of tags
     */
    public function getTags(): Collection
    {
        return $this->tags;
    }

    /**
     * Add a tag to the URL.
     *
     * @param Tag $tag The tag to add
     */
    public function addTag(Tag $tag): void
    {
        if (!$this->tags->contains($tag)) {
            $this->tags[] = $tag;
        }
    }

    /**
     * Remove a tag from the URL.
     *
     * @param Tag $tag The tag to remove
     */
    public function removeTag(Tag $tag): void
    {
        $this->tags->removeElement($tag);
    }

    /**
     * Get the user associated with the URL.
     *
     * @return User|null The user associated with the URL
     */
    public function getUsers(): ?User
    {
        return $this->users;
    }

    /**
     * Set the user associated with the URL.
     *
     * @param User|null $users The user associated with the URL
     */
    public function setUsers(?User $users): void
    {
        $this->users = $users;
    }

    /**
     * Get the guest user associated with the URL.
     *
     * @return GuestUser|null The guest user associated with the URL
     */
    public function getGuestUser(): ?GuestUser
    {
        return $this->guestUser;
    }

    /**
     * Set the guest user associated with the URL.
     *
     * @param GuestUser|null $guestUser The guest user associated with the URL
     */
    public function setGuestUser(?GuestUser $guestUser): void
    {
        $this->guestUser = $guestUser;
    }
}
