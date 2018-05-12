<?php

namespace App\Tests;

use PHPUnit\Framework\TestCase;

class ContainerAwareTest extends TestCase
{

    /**
     * @var App\Kernel
     */
    protected $kernel;
    /**
     * @var Symfony\Component\DependencyInjection\ContainerInterface
     */
    protected $container;

    public function __construct()
    {
        $this->kernel = new \App\Kernel('test', true);
        $this->kernel->boot();
        $this->container = $this->kernel->getContainer();

        $this->errorView = $this->get('App\Form\ErrorView');

        parent::__construct();
    }

    /**
     * Get service from Container
     * @param string
     * @return mixed
     */
    public function get($serviceId)
    {
        return $this->container->get($serviceId);
    }

}
