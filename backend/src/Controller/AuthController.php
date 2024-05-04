<?php

namespace App\Controller;

use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use App\Entity\User;

#[Route('/api')]
class AuthController extends AbstractController
{
    #[Route('/login', name: 'login')]
    public function login(): JsonResponse
    {
        $email = "frefefe";
        return $this->json($email);
    }

    #[Route('/login1', name: 'login1', methods: ['POST'])]
    public function login1(Request $request): JsonResponse
    {
        $email = $request->request->get('email');
        $email = $email."test";
        return $this->json($email);
    }

    #[Route('/signup', name: 'signup', methods: ['POST'])]
    public function signup(Request $request, ManagerRegistry $doctrine): JsonResponse
    {
        $repository = $doctrine->getRepository(User::class);
        $email = $request->request->get('Email');
        $username = $request->request->get('Username');
        $existingUser =  $repository->findOneBy(['email' => $email, 'username' => $username]);
        if (!$existingUser) {
            return $this->json(['success' => true, 'message' => 'Username and Email are available']);
        } else {
            return $this->json(['success' => false, 'message' => 'Username or Email already exists']);
        }
    }
}
