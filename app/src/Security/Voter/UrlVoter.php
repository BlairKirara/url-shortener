<?php

namespace App\Security\Voter;

use App\Entity\Url;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Class UrlVoter.
 */
class UrlVoter extends Voter
{
    /**
     *
     */
    public const EDIT = 'EDIT';
    /**
     *
     */
    public const VIEW = 'VIEW';
    /**
     *
     */
    public const DELETE = 'DELETE';
    /**
     *
     */
    public const BLOCK = 'BLOCK';

    /**
     * @var Security
     */
    private Security $security;

    /**
     * Constructor.
     *
     * @param Security $security
     */
    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    /**
     * @param string $attribute
     * @param $subject
     * @return bool
     */
    protected function supports(string $attribute, $subject): bool
    {
        return in_array($attribute, [self::EDIT, self::VIEW, self::DELETE, self::BLOCK])
            && $subject instanceof Url;
    }

    /**
     * @param string $attribute
     * @param $subject
     * @param TokenInterface $token
     * @return bool
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
     * @param string $permission
     * @param Url $url
     * @param User $user
     * @return bool
     */
    private function canAccess(string $permission, Url $url, User $user): bool
    {
        return $url->getUsers() === $user;
    }
}
