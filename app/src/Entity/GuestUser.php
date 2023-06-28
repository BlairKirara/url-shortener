<?php

namespace App\Entity;

use App\Repository\GuestUserRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;


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
    private ?string $email;


    public function getId(): ?int
    {
        return $this->id;
    }


    public function getEmail(): ?string
    {
        return $this->email;
    }


    public function setEmail(string $email): void
    {
        $this->email = $email;
    }
}
