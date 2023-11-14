<?php

namespace App\Controller;

use App\Repository\WishRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
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

    #[Route('/{id}', name: 'wish_detail')]
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
}
