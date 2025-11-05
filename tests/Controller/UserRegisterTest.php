<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class UserRegisterTest extends WebTestCase
{
    public function testRegisterFailsWithMissingFields(): void
    {
        $client = static::createClient();
        $client->request('POST', '/api/register', content: json_encode([
            'email' => 'test@example.com',
        ]));

        $this->assertResponseStatusCodeSame(400);
    }

    public function testRegisterSucceedsWithValidData(): void
    {
        $client = static::createClient();
        $payload = [
            'email' => 'user'.uniqid().'@example.com',
            'password' => 'secret123',
            'nickname' => 'tester',
        ];
        $client->request('POST', '/api/register', content: json_encode($payload));

        $this->assertResponseStatusCodeSame(201);
        $this->assertStringContainsString('User registered', $client->getResponse()->getContent());
    }
}
