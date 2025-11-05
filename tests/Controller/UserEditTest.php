<?php

namespace App\Tests\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class UserEditTest extends WebTestCase
{
    private function persistUser($client): User
    {
        $em = $client->getContainer()->get(EntityManagerInterface::class);
        $u = new User();
        $u->setEmail('edit'.uniqid().'@example.com');
        $u->setNickname('to-edit');
        $u->setPassword('hash');
        $em->persist($u);
        $em->flush();
        return $u;
    }

    public function testEditRequiresAuth(): void
    {
        $client = static::createClient();
        $client->request('PUT', '/api/users/edit', content: json_encode(['nickname' => 'x']));
        $this->assertResponseStatusCodeSame(401);
    }

    public function testAuthenticatedUserCanEdit(): void
    {
        $client = static::createClient();
        $user = $this->persistUser($client);

        $client->loginUser($user);
        $newNick = 'updatedNick';

        $client->request('PUT', '/api/users/edit', content: json_encode(['nickname' => $newNick]));
        $this->assertResponseIsSuccessful();
        $this->assertStringContainsString('User updated', $client->getResponse()->getContent());
    }
}
