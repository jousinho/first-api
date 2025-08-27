<?php
namespace App\Tests\Functional\Infrastructure\Http;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ExampleControllerTest extends WebTestCase
{
    public function test_example()
    {   
        $client = static::createClient();
        $client->request(
            'GET',
            '/example',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json']
        );

        $this->assertResponseIsSuccessful();
        $this->assertJson($client->getResponse()->getContent());
    }
}