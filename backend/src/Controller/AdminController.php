<?php

declare(strict_types=1);

namespace App\Controller;

use App\Repository\PostRepository;
use App\Entity\User;
use App\Entity\Post;
use App\Entity\Report;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\ORM\EntityManagerInterface;
#[Route('/admin_api')]
class AdminController extends AbstractController
{
    #[Route('/getAll/{tableName}', name:'getAll',methods: ['GET'])]
    public function getAll(string $tableName, ManagerRegistry $doctrine): JsonResponse
    {
        if (!$this->isValidTableName($tableName)) {
            return new JsonResponse(['error' => 'Invalid table name'], Response::HTTP_BAD_REQUEST);
        }
        $repository = match (strtolower($tableName)) {
            'post' => $doctrine->getRepository(Post::class),
            'user' => $doctrine->getRepository(User::class),
            'report' => $doctrine->getRepository(Report::class),
            default => throw new \InvalidArgumentException('Invalid entity class name: ' . $tableName),
        };
        $entities = $repository->findAll();

        return $this->json($entities);
    }

    #[Route('/deleteRow/{tableName}/{id}', name:'deleteRow',methods: ['POST'])]
    public function deleteRowAction(string $tableName, int $id, EntityManagerInterface $entityManager): JsonResponse
    {
        if (!$this->isValidTableName($tableName)) {
            return new JsonResponse(['error' => 'Invalid table name'], Response::HTTP_BAD_REQUEST);
        }
        $repository = match (strtolower($tableName)) {
            'post' => $entityManager->getRepository(Post::class),
            'user' => $entityManager->getRepository(User::class),
            'report' => $entityManager->getRepository(Report::class),
            default => throw new \InvalidArgumentException('Invalid entity class name: ' . $tableName),
        };
        $entity = $repository->find($id);

        if (!$entity) {
            return new JsonResponse(['error' => 'Entity not found'], Response::HTTP_NOT_FOUND);
        }
        $entityManager->remove($entity);
        $entityManager->flush();
        return new JsonResponse(['success' => 'Entity deleted successfully']);
    }

    #[Route('getCommentsByPostId/{postId}', name:'getCommentsByPostId',methods: ['GET'])]
    public function getCommentsByPostId(string $postId, EntityManagerInterface $entityManager): JsonResponse
    {
        $comments = $entityManager->getRepository('App\Entity\Comment')->findBy(['postId' => $postId]);
        return $this->json($comments);
    }
    #[Route('getValue/{tableName}/{id}', name:'getValue',methods: ['GET'])]
    public function getValue(string $tableName, int $id, EntityManagerInterface $entityManager): JsonResponse
    {
        if (!$this->isValidTableName($tableName))
        {
            return new JsonResponse(['error' => 'Invalid table name'], Response::HTTP_BAD_REQUEST);
        }
        $repository = match (strtolower($tableName)) {
            'post' => $entityManager->getRepository(Post::class),
            'user' => $entityManager->getRepository(User::class),
            'report' => $entityManager->getRepository(Report::class),
            default => throw new \InvalidArgumentException('Invalid entity class name: ' . $tableName),
        };
        $element = $repository->find($id);
        return $this->json($element);
    }
    private function isValidTableName(string $tableName): bool
    {
        $allowedTableNames = ['post', 'report', 'user'];
        return in_array(strtolower($tableName), $allowedTableNames);
    }

    #[Route('/postActivity', name: 'postActivity', methods: ['GET'])]
    public function postActivity(PostRepository $postRepository): JsonResponse
    {
        $postActivityData = $postRepository->getTotalPostsPerDay();
        return $this->json($postActivityData);
    }
}
