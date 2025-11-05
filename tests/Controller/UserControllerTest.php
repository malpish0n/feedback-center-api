<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use App\Entity\User;

class UserControllerTest extends WebTestCase
{
    public function testProfileEndpointRequiresAuthentication(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/profile');
        $this->assertResponseStatusCodeSame(401);
    }

    public function testAuthenticatedUserCanAccessProfile(): void
    {
        $client = static::createClient();
        $user = new User();
        $user->setEmail('test@example.com');
        $user->setNickname('tester');
        $client->loginUser($user);

        $client->request('GET', '/api/profile');
        $this->assertResponseIsSuccessful();
    }
}
