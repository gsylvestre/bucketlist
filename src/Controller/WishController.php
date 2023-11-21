<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Entity\Wish;
use App\Form\CommentType;
use App\Form\WishType;
use App\Repository\WishRepository;
use App\Util\Censurator;
use App\Util\Uploader;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\String\Slugger\SluggerInterface;

#[Route('/wish')]
class WishController extends AbstractController
{
    #[Route('/', name: 'wish_list')]
    public function list(WishRepository $wishRepository): Response
    {
        //va chercher les idées en bdd
        $wishes = $wishRepository->findBy(["isPublished" => true], ["dateCreated" => "DESC"]);

        return $this->render('wish/list.html.twig', [
            "wishes" => $wishes
        ]);
    }

    #[Route('/{id}', name: 'wish_detail', requirements: ['id' => '\d+'])]
    public function detail(int $id, WishRepository $wishRepository, Request $request, EntityManagerInterface $em): Response
    {
        //va chercher l'idée en bdd
        $wish = $wishRepository->findOneBy(["isPublished" => true, "id" => $id]);

        //par exemple, si le wish est supprimé ou n'est pas encore publié...
        if (!$wish){
            throw $this->createNotFoundException("This wish is not available! Sorry dude.");
        }

        $comment = new Comment();
        $commentForm = $this->createForm(CommentType::class, $comment);

        $commentForm->handleRequest($request);
        if ($commentForm->isSubmitted() && $commentForm->isValid() && $this->getUser()){

            $comment->setWish($wish);
            $comment->setAuthor($this->getUser());

            $em->persist($comment);
            $em->flush();
            $this->addFlash("success", "Your comment has been published! Thanks!");
            return $this->redirectToRoute("wish_detail", ["id" => $wish->getId()]);
        }

        return $this->render('wish/detail.html.twig', [
            "wish" => $wish,
            "commentForm" => $commentForm,
        ]);
    }

    #[Route('/create', name: 'wish_create', methods: ["GET", "POST"])]
    public function create(
        Request $request,
        EntityManagerInterface $em,
        Uploader $uploader,
        Censurator $censurator,
    ): Response
    {
        $wish = new Wish();
        $wishForm = $this->createForm(WishType::class, $wish);

        $wishForm->handleRequest($request);

        if ($wishForm->isSubmitted() && $wishForm->isValid()){

            /** @var UploadedFile $uploadedImage */
            $uploadedImage = $wishForm->get('file')->getData();
            if ($uploadedImage) {
                $newFilename = $uploader->upload($uploadedImage);
                $wish->setFilename($newFilename);
            }

            //on donne en créateur l'utilisateur actuellement connecté
            $wish->setCreator($this->getUser());

            //censure les textes
            $wish->setDescription($censurator->purify($wish->getDescription()));

            $em->persist($wish);
            $em->flush();

            $this->addFlash("success", "Your wish has been granted!");
            return $this->redirectToRoute("wish_detail", ["id" => $wish->getId()]);
        }

        return $this->render('wish/create.html.twig', [
            "wishForm" => $wishForm,
        ]);
    }

    #[Route('/{id}/edit', name: 'wish_edit', methods: ["GET", "POST"])]
    public function edit(
        Wish $wish,
        Request $request,
        EntityManagerInterface $em,
        Censurator $censurator,
        Uploader $uploader,
    ): Response
    {
        if (!$this->getUser()){
            throw $this->createAccessDeniedException("nope");
        }

        if ($this->getUser() !== $wish->getCreator()){
            throw $this->createAccessDeniedException("you must be the creator to edit this wish.");
        }

        $wishForm = $this->createForm(WishType::class, $wish);

        $wishForm->handleRequest($request);

        if ($wishForm->isSubmitted() && $wishForm->isValid()){
            $wish->setDateUpdated(new \DateTimeImmutable());

            //si le checkbox de suppression de l'image est coché, on supprime le fichier
            if ($wishForm->has('deleteCb')){
                unlink($this->getParameter('upload_directory') . '/' . $wish->getFilename());
                $wish->setFilename(null);
            }

            /** @var UploadedFile $uploadedImage */
            $uploadedImage = $wishForm->get('file')->getData();
            $newFilename = $uploader->upload($uploadedImage);
            $wish->setFilename($newFilename);

            //censure les textes
            $wish->setDescription($censurator->purify($wish->getDescription()));

            $em->persist($wish);
            $em->flush();

            $this->addFlash("success", "The wish has been updated!");
            return $this->redirectToRoute("wish_detail", ["id" => $wish->getId()]);
        }

        return $this->render('wish/edit.html.twig', [
            "wish" => $wish,
            "wishForm" => $wishForm,
        ]);
    }

    #[Route("/{id}/delete/{token}", name: "wish_delete", requirements: ["id" => "\d+"], methods: ["GET"])]
    public function delete(Wish $wish, string $token, EntityManagerInterface $em): Response
    {
        if ($this->getUser() !== $wish->getCreator() && !$this->isGranted("ROLE_ADMIN")){
            throw $this->createAccessDeniedException("you must be the creator to edit this wish or be an admin.");
        }

        //voir dans le twig de la page détail pour la création du token
        //ici on vérifie la validité manuellement du token csrf
        $tokenIsValid = $this->isCsrfTokenValid("delete-token-" . $wish->getId(), $token);

        //s'il est valide...
        if($tokenIsValid){
            $em->remove($wish);
            $em->flush();

            $this->addFlash("success", "The wish has vanished.");
            return $this->redirectToRoute("wish_list");
        }

        //sinon...
        $this->addFlash("danger", "The wish has NOT been deleted, sorry");
        return $this->redirectToRoute("wish_detail", ["id" => $wish->getId()]);
    }
}
