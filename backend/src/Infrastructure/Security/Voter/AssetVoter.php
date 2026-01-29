<?php

declare(strict_types=1);

namespace App\Infrastructure\Security\Voter;

use App\Domain\Asset\Entity\Asset;
use App\Domain\User\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class AssetVoter extends Voter
{
    public const VIEW = 'VIEW';
    public const EDIT = 'EDIT';
    public const DELETE = 'DELETE';

    protected function supports(string $attribute, mixed $subject): bool
    {
        return in_array($attribute, [self::VIEW, self::EDIT, self::DELETE])
            && $subject instanceof Asset;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        if (!$user instanceof User) {
            return false;
        }

        /** @var Asset $asset */
        $asset = $subject;

        return match ($attribute) {
            self::VIEW => $this->canView($user, $asset),
            self::EDIT => $this->canEdit($user, $asset),
            self::DELETE => $this->canDelete($user, $asset),
            default => false,
        };
    }

    private function canView(User $user, Asset $asset): bool
    {
        // Everyone with ROLE_USER can view assets
        return in_array('ROLE_USER', $user->getRoles());
    }

    private function canEdit(User $user, Asset $asset): bool
    {
        // Only MANAGER and ADMIN can edit
        return $this->hasRole($user, ['ROLE_MANAGER', 'ROLE_ADMIN']);
    }

    private function canDelete(User $user, Asset $asset): bool
    {
        // Only ADMIN can delete
        return $this->hasRole($user, ['ROLE_ADMIN']);
    }

    private function hasRole(User $user, array $roles): bool
    {
        return !empty(array_intersect($roles, $user->getRoles()));
    }
}
