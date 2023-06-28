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


class UserVoter extends Voter
{

    private Security $security;


    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    public const EDIT_USER_DATA = 'EDIT_USER_DATA';
    public const VIEW = 'VIEW';


    protected function supports(string $attribute, mixed $subject): bool
    {
        return in_array($attribute, [self::EDIT_USER_DATA, self::VIEW])
            && $subject instanceof User;
    }


    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();
        if (!$user instanceof UserInterface) {
            return false;
        }

        return match ($attribute) {
            self::VIEW, self::EDIT_USER_DATA => $this->canAccess($subject, $user),
            default => false,
        };
    }


    private function canAccess(User $subject, UserInterface $user): bool
    {
        return $subject === $user || $this->security->isGranted('ROLE_ADMIN');
    }
}
