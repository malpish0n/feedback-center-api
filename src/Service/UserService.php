<?php

namespace App\Service;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserService
{
    private EntityManagerInterface $em;
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(EntityManagerInterface $em, UserPasswordHasherInterface $passwordHasher)
    {
        $this->em = $em;
        $this->passwordHasher = $passwordHasher;
    }

    public function createUser(array $data): void
    {
        if (empty($data['email']) || empty($data['password']) || empty($data['nickname'])) {
            throw new \InvalidArgumentException('Missing required fields');
        }

        $user = new User();
        $user->setEmail($data['email']);
        $user->setNickname($data['nickname']);
        $hashed = $this->passwordHasher->hashPassword($user, $data['password']);
        $user->setPassword($hashed);

        $this->em->persist($user);
        $this->em->flush();
    }

}
