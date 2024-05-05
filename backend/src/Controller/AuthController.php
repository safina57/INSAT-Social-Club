<?php

namespace App\Controller;

use App\Service\MailerService;
use DateTime;
use Doctrine\Persistence\ManagerRegistry;
use Exception;
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
    #[Route('/verify', name: 'verify', methods: ['POST'])]
    public function verify(Request $request, MailerService $mailer): JsonResponse
    {
        $email = $request->request->get('Email');
        $verificationCode = $this->generateVerificationCode();
        $subject = "Verification";
        $body = "
    <html>
    <head>
        <style>
            body {
                font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
                background-color: #f9f9f9;
                color: #444;
                padding: 20px;
                line-height: 1.6;
            }
            .container {
                max-width: 600px;
                margin: 0 auto;
            }
            .logo {
                text-align: center;
                margin-bottom: 20px;
            }
            .logo img {
                max-width: 150px;
                height: auto;
                border-radius: 50%;
                box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
                transition: transform 0.3s ease;
            }
            h1 {
                color: #28536B;
                margin-bottom: 20px;
                text-align: center;
                font-size: 36px;
            }
            p {
                margin-bottom: 15px;
                font-size: 20px;
            }
            .message {
                background-color: #fff;
                padding: 20px;
                border-radius: 8px;
                box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            }
            .footer {
                text-align: center;
                margin-top: 20px;
                color: #666;
            }
        </style>
    </head>
    <body>
        <div class='container'>
        <div class='logo'>
            <img src='https://i.imgur.com/W911Nk3.gif' alt='INSAT: Social Club Logo'>
        </div>
            <div class='message'>
                <h1>Email Verification</h1>
                <p>Dear User,</p>
                <p>Your verification code is: <strong>$verificationCode</strong></p>
                <p>Please use this code to complete the registration process.</p>
                <p>If you did not request this verification code, please ignore this email.</p>
            </div>
            <p class='footer'>Best regards,<br>INSAT Social Club</p>
        </div>
    </body>
    </html>
";
        $sent = $mailer->sendEmail($email, $subject, $body);
        if ($sent) {
            return $this->json(['success' => true, 'message' => 'Verification code sent to your email', 'code' => $verificationCode]);
        } else {
            return $this->json(['success' => false, 'message' => 'Failed to send verification code']);
        }
    }

    /**
     * @throws Exception
     */
    #[Route('/verificationProcess', name: 'verificationProcess', methods: ['POST'])]
    public function verificationProcess(Request $request, ManagerRegistry $doctrine): JsonResponse
    {
        $code = $request->request->get('code');
        $verification = $request->request->get('verificationCode');
        $fullName = $request->request->get('FullName');

        $email = $request->request->get('Email');

        $username = $request->request->get('Username');

        $password = $request->request->get('Password');
        $password = password_hash($password, PASSWORD_DEFAULT);

        $birthDateString = $request->request->get('BirthDate');
        $birthDate = new DateTime($birthDateString);

        $result = $code === $verification;
        if ($result) {
            $user = new User();
            $entityManager = $doctrine->getManager();
            $user->setFullName($fullName);
            $user->setEmail($email);
            $user->setUsername($username);
            $user->setPassword($password);
            $user->setBirthDate($birthDate);
            $entityManager->persist($user);
            $entityManager->flush();
            return $this->json(['success' => true, 'message' => 'Signed up successfully']);
        } else {
            return $this->json(['success' => false, 'message' => 'Verification code is incorrect']);
        }
    }




    private function generateVerificationCode(): string
    {
        $characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $verificationCode = '';
        $length = strlen($characters);
        for ($i = 0; $i < 6; $i++) {
            $verificationCode .= $characters[rand(0, $length - 1)];
        }
        return $verificationCode;
    }
}
