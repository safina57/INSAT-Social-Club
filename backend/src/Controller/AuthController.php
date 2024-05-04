<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
#[Route('/auth')]
class AuthController extends AbstractController
{
    #[Route('/login', name: 'login')]
    public function login(): Response
    {
        return $this->render('auth/login.html.twig');
    }
    #[Route('/signup', name: 'signup')]
    public function signup(): Response
    {
        return $this->render('auth/signup.html.twig');
    }
}
