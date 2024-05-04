<?php

namespace App\Controller;

use App\Entity\Post;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\ORM\EntityManagerInterface;

#[Route('/homepage')]
class HomePageController extends AbstractController
{
    #[Route('/getAllPosts', name: 'getAllPosts')]
    public function getAllPosts(EntityManagerInterface $entityManager): JsonResponse
    {
        //$posts = $entityManager->getRepository(Post::class)->findAll();

        /*if (!$posts) {
            throw $this->createNotFoundException(
                'No posts found'
            );
        }*/
        $posts="1";

        return $this->json($posts);

    }

}
