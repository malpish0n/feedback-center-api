<?php

namespace App\Tests\Entity;

use App\Entity\User;
use PHPUnit\Framework\TestCase;

class UserTest extends TestCase
{
    public function testEmail(): void
    {
        $user = new User();
        $user->setEmail('test@example.com');
        $this->assertSame('test@example.com', $user->getEmail());
    }

    public function testRoles(): void
    {
        $user = new User();
        $user->setRoles(['ROLE_ADMIN']);

        $roles = $user->getRoles();
        $this->assertContains('ROLE_ADMIN', $roles);
        $this->assertContains('ROLE_USER', $roles, 'Default ROLE_USER should always be present');
    }

    public function testNickname(): void
    {
        $user = new User();
        $user->setNickname('tester');
        $this->assertSame('tester', $user->getNickname());
    }

    public function testPassword(): void
    {
        $user = new User();
        $user->setPassword('hashed_password');
        $this->assertSame('hashed_password', $user->getPassword());
    }

    public function testGetUserIdentifierReturnsEmail(): void
    {
        $user = new User();
        $user->setEmail('identifier@example.com');
        $this->assertSame('identifier@example.com', $user->getUserIdentifier());
    }

    public function testEraseCredentialsDoesNothing(): void
    {
        $user = new User();
        $user->eraseCredentials();
        $this->assertTrue(true);
    }
}
