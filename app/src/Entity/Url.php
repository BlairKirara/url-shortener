<?php

namespace App\Entity;

use App\Repository\UrlRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 *
 */
#[ORM\Entity(repositoryClass: UrlRepository::class)]
#[ORM\Table(name: "urls")]
class Url
{
    /**
     * @var int|null
     */
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    /**
     * @var string|null
     */
    #[ORM\Column(length: 191)]
    private ?string $short_name = null;

    /**
     * @var string|null
     */
    #[ORM\Column(length: 255)]
    private ?string $long_name = null;

    /**
     * @var \DateTimeInterface|null
     */
    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $create_time = null;

    /**
     * @var bool|null
     */
    #[ORM\Column]
    private ?bool $is_blocked = null;

    /**
     * @var \DateTimeInterface|null
     */
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
//

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
    public function getShortName(): ?string
    {
        return $this->short_name;
    }

    /**
     * @param string $short_name
     * @return $this
     */
    public function setShortName(string $short_name): self
    {
        $this->short_name = $short_name;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getLongName(): ?string
    {
        return $this->long_name;
    }

    /**
     * @param string $long_name
     * @return $this
     */
    public function setLongName(string $long_name): self
    {
        $this->long_name = $long_name;

        return $this;
    }

    /**
     * @return \DateTimeInterface|null
     */
    public function getCreateTime(): ?\DateTimeInterface
    {
        return $this->create_time;
    }

    /**
     * @param \DateTimeInterface $create_time
     * @return $this
     */
    public function setCreateTime(\DateTimeInterface $create_time): self
    {
        $this->create_time = $create_time;

        return $this;
    }

    /**
     * @return bool|null
     */
    public function isIsBlocked(): ?bool
    {
        return $this->is_blocked;
    }

    /**
     * @param bool $is_blocked
     * @return $this
     */
    public function setIsBlocked(bool $is_blocked): self
    {
        $this->is_blocked = $is_blocked;

        return $this;
    }

    /**
     * @return \DateTimeInterface|null
     */
    public function getBlockTime(): ?\DateTimeInterface
    {
        return $this->block_time;
    }

    /**
     * @param \DateTimeInterface|null $block_time
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
}
