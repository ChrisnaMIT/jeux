<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Entity\Post;
use App\Form\CommentType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class CommentController extends AbstractController
{
    #[Route('/comment/{id}', name: 'app_comment',priority: -1)]
    public function index(Request $request, EntityManagerInterface $manager, Post $post): Response
    {
        if (!$this->getUser() || !$post)
        {
            return $this->redirectToRoute('app_login');
        }

        $comment =new Comment();
        $form = $this->createForm(CommentType::class, $comment);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()){
            $comment->setAuthor($this->getUser());
            $comment->setPost($post);
            $manager->persist($comment);
            $manager->flush();
        }
        return $this->redirecToRoute('app_post_show', ['id' => $post->getId()]);
    }

    #[Route('/comment/{id}', name: 'app_comment_delete')]
    public function delete(Comment $comment, EntityManagerInterface $manager): Response
    {
        if (!$this->getUser() || !$comment){
            return $this->redirectToRoute('app_login');
        }
        $post = $comment->getPost();
        if ($comment->getAuthor() == $this->getUser()){
            $manager->remove($comment);
            $manager->flush();
        }

        return $this->redirectToRoute('app_post_show', ['id' => $post->getId()]);
    }

    #[Route('/comment/{id}/create', name: 'app_comment_create')]
    public function create(Request $request, EntityManagerInterface $manager, Post $post): Response
    {
        $comment = new Comment();
        $form = $this->createForm(CommentType::class, $comment);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()){
            $manager->persist($comment);
            $manager->flush();
            return $this->redirectToRoute('app_post_show', ['id' => $post->getId()]);
        }
        return $this->render('post/show.html.twig', [
            $form->createView(),
            'post' => $post,
            'comment' => $comment,
            'comments' => $post->getComments()
        ]);
    }


}
