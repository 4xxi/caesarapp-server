<?php

namespace App\Tests\Message;
use App\Tests\ContainerAwareTest;

use App\Model\Message;

class ManagerTest extends ContainerAwareTest
{

    /**
     * @var \App\Message\Manager
     */
    protected $manager;

    public function setUp()
    {
        $this->manager = $this->get('App\Message\Manager');
    }

    public function buildMessage($text = 'Text', $requestsLimit = 2, $secondsLimit = 100)
    {
        $message = new Message();
        $message->setMessage($text);
        $message->setRequestsLimit($requestsLimit);
        $message->setSecondsLimit($secondsLimit);
        $message->setupExpiration();
        return $message;
    }

    public function testSerialization()
    {
        $message = $this->buildMessage();
        $serialized = $this->manager->serialize($message);
        $deserializedMessage = $this->manager->deserialize($serialized);

        /**
         * Deal with serialization of DateTime objects
         * $this->assertEquals($message, $deserializedMessage;
         */
        $this->assertEquals($message->getId(), $deserializedMessage->getId());
        $this->assertEquals($message->getMessage(), $deserializedMessage->getMessage());
        $this->assertEquals($message->getRequestsLimit(), $deserializedMessage->getRequestsLimit());
        $this->assertEquals($message->getSecondsLimit(), $deserializedMessage->getSecondsLimit());
    }

    public function getKeysExamples()
    {
        return [
            'starts with letter' => ['abc123', 'messages:abc123', 'limits:abc123'],
            'starts with number' => ['123abc', 'messages:123abc', 'limits:123abc'],
        ];
    }

    /**
     * @dataProvider getKeysExamples
     */
    public function testBuilders($id, $redisId, $limitId)
    {
        $this->assertEquals($this->manager->buildRedisId($id), $redisId);
        $this->assertEquals($this->manager->buildLimitId($id), $limitId);
    }

    /**
     * @return Message
     */
    public function testCreate()
    {
        $message = $this->buildMessage();
        $message = $this->manager->create($message);
        $this->assertNotNull($message->getId());
        $this->assertNotNull($message->getExpires());
        return $message;
    }

    /**
     * @depends testCreate
     * @param Message $message
     * @return Message
     */
    public function testGet(Message $message)
    {
        $deserializedMessage = $this->manager->get($message->getId());
        $this->assertEquals($deserializedMessage->getMessage(), $message->getMessage());

        return $deserializedMessage;
    }

    /**
     * @depends testGet
     * @param Message $message
     * @return Message
     */
    public function testDecreasedLimit(Message $message)
    {
        $redis = self::$container->get('snc_redis.default');
        $requestsLimit = $redis->get($this->manager->buildLimitId($message->getId()));
        $this->assertEquals(1, $requestsLimit);

        return $message;
    }

    /**
     * @depends testDecreasedLimit
     * @param Message $message
     */
    public function testDel(Message $message)
    {
        $deserializedMessage = $this->manager->get($message->getId());
        $deserializedMessage = $this->manager->get($message->getId());
        $redis = self::$container->get('snc_redis.default');
        $requestsLimit = $redis->get($this->manager->buildLimitId($message->getId()));
        $this->assertNull($requestsLimit);
    }

}
