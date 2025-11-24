<?php

namespace App\Tests\Integration\Infrastructure\Http\Controllers;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class RegisterUserControllerTest extends WebTestCase
{
    public function test_register_user_with_valid_data_should_return_201(): void
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

    public function test_register_user_with_invalid_json_should_return_400(): void
    {
        $client = static::createClient();
        
        $client->request(
            'POST',
            '/register',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            'invalid-json{'
        );

        $this->assertEquals(400, $client->getResponse()->getStatusCode());
    }

    public function test_register_user_with_missing_data_should_return_400(): void
    {
        $client = static::createClient();
        
        $client->request(
            'POST',
            '/register',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'name' => 'Test User'
                // email and password missing
            ])
        );

        $this->assertEquals(400, $client->getResponse()->getStatusCode());
    }
}