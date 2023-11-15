<?php

namespace App\Controller;

use App\Entity\Wish;
use App\Form\WishType;
use App\Repository\WishRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

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
    public function detail(int $id, WishRepository $wishRepository): Response
    {
        dump($id);
        //va chercher l'idée en bdd
        $wish = $wishRepository->findOneBy(["isPublished" => true, "id" => $id]);

        //par exemple, si le wish est supprimé ou n'est pas encore publié...
        if (!$wish){
            throw $this->createNotFoundException("This wish is not available! Sorry dude.");
        }

        return $this->render('wish/detail.html.twig', [
            "wish" => $wish
        ]);
    }

    #[Route('/create', name: 'wish_create', methods: ["GET", "POST"])]
    public function create(Request $request, EntityManagerInterface $em): Response
    {
        $wish = new Wish();
        $wishForm = $this->createForm(WishType::class, $wish);

        $wishForm->handleRequest($request);

        if ($wishForm->isSubmitted() && $wishForm->isValid()){
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
    public function edit(Wish $wish, Request $request, EntityManagerInterface $em): Response
    {
        $wishForm = $this->createForm(WishType::class, $wish);

        $wishForm->handleRequest($request);

        if ($wishForm->isSubmitted() && $wishForm->isValid()){
            $wish->setDateUpdated(new \DateTimeImmutable());

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
