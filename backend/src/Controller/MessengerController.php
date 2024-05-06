<?php

namespace App\Controller;

use App\Entity\Chat;
use App\Entity\User; // Add this line to import the User entity
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request; // Add this line to import the Request class
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route; // Update this line to import Route annotation

class MessengerController extends AbstractController
{

    public function getUsernameFromUserId(ManagerRegistry $doctrine, $user_id): string
    {
        $repository = $doctrine->getRepository(User::class);
        $user = $repository->find($user_id);
        return $user->getUsername();
    }

    public function sendMessage(ManagerRegistry $doctrine, Request $request, $message): bool
    {
        //getting sender user id from session
        $session = $request->getSession();
        $user_id = $session->get('userId');

        //getting username from user id
        $from_name = $this->getUsernameFromUserId($doctrine ,$user_id);

        //getting receiver name from session
        $to_name = $session->get('to_name');

        //saving message to database
        if(!empty($message) && !empty($to_name)){
            $entityManager = $doctrine->getManager();
            $newMessage = new Chat();
            $newMessage->setFromName($from_name);
            $newMessage->setToName($to_name);
            $newMessage->setMessage($message);
            $newMessage->setDate(new \DateTime());
            $entityManager->persist($newMessage);
            $entityManager->flush();
            return true;
        }
        else{
            return false;
        }
    }

    public function fetchMessages(ManagerRegistry $doctrine, Request $request): array|bool
    {
        $session = $request->getSession();
        if ($session->has('to_name')) {
            $to_name = $session->get('to_name');
            $user_id = $session->get('userId');
            $from_name = $this->getUsernameFromUserId($doctrine, $user_id);
            $repository = $doctrine->getRepository(Chat::class);
            $messages = $repository->findBy(['fromName' => $from_name, 'toName' => $to_name], ['date' => 'ASC']);
            return $messages;
        }
        return false;
    }

    #[Route('/messenger', name: 'app_messenger')]
    public function index(): Response
    {
        return $this->render('messenger/index.html.twig', [
            'controller_name' => 'MessengerController',
        ]);
    }
}
