<?php

namespace App\Controller;

use App\Service\MailerService;
use DateTime;
use Doctrine\Persistence\ManagerRegistry;
use Exception;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use App\Entity\User;

#[Route('/api')]
class AuthController extends AbstractController
{
    #[Route('/signup', name: 'signup', methods: ['POST'])]
    public function signup(Request $request, ManagerRegistry $doctrine): JsonResponse
    {
        $repository = $doctrine->getRepository(User::class);
        $email = $request->request->get('Email');
        $username = $request->request->get('Username');
        $existingUser =  $repository->userExist($username, $email);
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
    #[Route('/resetPasswordRequest', name: 'resetPasswordRequest', methods: ['POST'])]
    public function resetPasswordRequest(Request $request,ManagerRegistry $doctrine, MailerService $mailer): JsonResponse
    {
        $email = $request->request->get('email');
        $repository = $doctrine->getRepository(User::class);
        $exist = $repository->findOneBy(['email' => $email]);
        if ($exist) {
            $token = $this->generateToken();
            $data = ['email' => $email];
            $entityManager = $doctrine->getManager();
            $result = $repository->addToken($data, $token, $entityManager);
            if ($result) {
                $URL = "http://localhost:8080/login/passwordReset/" . $token;
                $result = $this->passwordResetEmail($email, $URL, $mailer);
                if ($result) {
                    return $this->json(['success' => true, 'message' => 'Password reset link sent to your email']);
                } else {
                    return $this->json(['success' => false, 'message' => 'Failed to send password reset link']);
                }
            } else {
                return $this->json(['success' => false, 'message' => 'Failed to add token']);
            }
        } else {
            return $this->json(['success' => false, 'message' => 'Email does not exist']);
        }
    }

    #[Route('/resetPassword', name: 'resetPassword', methods: ['POST'])]
    public function passwordReset(Request $request, ManagerRegistry $doctrine): JsonResponse
    {
        $token = $request->request->get('resetPasswordToken');
        $password = $request->request->get('password');

        $repository = $doctrine->getRepository(User::class);
        $user = $repository->findOneBy(['resetPasswordToken' => $token]);
        if ($user) {
            $user->setPassword(password_hash($password, PASSWORD_DEFAULT));
            $user->setResetPasswordToken(null);
            $entityManager = $doctrine->getManager();
            $entityManager->persist($user);
            $entityManager->flush();
            return $this->json(['success' => true, 'message' => 'Password reset successfully']);
        } else {
            return $this->json(['success' => false, 'message' => 'Invalid token']);
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
    #[Route('/login', name: 'login')]
    public function login(Request $request , ManagerRegistry $manager ): JsonResponse
    {
        $repository = $manager->getRepository(User::class);
        $username = $request->request->get('Username');
        $password = $request->request->get('Password');
        $rememberMe = $request->request->get('rememberMe');
        $user = $repository->findOneBy(['username' => $username]);
        $isAdmin = false;
        if ($user) {
            if (password_verify($password, $user->getPassword())) {
                $session = $request->getSession();
                $session->start();
                $sessionId=$request->getSession()->getId();
                $session->set('userId', $user->getId());
                $session->set('loggedIn', true);
                if($user->getEmail()=='insatsocialclubadm1n@gmail.com') {
                    $isAdmin = true;
                }
                $flag=$rememberMe==='true';
                if($flag) {
                    $token = $this->generateToken();
                    $expiry = time() + (60*60*24);
                    $cookie=new Cookie('rememberMe', $token, $expiry,'/',null,true,true,true,'None');
                    $data = ['username' => $username];
                    $entityManager = $manager->getManager();
                    $repository->addToken($data, $token, $entityManager);
                    $response = new JsonResponse(['success' => true, 'message' => 'Logged in successfully', 'sessionID' => $sessionId, 'userId' => $user->getId(), 'isAdmin' => $isAdmin]);
                    $response->headers->setCookie($cookie);
                    return $response;
                }
                else{
                    return $this->json(['success' => true, 'message' => 'Logged in successfully', 'sessionID' => $sessionId, 'userId' => $user->getId(), 'isAdmin' => $isAdmin]);
                    }
            }
            else{
                return $this->json(['success' => false, 'message' => 'Incorrect username or password']);
            }
        }
        return $this->json(['success' => false, 'message' => 'Incorrect username or password']);
    }

    private function generateToken(): string
    {
        // Generate random bytes
        $randomBytes = random_bytes(16);

        // Convert random bytes to hexadecimal string
        $token = bin2hex($randomBytes);
        return $token;
    }
    private function passwordResetEmail($email, $URL, $mailer): bool
    {
        $subject = "Reset Password";
        $body = "
            <html>
                <head>
                    <meta charset='UTF-8'>
                    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
                    <title>Password Reset</title>
            <style>
                body {
                    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
                    background-color: #f9f9f9;
                    color: #444;
                    padding: 20px;
                    line-height: 1.6;
                    margin: 0;
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
                .message {
                    background-color: #fff;
                    padding: 20px;
                    border-radius: 8px;
                    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
                    text-align: center;
                }
                h1 {
                    color: #28536B;
                    margin-bottom: 20px;
                    font-size: 36px;
                }
                p {
                    margin-bottom: 15px;
                    font-size: 20px;
                    color: #666;
                }
                a {
                    display: inline-block;
                    background-color: #28536B;
                    color: #fff !important;
                    font-size: 18px;
                    text-decoration: none;
                    padding: 10px 20px;
                    border-radius: 5px;
                    transition: background-color 0.3s ease;
                    text-decoration: none;
                }
                a:hover {
                    background-color: #1d3c4f;
                    color: #fff !important;
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
                            <h1>Password Reset</h1>
                            <p>Click the link below to reset your password</p>
                            <a href='$URL'>Reset Password</a>
                        </div>
                        <p class='footer'>Best regards,<br>INSAT Social Club</p>
                    </div>
                </body>
            </html>
";
        return $mailer->sendEmail($email, $subject, $body);
    }
    #[Route('/isLoggedIn', name: 'isLoggedIn', methods: ['POST'])]
    public function isLoggedIn(Request $request, ManagerRegistry $doctrine): JsonResponse
    {
        $sessionId = $request->request->get('sessionId');
        $session = $request->getSession();
        if ($sessionId) {
            $session->setId($sessionId);
            $session->start();
        }
        else {
            $session->start();
            $sessionId=$session->getId();
        }
        $result = $session->get('loggedIn');
        if ($result) {
            return $this->json(['success' => true, 'message' => 'User is logged in', 'sessionID' => $sessionId, 'userId' => $session->get('userId')]);
        }
        //check for remember me
        $result = $request->cookies->get('rememberMe');
        if ($result) {
            $token = $request->cookies->get('rememberMe');
            $repository = $doctrine->getRepository(User::class);
            $user = $repository->findOneBy(['rememberMeToken' => $token]);
            $isAdmin = false;
            if ($user) {
                $session->set('userId', $user->getId());
                $session->set('loggedIn', true);
                if($user->getEmail()=='insatsocialclubadm1n@gmail.com') {
                    $isAdmin = true;
                }
                return $this->json(['success' => true, 'message' => 'User is logged in', 'sessionID' => $sessionId, 'userId' => $user->getId(), 'isAdmin' => $isAdmin]);
            }
        }
        return $this->json(['success' => false, 'message' => 'User is not logged in']);
    }
    #[Route('/logout', name: 'logout', methods: ['POST'])]
    public function logout(Request $request, ManagerRegistry $doctrine): JsonResponse
    {
        $session = $request->getSession();
        $session->invalidate();
        $token = $request->cookies->get('rememberMe');
        $repository = $doctrine->getRepository(User::class);
        $userId = $request->request->get('userID');
        $user = $repository->findOneBy(['id' => $userId]);
        if ($user){
            $user->setStatus('Offline');
            $user->setRememberMeToken(null);
            $entityManager = $doctrine->getManager();
            $entityManager->persist($user);
            $entityManager->flush();
        }
        if ($token) {

            $response = new JsonResponse(['success' => true, 'message' => 'Logged out successfully']);
            $expiry = time() - 3600;
            $cookie=new Cookie('rememberMe', '', $expiry,'/',null,true,true,true,'None');
            $response->headers->setCookie($cookie);
            return $response;
        }
        return $this->json(['success' => true, 'message' => 'Logged out successfully']);
    }

}
