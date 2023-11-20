<?php

namespace App\Controller;

use App\Form\EventSearchType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class EventController extends AbstractController
{
    #[Route('/event', name: 'event_search')]
    public function search(
        HttpClientInterface $httpClient,
        SerializerInterface $serializer,
        Request $request,
    ): Response
    {
        $searchForm = $this->createForm(EventSearchType::class);
        $searchForm->handleRequest($request);
        $url = "https://public.opendatasoft.com/api/explore/v2.1/catalog/datasets/evenements-publics-openagenda/records?limit=20";

        if ($searchForm->isSubmitted() && $searchForm->isValid()){
            $searchData = $searchForm->getData();

            $url .= "&refine=firstdate_begin:" . $searchData['date']->format("Y-m-d");
            $url .= "&refine=location_city:" . ucfirst($searchData['city']);
        }

        $response = $httpClient->request("GET", $url);
        $content = $response->getContent();
        $results = $serializer->decode($content, 'json');

        return $this->render('event/search.html.twig', [
            "results" => $results,
            "searchForm" => $searchForm,
        ]);
    }
}
