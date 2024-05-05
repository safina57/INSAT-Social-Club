<?php


namespace App\Controller;

use App\Entity\User;
use DateTime;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api')]
class EditProfileController extends AbstractController
{
    /**
     * @throws \Exception
     */
    #[Route('/edit-profile', name: 'edit_profile', methods: ['POST'])]
    public function UpdatePersonalDetails(Request $request, ManagerRegistry $doctrine): JsonResponse
    {
        $repository = $doctrine->getRepository(User::class);

        $sessionId = $request->request->get('sessionId');
        $session = $request->getSession();
        $session->setId($sessionId);
        $session->start();
        $id = $session->get('userId');
        $user = $repository->findOneBy(['id' => $id]);

        $fullName = $request->request->get('fullName') ?? '';
        $username = $request->request->get('username') ?? '';
        $birthDateString = $request->request->get('birthDate') ?? '';
        $birthDate = new DateTime($birthDateString);
        $bio = $request->request->get('bio') ?? '';
        $oldPassword = $request->request->get('oldPassword') ?? '';
        $newPassword = $request->request->get('newPassword') ?? '';



        $exist = $repository->findOneBy(['username' => $username]);

        if ($username && $exist && $exist->getId() !== $id){
            return $this->json(['success' => false, 'message' => 'Username already exists']);
        }
        if ($oldPassword && $this->verifyPassword($user, $id, $oldPassword)) {
            return $this->json(['success' => false, 'message' => 'Incorrect password']);
        }
        $entityManager = $doctrine->getManager();
        if ($fullName) {
            $user->setFullName($fullName);
        }
        if ($username) {
            $user->setUsername($username);
        }
        if ($birthDate) {
            $user->setBirthDate($birthDate);
        }
        if ($bio) {
            $user->setBio($bio);
        }
        if ($newPassword) {
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            $user->setPassword($hashedPassword);
        }
        $entityManager->persist($user);
        $entityManager->flush();

        return $this->json(['success' => 'Personal details updated successfully']);

    }

    #[Route('/DetailsFetch', name: 'DetailsFetch', methods: ['POST'])]
    public function fetchDetails(Request $request, ManagerRegistry $doctrine): JsonResponse
    {
        $repository = $doctrine->getRepository(User::class);

        $sessionId = $request->request->get('sessionId');
        $session = $request->getSession();
        $session->setId($sessionId);
        $session->start();
        $id = $session->get('userId');
        $user = $repository->findOneBy(['id' => $id]);

        $data = [];
        $data['fullName'] = $user->getFullName();
        $data['username'] = $user->getUsername();
        $data['email'] = $user->getEmail();
        $data['img'] = $user->getImage();

        if ($data){
            return $this->json(['success' => true, 'message' => 'Details fetched successfully', 'data' => $data]);
        }
        return $this->json(['success' => false, 'message' => 'Failed to fetch details']);

    }

    #[Route('/UploadAvatar', name: 'UploadAvatar', methods: ['POST'])]
    public function uploadAvatar(Request $request, ManagerRegistry $doctrine): JsonResponse
    {
        $repository = $doctrine->getRepository(User::class);

        $sessionId = $request->request->get('sessionId');
        $session = $request->getSession();
        $session->setId($sessionId);
        $session->start();
        $id = $session->get('userId');
        $user = $repository->findOneBy(['id' => $id]);

        $avatar = $request->files->get('avatar');
        $avatar->move($this->getParameter('upload_directory'), $avatar->getClientOriginalName());

        $previousImage = $user->getImage();
        if ($previousImage){
            unlink($this->getParameter('upload_directory').'/'.$previousImage);
        }
        $user->setImage($avatar->getClientOriginalName());
        $entityManager = $doctrine->getManager();
        $entityManager->persist($user);
        $entityManager->flush();

        return $this->json(['success' => true, 'message' => 'Avatar uploaded successfully', 'path' => $avatar->getClientOriginalName()]);
    }

    #[Route('/fetchAvatar', name: 'fetchAvatar', methods: ['POST'])]
    public function fetchAvatar(Request $request, ManagerRegistry $doctrine): JsonResponse
    {
        $repository = $doctrine->getRepository(User::class);

        $repository = $doctrine->getRepository(User::class);

        $sessionId = $request->request->get('sessionId');
        $session = $request->getSession();
        $session->setId($sessionId);
        $session->start();
        $id = $session->get('userId');
        $user = $repository->findOneBy(['id' => $id]);

        $path = $user->getImage();

        if ($path){
            return $this->json(['success' => true, 'message' => 'Avatar fetched successfully', 'path' => $path]);
        }
        return $this->json(['success' => false, 'message' => 'Failed to fetch avatar']);

    }

    private function verifyPassword($user, $id, $oldPassword) : bool
    {
        $hashedPassword = $user->getPassword();
        if(password_verify($oldPassword, $hashedPassword)){
            return false;
        }
        return true;
    }
}
