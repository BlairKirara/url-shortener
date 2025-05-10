<?php

/**
 * Tag.
 */

namespace App\Entity;

use App\Repository\TagRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class Tag.
 *
 * This class represents a tag entity.
 */
#[ORM\Entity(repositoryClass: TagRepository::class)]
#[ORM\Table(name: 'tags')]
#[UniqueEntity(fields: ['name'])]
class Tag
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 70)]
    #[Assert\Type('string')]
    #[Assert\NotBlank]
    private ?string $name = null;

    /**
     * Get the ID of the tag.
     *
     * @return int|null The tag ID
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * Get the name of the tag.
     *
     * @return string|null The tag name
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * Set the name of the tag.
     *
     * @param string|null $name The tag name
     */
    public function setName(?string $name): void
    {
        $this->name = $name;
    }
}
