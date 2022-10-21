<?php
namespace App\Security;

use App\Entity\Post;
use App\Entity\User;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class PostVoter extends Voter {

    const VIEW = 'ROLE_USER';
    const EDIT = 'ROLE_AUTHOR';
    const ADMIN = 'ROLE_ADMIN';

    public function __construct(private Security $security){}

    protected function supports(string $attribute, mixed $subject): bool
    {
        if (!in_array($attribute, [self::VIEW, self::EDIT, self::ADMIN])) {
            return false;
        }

        // only vote on `Post` objects
        if (!$subject instanceof Post) {
            return false;
        }

        return true;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        if (!$user instanceOf User) {
            return false;
        }

        if ($this->security->isGranted('ROLE_ADMIN')) {
            return true;
        }

        /**
         * @var Post $post
         */
        $post = $subject;

        return match($attribute) {
            self::VIEW => $this->canView($post, $user),
            self::EDIT => $this->canEdit($post, $user),
            default => throw new \LogicException('This code should not be reached!')
        };
    }

    private function canView(Post $post, User $user): bool
    {
        return true;
    }

    private function canEdit(Post $post, User $user): bool
    {
        return true;
    }
}