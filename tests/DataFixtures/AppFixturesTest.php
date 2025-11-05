<?php

namespace App\Tests\DataFixtures;

use App\DataFixtures\AppFixtures;
use App\Entity\User;
use App\Entity\Group;
use App\Entity\UserMeta;
use Doctrine\Persistence\ObjectManager;
use PHPUnit\Framework\TestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixturesTest extends TestCase
{
    public function testLoadPersistsEntitiesAndFlushes(): void
    {
        $hasher = $this->createMock(UserPasswordHasherInterface::class);
        $hasher->method('hashPassword')->willReturn('hashed_password');

        $manager = $this->createMock(ObjectManager::class);
        $manager->expects($this->exactly(6))
            ->method('persist')
            ->with($this->callback(function ($entity) {
                return $entity instanceof User
                    || $entity instanceof Group
                    || $entity instanceof UserMeta;
            }));

        $manager->expects($this->once())
            ->method('flush');

        $fixtures = new AppFixtures($hasher);
        $fixtures->load($manager);
    }

    public function testPasswordHasherIsUsed(): void
    {
        $hasher = $this->createMock(UserPasswordHasherInterface::class);
        $hasher->expects($this->atLeastOnce())
            ->method('hashPassword')
            ->willReturn('fake_hash');

        $manager = $this->createMock(ObjectManager::class);
        $manager->method('persist');

        $fixtures = new AppFixtures($hasher);
        $fixtures->load($manager);

        $this->assertTrue(true);
    }
}
