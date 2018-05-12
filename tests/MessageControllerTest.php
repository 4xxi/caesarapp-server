<?php

namespace App\Tests;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class MessageControllerTest extends WebTestCase
{

    /**
     * It just works...
     */
    public function testStatus()
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/api/messages');

        $this->assertSame(200, $client->getResponse()->getStatusCode());
        $response = (array)json_decode($client->getResponse()->getContent());

        $this->assertSame('OK', $response['status']);
    }

    /**
     * Test cases:
     * 1. Empty POST request to /api/messages. Should trigger 3 errors.
     * 2. Not-full POST request. Should trigger a proper error.
     */
    public function testNewMessageFail()
    {
        $client = static::createClient();
        $crawler = $client->request('POST', '/api/messages');

        $response = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('errors', $response);

        $this->assertArrayHasKey('message', $response['errors']);
        $this->assertArrayHasKey('secondsLimit', $response['errors']);
        $this->assertArrayHasKey('requestsLimit', $response['errors']);

        $client->request('POST', '/api/messages', [
            'message' => [
                'message' => 'text',
                'requestsLimit' => 1,
            ]
        ]);

        $response = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('errors', $response);
        $this->assertArrayHasKey('secondsLimit', $response['errors']);
    }

    /**
     * Should create a new message with unique ID.
     */
    public function testNewMessageSuccess()
    {
        $client = static::createClient();
        $crawler = $client->request('POST', '/api/messages', [
            'message' => [
                'message' => 'text',
                'secondsLimit' => 10,
                'requestsLimit' => 5,
            ]
        ]);

        $response = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayNotHasKey('error', $response);
        $this->assertArrayHasKey('id', $response);
        $this->assertArrayHasKey('message', $response);
        $this->assertArrayHasKey('secondsLimit', $response);
        $this->assertArrayHasKey('requestsLimit', $response);
        $this->assertArrayHasKey('expires', $response);
        $this->assertNotNull($response['expires']);

        $this->assertEquals('text', $response['message']);
        $this->assertEquals(5, $response['requestsLimit']);
        $this->assertEquals(10, $response['secondsLimit']);
        $this->assertNotNull($response['id']);
    }

    /**
     * Should create a new message with unique ID and check GET request.
     */
    public function testCreateAndGet()
    {
        $client = static::createClient();
        $crawler = $client->request('POST', '/api/messages', [
            'message' => [
                'message' => 'text',
                'secondsLimit' => 100,
                'requestsLimit' => 5,
            ]
        ]);

        $response = json_decode($client->getResponse()->getContent(), true);
        $id = $response['id'];

        $client->request('GET', '/api/messages/'.$id);
        $response = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayNotHasKey('errors', $response);
        $this->assertArrayHasKey('id', $response);
        $this->assertArrayHasKey('message', $response);
        $this->assertArrayHasKey('secondsLimit', $response);
        $this->assertArrayHasKey('requestsLimit', $response);
        $this->assertArrayHasKey('expires', $response);
    }

    /**
     * Test that the messages are expired automatically based on secondsLimit parameter
     */
    public function testSecondsLimit()
    {
        $client = static::createClient();
        $crawler = $client->request('POST', '/api/messages', [
            'message' => [
                'message' => 'text',
                'secondsLimit' => 1,
                'requestsLimit' => 5,
            ]
        ]);

        $response = json_decode($client->getResponse()->getContent(), true);
        $id = $response['id'];

        sleep(1);

        $client->request('GET', '/api/messages/'.$id);
        $response = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('errors', $response);
    }

    public function testRequestsLimit()
    {
        $client = static::createClient();
        $crawler = $client->request('POST', '/api/messages', [
            'message' => [
                'message' => 'text',
                'secondsLimit' => 100,
                'requestsLimit' => 2,
            ]
        ]);

        $response = json_decode($client->getResponse()->getContent(), true);
        $id = $response['id'];

        /**
         * After second try it still should work.
         */
        $client->request('GET', '/api/messages/'.$id);
        $client->request('GET', '/api/messages/'.$id);
        $response = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayNotHasKey('errors', $response);
        $this->assertArrayHasKey('id', $response);

        /**
         * Should not work on 3rd try
         */
        $client->request('GET', '/api/messages/'.$id);
        $response = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('errors', $response);

    }

}
