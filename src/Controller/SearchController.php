<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\SearchRepository;

final class SearchController extends AbstractController
{
    #[Route('/search', name: 'app_search')]
    public function index(SearchRepository $repository): Response
    {
        $searchs = $repository->findAllByTimeOrder();
        dump($searchs);

        return $this->render('search/index.html.twig', [
            'searchHistory' => $searchs,
        ]);
    }
}
