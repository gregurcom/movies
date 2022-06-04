<?php

declare(strict_types=1);

namespace App\Security\Voter;

use App\Entity\Comment;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;

final class CommentVoter extends Voter
{
    public const UPDATE = 'COMMENT_UPDATE';
    public const DELETE = 'COMMENT_DELETE';

    public function __construct(private Security $security) {}

    protected function supports(string $attribute, $subject): bool
    {
        return in_array($attribute, [self::UPDATE, self::DELETE]) && $subject instanceof Comment;
    }

    /**
     * @param string $attribute
     * @param Comment $subject
     * @param TokenInterface $token
     * @return bool
     */
    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();
        // if the user is anonymous, do not grant access
        if (!$user instanceof UserInterface) {
            return false;
        }

        return match ($attribute) {
            self::UPDATE, self::DELETE => $subject->getAuthor() === $user || $this->security->isGranted('ROLE_ADMIN'),
        };
    }
}
