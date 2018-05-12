<?php

namespace App\Tests;

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

    /**
     * @var Message
     */
    protected $message;
    /**
     * @var \Symfony\Component\Form\Form
     */
    protected $form;
    /**
     * @var \App\Form\ErrorView
     */
    protected $errorView;

    public function setUp()
    {
        $this->errorView = self::$container->get('App\Form\ErrorView');
    }

    /**
     * @var array $data
     * @return \Symfony\Component\Form\Form
     */
    public function buildForm($data)
    {
        $form = self::$container->get('form.factory')->create(MessageType::class, $this->message);
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
