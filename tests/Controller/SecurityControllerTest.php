<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class SecurityControllerTest extends WebTestCase
{
    public function testLoginPageLoads(): void
    {
        $client = static::createClient();
        $client->request('GET', '/login');
        $this->assertResponseStatusCodeSame(200);
    }

    public function testLoginRedirectsBackOnInvalidCredentials(): void
    {
        $client = static::createClient();
        $client->request('POST', '/login', [
            '_username' => 'fake@example.com',
            '_password' => 'wrongpass',
        ]);

        $this->assertResponseRedirects('/login');
    }
}
