<?php

namespace App\Controller;

use App\Entity\Chat;
use App\Entity\User;
use DateTime;
use Doctrine\Persistence\ManagerRegistry;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
#[Route('/messengerApi')]
class MessengerController extends AbstractController
{

    private function getUsernameFromUserId(ManagerRegistry $doctrine, $user_id): string
    {
        $userRepository = $doctrine->getRepository(User::class);
        $user = $userRepository->find($user_id);
        return $user->getUsername();
    }

    #[Route('/all-users', name: 'api_all_users', methods: ['POST'])]
    public function getAllUsers(ManagerRegistry $doctrine, Request $request): JsonResponse{
        $userRepository = $doctrine->getRepository(User::class);
        $sessionId = $request->request->get('sessionId');
        $session = $request->getSession();
        $session->setId($sessionId);
        $session->start();
        $userId = $session->get('userId');
        $username = $this->getUsernameFromUserId($doctrine, $userId);
        // Fetch all users from the database except the current user and the admin
        $queryBuilder = $userRepository->createQueryBuilder('u');
        $email = 'insatsocialclubadm1n@gmail.com';
        $allUsers = $queryBuilder->where($queryBuilder->expr()->andX(
                $queryBuilder->expr()->neq('u.email', ':email'),
                $queryBuilder->expr()->neq('u.username', ':username')
            ))->setParameter('username', $username)
            ->setParameter('email', $email)
            ->getQuery()
            ->getResult();
        return $this->json($allUsers);
        //return new JsonResponse(['success' => true, 'users' => $allUsers]);
    }

    #[Route('/send-message', name: 'api_send_message', methods: ['POST'])]
    public function sendMessage(ManagerRegistry $doctrine, Request $request): JsonResponse
    {

        $sessionId = $request->request->get('sessionId');
        $session = $request->getSession();
        $session->setId($sessionId);
        $session->start();
        $userId = $session->get('userId');
        $toName = $session->get('to_name');
        $message = $request->request->get('message');
        if (!$userId || !$toName || empty($message)) {
            return new JsonResponse(['success' => false, 'message' => 'Error occurred while sending message1']);
        }

        $fromName = $this->getUsernameFromUserId($doctrine, $userId);

        $entityManager = $doctrine->getManager();
        $newMessage = new Chat();
        $newMessage->setFromName($fromName);
        $newMessage->setToName($toName);
        $newMessage->setMessage($message);
        $newMessage->setDate(new DateTime());

        try {
            $entityManager->persist($newMessage);
            $entityManager->flush();
            return new JsonResponse(['success' => true, 'message' => 'Message sent successfully']);
        } catch (Exception $e) {
            return new JsonResponse(['success' => false, 'message' => 'Error occurred while sending message']);
        }
    }

    #[Route('/fetch-messages', name: 'api_fetch_messages', methods: ['POST'])]
    public function fetchMessages(ManagerRegistry $doctrine, Request $request): JsonResponse
    {
        $sessionId = $request->request->get('sessionId');
        $username = $request->request->get('userName');
        $session = $request->getSession();
        $session->setId($sessionId);
        $session->start();
        $session->set('to_name', $username);
        if (!$session->has('to_name')) {
            return new JsonResponse(['success' => false, 'message' => 'No recipient selected']);
        }
        // Get the recipient's name from the session
        $toName = $session->get('to_name');
        $userId = $session->get('userId');
        $fromName = $this->getUsernameFromUserId($doctrine, $userId);
        // Fetch messages from the database
        $chatRepository = $doctrine->getRepository(Chat::class);
        $queryBuilder = $chatRepository->createQueryBuilder('c');

        $messages = $queryBuilder->where(
            $queryBuilder->expr()->orX(
                $queryBuilder->expr()->andX(
                    $queryBuilder->expr()->eq('c.from_name', ':fromName'),
                    $queryBuilder->expr()->eq('c.to_name', ':toName')
                ),
                $queryBuilder->expr()->andX(
                    $queryBuilder->expr()->eq('c.from_name', ':toName'),
                    $queryBuilder->expr()->eq('c.to_name', ':fromName')
                )
            )
        )
            ->setParameter('fromName', $fromName)
            ->setParameter('toName', $toName)
            ->orderBy('c.date', 'ASC')
            ->getQuery()
            ->getResult();

        return $this->json($messages);
    }

}