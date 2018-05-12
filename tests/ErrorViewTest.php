<?php

namespace App\Tests;

use PHPUnit\Framework\TestCase;

use App\Form\ErrorView;

use App\Model\Message;
use App\Form\Type\MessageType;

class ErrorViewTest extends ContainerAwareTest
{

    protected $dataWithNoRequestsLimit = [
        'message' => 'text',
        'secondsLimit' => 100,
    ];

    protected $dataWithNoMessageAndSecondsLimit = [
        'requestsLimit' => 5,
    ];

    protected $message;
    protected $form;

    protected $errorView;

    public function __construct()
    {
        parent::__construct();

        $this->message = new Message();
        $this->errorView = $this->get('App\Form\ErrorView');
    }

    public function buildForm($data)
    {
        $form = $this->container->get('form.factory')->create(MessageType::class, $this->message);
        $form->submit($data);
        return $form;
    }

    public function testdataWithNoRequestsLimit()
    {
        $form = $this->buildForm($this->dataWithNoRequestsLimit);
        $errors = $this->errorView->getFormErrorsAsArray($form);
        $this->assertArrayHasKey('requestsLimit', $errors);
        $this->assertArrayNotHasKey('secondsLimit', $errors);
        $this->assertArrayNotHasKey('message', $errors);

        $form = $this->buildForm($this->dataWithNoMessageAndSecondsLimit);
        $errors = $this->errorView->getFormErrorsAsArray($form);
        $this->assertArrayHasKey('secondsLimit', $errors);
        $this->assertArrayHasKey('message', $errors);
        $this->assertArrayNotHasKey('requestsLimit', $errors);
    }
}
