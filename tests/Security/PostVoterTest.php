<?php

namespace App\Tests\Security;

use App\Entity\User;
use App\Entity\Post;
use App\Security\PostVoter;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;
use ReflectionProperty;

class PostVoterTest extends TestCase
{
    private function createToken(User $user): UsernamePasswordToken
    {
        return new UsernamePasswordToken($user, 'credentials', ['ROLE_USER']);
    }

    private function setEntityId(object $entity, int $id): void
    {
        $ref = new ReflectionProperty($entity, 'id');
        $ref->setAccessible(true);
        $ref->setValue($entity, $id);
    }

    public function testOwnerCanEdit(): void
    {
        $user = new User();
        $this->setEntityId($user, 1);

        $post = new Post();
        $this->setEntityId($post, 10);
        $post->setAuthor($user);

        $token = $this->createToken($user);

        $voter = new PostVoter();
        $result = $voter->vote($token, $post, [PostVoter::POST_EDIT]);

        $this->assertSame(VoterInterface::ACCESS_GRANTED, $result);
    }

    public function testNonOwnerCannotEdit(): void
    {
        $user = new User();
        $this->setEntityId($user, 2);

        $owner = new User();
        $this->setEntityId($owner, 1);

        $post = new Post();
        $post->setAuthor($owner);

        $token = $this->createToken($user);

        $voter = new PostVoter();
        $result = $voter->vote($token, $post, [PostVoter::POST_EDIT]);

        $this->assertSame(VoterInterface::ACCESS_DENIED, $result);
    }

    public function testUnsupportedAttributeReturnsAbstain(): void
    {
        $user = new User();
        $this->setEntityId($user, 3);

        $token = $this->createToken($user);
        $post = new Post();

        $voter = new PostVoter();
        $result = $voter->vote($token, $post, ['UNKNOWN']);

        $this->assertSame(VoterInterface::ACCESS_ABSTAIN, $result);
    }
}
