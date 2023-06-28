<?php

namespace App\Entity;

use App\Repository\GuestUserRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class GuestUser.
 */
#[ORM\Entity(repositoryClass: GuestUserRepository::class)]
#[ORM\Table(name: 'guest_users')]
class GuestUser
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
    #[ORM\Column(type: 'string', length: 191)]
    #[Assert\NotBlank]
    #[Assert\Email]
    private ?string $email;

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
    public function getEmail(): ?string
    {
        return $this->email;
    }

    /**
     * @param string $email
     * @return void
     */
    public function setEmail(string $email): void
    {
        $this->email = $email;
    }
}
