<?php

namespace App\Tests\Integration\Infrastructure\Http\Controllers;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class RegisterUserControllerTest extends WebTestCase
{
    public function test_register_user_endpoint(): void
    {
        $client = static::createClient();
        
        $client->request(
            'POST',
            '/register',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'name' => 'Test User',
                'email' => 'test@example.com',
                'password' => 'password123'
            ])
        );

        $this->assertEquals(201, $client->getResponse()->getStatusCode());
        
        $responseData = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('user_id', $responseData);
        $this->assertArrayHasKey('message', $responseData);
    }
}