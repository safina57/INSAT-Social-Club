<?php

namespace App\Controller;

use App\Entity\Post;
use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('/homepage')]
class HomePageController extends AbstractController
{
    #[Route('/getAllPosts', name: 'getAllPosts')]
    public function getAllPosts(EntityManagerInterface $entityManager): JsonResponse
    {
        $posts = $entityManager->getRepository(Post::class)->findAll();
        $posts = array_reverse($posts);
        if (!$posts) {
            throw $this->createNotFoundException(
                'No posts found'
            );
        }




        return $this->json($posts);

    }
    #[Route('/addPost', name: 'addPost',methods: ['POST'])]
    public function addPost(EntityManagerInterface $entityManager, Request $request): JsonResponse
    {
        $post = new Post();

        $user = $entityManager->getRepository(User::class)->find($request->get('user_id'));

        $post->setCaption($request->get('Content'))
            ->setReactCount(0)
            ->setUser($user);
        if(!$request->get('Media')){
            $post->setMedia($request->get('Media'));
        }



        $entityManager->persist($post);
        $entityManager->flush();


        return $this->json([
            'success' => true,
            'message' => 'Post added successfully',
            'post' => [
                'id' => $post->getId(),
                'caption' => $post->getCaption(),
                'reactCount' => $post->getReactCount(),
                'media' => $post->getMedia(),

            ]
        ]);
    }

}
