<?php

namespace App\Tests;

use PHPUnit\Framework\TestCase;

abstract class ContainerAwareTest extends TestCase
{

    /**
     * @var App\Kernel
     */
    protected static $kernel;
    /**
     * @var Symfony\Component\DependencyInjection\ContainerInterface
     */
    protected static $container;

    public static function setUpBeforeClass()
    {
        self::$kernel = new \App\Kernel('test', true);
        self::$kernel->boot();
        self::$container = self::$kernel->getContainer();
    }

    /**
     * Get service from Container
     * @param string
     * @return mixed
     */
    public function get($serviceId)
    {
        return self::$container->get($serviceId);
    }

}
