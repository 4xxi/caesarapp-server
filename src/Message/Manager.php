<?php

namespace App\Message;

use App\Model\Message;
use \Predis\Client;
use Symfony\Component\Serializer\SerializerInterface;

class Manager
{

    /**
     * @var Client
     */
    protected $redis;
    /**
     * @var SerializerInterface $serializer
     */

    public function __construct(Client $redis, SerializerInterface $serializer)
    {
        $this->redis = $redis;
        $this->serializer = $serializer;
    }

    /**
     * @param Message $message
     * @return Message
     */
    public function create(Message $message)
    {
        $id = bin2hex(random_bytes(20));
        $message->setId($id);
        $this->redis->set('message:'.$message->getId(), $this->serialize($message));
        $this->redis->expire('message:'.$message->getId(), $message->getSecondsLimit());

        return $message;
    }

    /**
     * @param string $id
     * @return Message|null
     */
    public function get($id)
    {
        $json = $this->redis->get('message:'.$id);
        if (is_null($json)) {
            return null;
        }

        $message = $this->deserialize($json);
        return $message;
    }
    
    /**
     * @param Message $message
     * @return string
     */
    public function serialize(Message $message)
    {
        return $this->serializer->serialize($message, 'json');
    }

    /**
     * @param string $data
     * @return Message
     */
    public function deserialize($data)
    {
        return $this->serializer->deserialize($data, Message::class, 'json');
    }


}
