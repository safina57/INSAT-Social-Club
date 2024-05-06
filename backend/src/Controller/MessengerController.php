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

class MessengerController extends AbstractController
{
    public function getUsernameFromUserId(ManagerRegistry $doctrine, $user_id): string
    {
        $userRepository = $doctrine->getRepository(User::class);
        $user = $userRepository->find($user_id);

        if (!$user) {
            return ''; // Handle case where user is not found
        }

        return $user->getUsername();
    }

    #[Route('/api/send-message', name: 'api_send_message', methods: ['POST'])]
    public function sendMessage(ManagerRegistry $doctrine, Request $request, string $message): JsonResponse
    {
        $session = $request->getSession();

        $userId = $session->get('userId');
        $toName = $session->get('to_name');

        if (!$userId || !$toName || empty($message)) {
            return new JsonResponse(['success' => false, 'message' => 'Error occurred while sending message']);
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

    #[Route('/api/fetch-messages', name: 'api_fetch_messages', methods: ['GET'])]
    public function fetchMessages(ManagerRegistry $doctrine, Request $request): JsonResponse
    {
        $session = $request->getSession();

        if (!$session->has('to_name')) {
            return false; // No recipient specified
        }

        // Get the recipient's name and the sender name
        $toName = $session->get('to_name');
        $userId = $session->get('userId');
        $fromName = $this->getUsernameFromUserId($doctrine, $userId);

        // Fetch messages from the database
        $chatRepository = $doctrine->getRepository(Chat::class);
        $queryBuilder = $chatRepository->createQueryBuilder('c');

        $messages = $queryBuilder->where(
            $queryBuilder->expr()->orX(
                $queryBuilder->expr()->andX(
                    $queryBuilder->expr()->eq('c.fromName', ':fromName'),
                    $queryBuilder->expr()->eq('c.toName', ':toName')
                ),
                $queryBuilder->expr()->andX(
                    $queryBuilder->expr()->eq('c.fromName', ':toName'),
                    $queryBuilder->expr()->eq('c.toName', ':fromName')
                )
            )
        )
            ->setParameters(['fromName' => $fromName, 'toName' => $toName])
            ->orderBy('c.date', 'ASC')
            ->getQuery()
            ->getResult();

        return new JsonResponse(['success' => true, 'messages' => $messages]);
    }

    #[Route('/messenger', name: 'app_messenger')]
    public function index(): Response
    {
        return $this->render('messenger/index.html.twig', [
            'controller_name' => 'MessengerController',
        ]);
    }
}
