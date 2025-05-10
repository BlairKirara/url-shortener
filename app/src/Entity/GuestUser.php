<?php

/**
 * Guest user.
 */

namespace App\Entity;

use App\Repository\GuestUserRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class GuestUser.
 *
 * This class represents a guest user entity.
 */
#[ORM\Entity(repositoryClass: GuestUserRepository::class)]
#[ORM\Table(name: 'guest_users')]
class GuestUser
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 191)]
    #[Assert\NotBlank]
    #[Assert\Email]
    private ?string $email = null;

    /**
     * Get the ID of the guest user.
     *
     * @return int|null The guest user ID
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * Get the email of the guest user.
     *
     * @return string|null The guest user email
     */
    public function getEmail(): ?string
    {
        return $this->email;
    }

    /**
     * Set the email of the guest user.
     *
     * @param string $email The guest user email
     */
    public function setEmail(string $email): void
    {
        $this->email = $email;
    }
}
