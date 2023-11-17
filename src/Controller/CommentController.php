<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Form\CommentType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/comment')]
#[IsGranted("ROLE_USER")]
class CommentController extends AbstractController
{
    #[Route('/{id}/edit', name: 'comment_edit', methods: ["GET", "POST"], requirements:["id" => "\d+"])]
    public function edit(Comment $comment, EntityManagerInterface $em, Request $request): Response
    {
        if ($this->getUser() !== $comment->getAuthor()){
            throw $this->createAccessDeniedException("you are not welcome here");
        }

        $commentForm = $this->createForm(CommentType::class, $comment);
        $commentForm->handleRequest($request);
        if ($commentForm->isSubmitted() && $commentForm->isValid()){

            $comment->setDateUpdated(new \DateTimeImmutable());

            $em->persist($comment);
            $em->flush();
            $this->addFlash("success", "Your comment has been edited! Thanks!");
            return $this->redirectToRoute("wish_detail", ["id" => $comment->getWish()->getId()]);
        }

        return $this->render('comment/edit.html.twig', [
            "commentForm" => $commentForm,
            "comment" => $comment,
        ]);
    }

    #[IsGranted("ROLE_USER")]
    #[Route("/{id}/delete/{token}", name: "comment_delete", methods: ["GET"], requirements:["id" => "\d+"])]
    public function delete(Comment $comment, string $token, EntityManagerInterface $em): Response
    {
        if (!$this->isCsrfTokenValid("comment-delete-".$comment->getId(), $token)){
            throw $this->createNotFoundException("invalid url");
        }

        if (!$this->isGranted("ROLE_ADMIN") && $this->getUser() !== $comment->getAuthor()){
            throw $this->createAccessDeniedException("You are not the author sorry");
        }

        $em->remove($comment);
        $em->flush();
        $this->addFlash("success", "Your comment has been deleted!");
        return $this->redirectToRoute("wish_detail", ["id" => $comment->getWish()->getId()]);
    }
}
