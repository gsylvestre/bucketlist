<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/wish')]
class WishController extends AbstractController
{
    #[Route('/', name: 'wish_list')]
    public function list(): Response
    {
        //todo: va chercher les idées en bdd

        return $this->render('wish/list.html.twig', [

        ]);
    }

    #[Route('/{id}', name: 'wish_detail')]
    public function detail(int $id): Response
    {
        dump($id);
        //todo: va chercher l'idée en bdd

        return $this->render('wish/detail.html.twig', [

        ]);
    }
}
