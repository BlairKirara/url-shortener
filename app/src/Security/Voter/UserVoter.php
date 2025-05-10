<?php

/**
 * User voter.
 */

namespace App\Security\Voter;

use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Class UserVoter.
 *
 * This class is a voter for determining access permissions for user-related actions.
 */
class UserVoter extends Voter
{
    /**
     * Constructor.
     *
     * @param Security $security The security service
     */
    public function __construct(private readonly Security $security)
    {
    }

    public const EDIT_USER = 'EDIT_USER'; // Permission for editing a user
    public const VIEW = 'VIEW'; // Permission for viewing a user

    /**
     * Checks if the voter supports the given attribute and subject.
     *
     * @param string $attribute The attribute to check
     * @param mixed  $subject   The subject to check
     *
     * @return bool True if the voter supports the attribute and subject, false otherwise
     */
    protected function supports(string $attribute, mixed $subject): bool
    {
        return in_array($attribute, [self::EDIT_USER, self::VIEW])
            && $subject instanceof User;
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
    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();
        if (!$user instanceof UserInterface) {
            return false;
        }

        return match ($attribute) {
            self::VIEW, self::EDIT_USER => $this->canAccess($subject, $user),
            default => false,
        };
    }

    /**
     * Checks if the user has access to perform the given permission on the user.
     *
     * @param User          $subject The user object
     * @param UserInterface $user    The user object representing the current user
     *
     * @return bool True if the user has access, false otherwise
     */
    private function canAccess(User $subject, UserInterface $user): bool
    {
        return $subject === $user || $this->security->isGranted('ROLE_ADMIN');
    }
}
