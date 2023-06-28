<?php

namespace App\Security\Voter;

use App\Entity\Tag;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;

class TagVoter extends Voter
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
            && $subject instanceof Tag;
    }


    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();
        if (!$user instanceof UserInterface) {
            return false;
        }

        return match ($attribute) {
            self::EDIT => $this->canEdit($subject, $user) || $this->security->isGranted('ROLE_ADMIN'),
            self::VIEW => $this->canView($subject, $user) || $this->security->isGranted('ROLE_ADMIN'),
            self::DELETE => $this->canDelete($subject, $user) || $this->security->isGranted('ROLE_ADMIN'),
            self::BLOCK => $this->security->isGranted('ROLE_ADMIN'),
            default => false,
        };
    }


    private function canEdit(Tag $tag, User $user): bool
    {
        return $tag->getUsers() === $user;
    }


    private function canView(Tag $tag, User $user): bool
    {
        return $tag->getUsers() === $user;
    }


    private function canDelete(Tag $tag, User $user): bool
    {
        return $tag->getUsers() === $user;
    }

}