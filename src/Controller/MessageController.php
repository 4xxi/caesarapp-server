<?php

namespace App\Controller;

use Symfony\Component\Routing\Annotation\Route;
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
     * @Route("/api/messages/{id}", name="messages_get", methods={"GET"})
     */
    public function show($id)
    {
        $message = $this->manager->get($id);
        if ($message) {
            return new Response($this->manager->serialize($message));
        }

        return new JsonResponse(['errors' => ['id' => 'Message not found']], 404);
    }

    /**
     * @Route("/api/messages", name="messages_create", methods={"POST"})
     * @Route("/api/messages/", name="messages_create_with_slash", methods={"POST"})
     */
    public function create(Request $request)
    {
        $message = new Message();
        $form = $this->createForm(MessageType::class, $message);

        if ($request->isMethod('POST')) {
            $messageArray = json_decode($request->getContent(), true);
            $form->submit($messageArray);

            if ($form->isSubmitted() && $form->isValid()) {
                $message = $form->getData();
                $message = $this->manager->create($message);

                return new Response($this->manager->serialize($message));
            }
        }

        $errors = $this->get('App\Form\ErrorView')->getFormErrorsAsArray($form);
        return new JsonResponse(['errors' => $errors], 400);
    }

    /**
     * @Route("/api/messages", name="messages_status", methods={"GET"})
     * @Route("/api/messages/", name="messages_status_with_slash", methods={"GET"})
     * @Route("/", name="homepage", methods={"GET"})
     */
    public function status()
    {
        return new JsonResponse(['status' => 'OK']);
    }
}
