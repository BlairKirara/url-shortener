<?php

namespace App\Security\Voter;

use App\Entity\Url;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;


class UrlVoter extends Voter
{

    public const EDIT = 'EDIT';


    public const VIEW = 'VIEW';


    public const DELETE = 'DELETE';


    public const BLOCK = 'BLOCK';


    private Security $security;


    public function __construct(Security $security)
    {
        $this->security = $security;
    }


    protected function supports(string $attribute, $subject): bool
    {
        return in_array($attribute, [self::EDIT, self::VIEW, self::DELETE, self::BLOCK])
            && $subject instanceof Url;
    }


    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();
        if (!$user instanceof UserInterface) {
            return false;
        }

        switch ($attribute) {
            case self::EDIT:
                return $this->canEdit($subject, $user) || $this->security->isGranted('ROLE_ADMIN');
            case self::VIEW:
                return $this->canView($subject, $user) || $this->security->isGranted('ROLE_ADMIN');
            case self::DELETE:
                return $this->canDelete($subject, $user) || $this->security->isGranted('ROLE_ADMIN');
            case self::BLOCK:
                return $this->security->isGranted('ROLE_ADMIN');
            default:
                return false;
        }
    }


    private function canEdit(Url $url, User $user): bool
    {
        return $url->getUsers() === $user;
    }


    private function canView(Url $url, User $user): bool
    {
        return $url->getUsers() === $user;
    }


    private function canDelete(Url $url, User $user): bool
    {
        return $url->getUsers() === $user;
    }
}
