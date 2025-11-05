<?php

namespace App\Tests\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class UserDeleteTest extends WebTestCase
{
    private function persistUser($client): User
    {
        $em = $client->getContainer()->get(EntityManagerInterface::class);
        $u = new User();
        $u->setEmail('del'.uniqid().'@example.com');
        $u->setNickname('to-delete');
        $u->setPassword('hash');
        $em->persist($u);
        $em->flush();
        return $u;
    }

    public function testDeleteRequiresAuth(): void
    {
        $client = static::createClient();
        $client->request('DELETE', '/api/users/delete');
        $this->assertResponseStatusCodeSame(401);
    }

    public function testAuthenticatedUserCanDeleteSelf(): void
    {
        $client = static::createClient();
        $user = $this->persistUser($client);

        $client->loginUser($user);
        $client->request('DELETE', '/api/users/delete');

        $this->assertResponseStatusCodeSame(200);
        $this->assertStringContainsString('User deleted', $client->getResponse()->getContent());
    }
}
