<?php
/**
 * User.
 */

namespace App\Entity;

use App\Entity\Enum\UserRole;
use App\Repository\UserRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class User.
 *
 * This class represents a user entity.
 */
#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: 'users')]
#[ORM\UniqueConstraint(name: 'email_idx', columns: ['email'])]
#[UniqueEntity(fields: ['email'])]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 191, unique: true)]
    #[Assert\NotBlank]
    #[Assert\Email]
    private ?string $email;

    #[ORM\Column(type: 'json')]
    private array $roles = [];

    #[ORM\Column(type: 'string')]
    #[Assert\NotBlank]
    private ?string $password;

    /**
     * Get the ID of the user.
     *
     * @return int|null The user ID
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * Get the email of the user.
     *
     * @return string|null The user email
     */
    public function getEmail(): ?string
    {
        return $this->email;
    }

    /**
     * Set the email of the user.
     *
     * @param string $email The user email
     */
    public function setEmail(string $email): void
    {
        $this->email = $email;
    }

    /**
     * Get the user identifier.
     *
     * @return string The user identifier
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    /**
     * Get the username.
     *
     * @return string The username
     */
    public function getUsername(): string
    {
        return (string) $this->email;
    }

    /**
     * Get the roles of the user.
     *
     * @return array An array of roles
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = UserRole::ROLE_USER->value;

        return array_unique($roles);
    }

    /**
     * Set the roles of the user.
     *
     * @param array $roles An array of roles
     */
    public function setRoles(array $roles): void
    {
        $this->roles = $roles;
    }

    /**
     * Get the password of the user.
     *
     * @return string|null The user password
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }

    /**
     * Set the password of the user.
     *
     * @param string $password The user password
     */
    public function setPassword(string $password): void
    {
        $this->password = $password;
    }

    /**
     * Get the salt.
     *
     * @return string|null The salt
     */
    public function getSalt(): ?string
    {
        return null;
    }

    /**
     * Erase the user credentials.
     *
     * This method is called when the user's credentials should be erased.
     * In this case, no action is performed.
     */
    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }
}
