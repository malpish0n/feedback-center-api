<?php

namespace App\Tests\Repository;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class UserRepositoryTest extends KernelTestCase
{
    private ?EntityManagerInterface $em = null;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->em = static::getContainer()->get('doctrine')->getManager();
    }

    public function testPersistAndFindUser(): void
    {
        $user = new User();
        $user->setEmail('repo@example.com');
        $user->setNickname('RepoTester');
        $user->setPassword('secret');

        $this->em->persist($user);
        $this->em->flush();

        $repo = $this->em->getRepository(User::class);
        $found = $repo->findOneBy(['email' => 'repo@example.com']);

        $this->assertNotNull($found);
        $this->assertSame('RepoTester', $found->getNickname());
    }

    public function testRemoveUser(): void
    {
        $repo = $this->em->getRepository(User::class);

        $user = new User();
        $user->setEmail('remove@example.com');
        $user->setNickname('RemoveTester');
        $user->setPassword('pwd');

        $this->em->persist($user);
        $this->em->flush();

        $found = $repo->findOneBy(['email' => 'remove@example.com']);
        $this->assertNotNull($found);

        $this->em->remove($found);
        $this->em->flush();

        $deleted = $repo->findOneBy(['email' => 'remove@example.com']);
        $this->assertNull($deleted);
    }
}
