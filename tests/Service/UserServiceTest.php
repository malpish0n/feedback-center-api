<?php

declare(strict_types=1);

namespace App\Tests\Service;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Service\UserService;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

final class UserServiceTest extends TestCase
{
    private EntityManagerInterface $em;
    private UserPasswordHasherInterface $hasher;
    private UserRepository $userRepo;

    protected function setUp(): void
    {
        $this->em = $this->createMock(EntityManagerInterface::class);
        $this->hasher = $this->createMock(UserPasswordHasherInterface::class);
        $this->userRepo = $this->createMock(UserRepository::class);
    }

    private function makeService(): UserService
    {
        return new UserService($this->em, $this->hasher, $this->userRepo);
    }

    public function testCreateUserPersistsAndFlushes(): void
    {
        $data = ['email' => 'john@example.com', 'password' => 'Secret123!', 'nickname' => 'john'];

        $this->hasher
            ->method('hashPassword')
            ->willReturn('hashed');

        $this->em->expects($this->once())->method('persist')->with($this->callback(
            function ($u): bool {
                return $u instanceof User
                    && $u->getEmail() === 'john@example.com'
                    && $u->getNickname() === 'john';
            }
        ));
        $this->em->expects($this->once())->method('flush');

        $service = $this->makeService();
        $service->createUser($data);

        $this->assertTrue(true);
    }

    public function testCreateUserHashesPassword(): void
    {
        $data = ['email' => 'a@b.c', 'password' => 'Plain123', 'nickname' => 'a'];

        $this->hasher
            ->expects($this->once())
            ->method('hashPassword')
            ->with($this->isInstanceOf(User::class), 'Plain123')
            ->willReturn('hashed');

        $this->em->expects($this->once())->method('persist');
        $this->em->expects($this->once())->method('flush');

        $service = $this->makeService();
        $service->createUser($data);

        $this->assertTrue(true);
    }

    public function testCreateUserThrowsOnMissingEmail(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $data = ['password' => 'x', 'nickname' => 'y'];
        $service = $this->makeService();
        $service->createUser($data);
    }
}
