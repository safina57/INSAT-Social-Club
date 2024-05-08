<?php

declare(strict_types=1);

namespace App\Controller;

use Symfony\Component\HttpFoundation\Request;
use App\Entity\Comment;
use App\Entity\React;
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
        if (strtolower($tableName)=='post') {
            $media = $entity->getMedia();
            //check how many times the media is used
            $numOfMediaUsed = $entityManager->getRepository(Post::class)->findBy(['media'=>$media]);
            $mediaUsedMoreThanOnce = count($numOfMediaUsed) > 1;
            if ($media && !$mediaUsedMoreThanOnce) {
                unlink($this->getParameter('upload_directory_post') . '/' . $media);
            }
            $comments = $entityManager->getRepository(Comment::class)->findBy(['Post'=>$entity]);
            foreach($comments as $comment){
                $entityManager->remove($comment);
            }
            $reacts = $entityManager->getRepository(React::class)->findBy(['Post'=>$entity]);
            foreach($reacts as $react){
                $entityManager->remove($react);
            }
        }
        if (!$entity) {
            return new JsonResponse(['error' => 'Entity not found'], Response::HTTP_NOT_FOUND);
        }
        $entityManager->remove($entity);
        $entityManager->flush();
        return new JsonResponse(['success' => 'Entity deleted successfully']);
    }

    #[Route('getCommentsByPostId', name:'getCommentsByPostId',methods: ['POST'])]
    public function getCommentsByPostId(EntityManagerInterface $entityManager, Request $request): JsonResponse
    {
        // Get post ID from request
        $postId = $request->request->get('id');

        // Fetch post entity by ID
        $post = $entityManager->getRepository(Post::class)->findOneBy(['id' => $postId]);

        // If post not found, return empty response or appropriate error handling
        if (!$post) {
            return new JsonResponse(['error' => 'Post not found'], JsonResponse::HTTP_NOT_FOUND);
        }

        // Fetch comments associated with the post
        $comments = $post->getComments();

        // Serialize comments to a basic array
        $commentsData = [];
        foreach ($comments as $comment) {
            $commentsData[] = [
                'id' => $comment->getId(),
                'content' => $comment->getContent(),
                'username' => $comment->getUser()->getUsername()
            ];
        }
        return $this->json($commentsData);
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
