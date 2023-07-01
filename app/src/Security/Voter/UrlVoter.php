<?php
/**
 * Url voter.
 */

namespace App\Security\Voter;

use App\Entity\Url;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Class UrlVoter.
 *
 * This class is a voter for determining access permissions for URL-related actions.
 */
class UrlVoter extends Voter
{
    public const EDIT = 'EDIT'; // Permission for editing a URL
    public const VIEW = 'VIEW'; // Permission for viewing a URL
    public const DELETE = 'DELETE'; // Permission for deleting a URL
    public const BLOCK = 'BLOCK'; // Permission for blocking a URL

    private Security $security;

    /**
     * Constructor.
     *
     * @param Security $security The security service
     */
    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    /**
     * Checks if the voter supports the given attribute and subject.
     *
     * @param string $attribute The attribute to check
     * @param mixed  $subject   The subject to check
     *
     * @return bool True if the voter supports the attribute and subject, false otherwise
     */
    protected function supports(string $attribute, $subject): bool
    {
        return in_array($attribute, [self::EDIT, self::VIEW, self::DELETE, self::BLOCK])
            && $subject instanceof Url;
    }

    /**
     * Performs the voting operation based on the attribute, subject, and token.
     *
     * @param string         $attribute The attribute to vote on
     * @param mixed          $subject   The subject to vote on
     * @param TokenInterface $token     The token representing the user
     *
     * @return bool True if access is granted, false otherwise
     */
    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();
        if (!$user instanceof UserInterface) {
            return false;
        }

        return match ($attribute) {
            self::EDIT, self::VIEW, self::DELETE => $this->canAccess($attribute, $subject, $user) || $this->security->isGranted('ROLE_ADMIN'),
            self::BLOCK => $this->security->isGranted('ROLE_ADMIN'),
            default => false,
        };
    }

    /**
     * Checks if the user has access to perform the given permission on the URL.
     *
     * @param string $permission The permission to check
     * @param Url    $url        The URL object
     * @param User   $user       The user object
     *
     * @return bool True if the user has access, false otherwise
     */
    private function canAccess(string $permission, Url $url, User $user): bool
    {
        return $url->getUsers() === $user;
    }
}
