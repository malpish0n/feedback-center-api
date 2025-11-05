<?php

namespace App\Tests\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserLoginTest extends WebTestCase
{
    private function seedUser($client, string $email, string $password): void
    {
        $container = $client->getContainer();
        $em = $container->get(EntityManagerInterface::class);
        $hasher = $container->get(UserPasswordHasherInterface::class);

        $user = new User();
        $user->setEmail($email);
        $user->setNickname('tester');
        $user->setPassword($hasher->hashPassword($user, $password));
        $em->persist($user);
        $em->flush();
    }

    public function testLoginFailsWithInvalidCredentials(): void
    {
        $client = static::createClient();
        $client->request('POST', '/api/login', content: json_encode([
            'email' => 'nope@example.com',
            'password' => 'wrong',
        ]));
        $this->assertResponseStatusCodeSame(401);
    }

    public function testLoginSucceeds(): void
    {
        $client = static::createClient();
        $email = 'user'.uniqid().'@example.com';
        $password = 'secret123';

        $this->seedUser($client, $email, $password);

        $client->request('POST', '/api/login', content: json_encode([
            'email' => $email,
            'password' => $password,
        ]));

        $this->assertResponseStatusCodeSame(200);
        $this->assertStringContainsString('"token":', $client->getResponse()->getContent());
    }
}
