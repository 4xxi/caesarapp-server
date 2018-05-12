<?php

namespace App\Controller;

use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;

use App\Model\Message;
use App\Form\Type\MessageType;

class MessageController extends Controller
{

    protected $manager;

    /**
     * @param \App\Message\Manager
     */
    public function __construct(\App\Message\Manager $manager)
    {
        $this->manager = $manager;
    }

    /**
     * @Route("/api/messages/{id}", name="messages_get")
     * @Method({"GET"})
     */
    public function show($id)
    {
        $message = $this->manager->get($id);
        if ($message) {
            return new Response($this->manager->serialize($message));
        }

        return new JsonResponse(['errors' => ['id' => 'Message not found']]);
    }

    /**
     * @Route("/api/messages", name="messages_create")
     * @Route("/api/messages/", name="messages_create_with_slash")
     * @Method({"POST"})
     */
    public function create(Request $request)
    {
        $message = new Message();
        $form = $this->createForm(MessageType::class, $message);

        if ($request->isMethod('POST')) {

            $form->submit($request->request->get($form->getName()));

            if ($form->isSubmitted() && $form->isValid()) {
                $message = $form->getData();
                $message = $this->manager->create($message);
                return new Response($this->manager->serialize($message));
            }
        }

        $errors = $this->get('App\Form\ErrorView')->getFormErrorsAsArray($form);
        return new JsonResponse(['errors' => $errors]);
    }

    /**
     * @Route("/api/messages", name="messages_status")
     * @Route("/api/messages/", name="messages_status_with_slash")
     * @Route("/", name="homepage")
     * @Method({"GET"})
     */
    public function status()
    {
        return new JsonResponse(['status' => 'OK']);
    }

}
