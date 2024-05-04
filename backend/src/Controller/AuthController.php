<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
#[Route('/api')]
class AuthController extends AbstractController
{
    #[Route('/login', name: 'login')]
    public function login(): JsonResponse
    {
        $email = "frefefe";
        return $this->json($email);
    }
    #[Route('/login1', name: 'login1', methods: ['POST'],)]
    public function login1(Request $request): JsonResponse
    {
        $email = $request->request->get('email');
        $email = $email."test";
        return $this->json($email);
    }


}
