<?php

namespace App\Controller;

use App\Entity\Post;
use App\Entity\User;
use App\Entity\React;
use App\Entity\Comment;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Serializer\SerializerInterface;
class PostDTO
{
    private $post;
    private $isLiked;

    public function __construct($post, $isLiked)
    {
        $this->post = $post;
        $this->isLiked = $isLiked;
    }

    public function getPost()
    {
        return $this->post;
    }

    public function getIsLiked()
    {
        return $this->isLiked;
    }
}

#[Route('/homepage')]
class HomePageController extends AbstractController
{
    #[Route('/getAllPosts', name: 'getAllPosts',methods: ['POST'])]
    public function getAllPosts(EntityManagerInterface $entityManager,Request $request): JsonResponse
    {
        $user = $entityManager->getRepository(User::class)->find($request->get('User_ID'));
        if(!$request->get('UserPosts') ){
            $posts = $entityManager->getRepository(Post::class)->findAll();
        }else if($request->get('profileUser_ID')){
            $profileUser = $entityManager->getRepository(User::class)->find($request->get('profileUser_ID'));
            $posts = $entityManager->getRepository(Post::class)->findBy(['User'=>$profileUser]);
        }else{
            $posts = $entityManager->getRepository(Post::class)->findBy(['User'=>$user]);
        }


        $posts = array_reverse($posts);
        $postsWithIsLiked = [];
        if (!$posts) {
            throw $this->createNotFoundException(
                'No posts found'
            );
        }

        foreach ($posts as $post) {
            // Assuming you have a method to check if the user has reacted to the post
            $react = $entityManager->getRepository(React::class)->findOneBy([
                'User'=>$user,
                'Post'=>$post
            ]);
            $isLiked = $react?true:false;
            $postDTO = new PostDTO($post, $isLiked);
            $postsWithIsLiked[] = $postDTO;
        }



        return $this->json($postsWithIsLiked);

    }
    #[Route('/addPost', name: 'addPost',methods: ['POST'])]
    public function addPost(EntityManagerInterface $entityManager, Request $request): JsonResponse
    {
        $post = new Post();

        $user = $entityManager->getRepository(User::class)->find($request->get('user_id'));
        if(!$request->get('Post_ID')){
            $post->setCaption($request->get('Content'))
                ->setReactCount(0)
                ->setUser($user);
            if(!$request->get('Media')){
                $post->setMedia($request->get('Media'));
            }
        }else{
            $oldPost = $entityManager->getRepository(Post::class)->find($request->get('Post_ID'));
            $post->setCaption($oldPost->getCaption())
                ->setReactCount(0)
                ->setUser($user)
                ->setMedia($oldPost->getMedia());

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
    #[Route('/addReact', name: 'addReact',methods: ['POST'])]
    public function addReact(EntityManagerInterface $entityManager, Request $request): JsonResponse
    {
        $post = $entityManager->getRepository(Post::class)->find($request->get('Post_ID'));;

        $user = $entityManager->getRepository(User::class)->find($request->get('User_ID'));
        $react = $entityManager->getRepository(React::class)->findOneBy([
            'User'=>$user,
            'Post'=>$post
        ]);
        if(!$react){
            $react = new React();

            $react->setUser($user)
                ->setPost($post);

            $entityManager->persist($react);

            $post->setReactCount($post->getReactCount()+1);

            $entityManager->persist($post);
        }else{
            $entityManager->remove($react);
            $post->setReactCount($post->getReactCount()-1);

            $entityManager->persist($post);
        }


        $entityManager->flush();


        return $this->json([
            'success' => true,
            'message' => 'React added successfully',
            'react' => [
                'id' => $react->getId(),
            ]
        ]);
    }
    #[Route('/addComment', name: 'addComment',methods: ['POST'])]
    public function addComment(EntityManagerInterface $entityManager, Request $request): JsonResponse
    {
        $comment = new Comment();

        $user = $entityManager->getRepository(User::class)->find($request->get('User_ID'));
        $post = $entityManager->getRepository(Post::class)->find($request->get('Post_ID'));
        $comment->setPost($post)
                ->setUser($user)
                ->setContent($request->get('Content'));



        $entityManager->persist($comment);
        $entityManager->flush();


        return $this->json([
            'success' => true,
            'message' => 'Comment added successfully',
            'comment' => [
                'id' => $comment->getId(),
                'caption' => $comment->getCaption(),

            ]
        ]);
    }
    #[Route('/getComments', name: 'getComments',methods: ['POST'])]
    public function getComments(EntityManagerInterface $entityManager,Request $request): JsonResponse
    {
        $post = $entityManager->getRepository(Post::class)->find($request->get('Post_ID'));
        $comments = $entityManager->getRepository(Comment::class)->findBy(['Post'=>$post]);
        $comments = array_reverse($comments);

        return $this->json($comments);

    }
    #[Route('/getUser', name: 'getUser',methods: ['POST'])]
    public function getUserInfo(EntityManagerInterface $entityManager,Request $request): JsonResponse
    {
        $user = $entityManager->getRepository(User::class)->find($request->get('User_ID'));

        return $this->json($user);

    }
}
