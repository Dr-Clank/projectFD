<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\SearchRepository;
use App\Form\SearchType;

class SearchController extends AbstractController
{
    #[Route('/searchs', name: 'app_search')]
    public function index(SearchRepository $repository): Response
    {
        $searchs = $repository->findAllByTimeOrder();
        dump($searchs);

        return $this->render('search/index.html.twig', [
            'searchHistory' => $searchs,
        ]);
    }


    #[Route('/search/{id<\d+>}', name: 'get_search')]
    public function getSearch(SearchRepository $repository, $id): Response
    {
        $search = $repository->findOneBy(['id'=> $id]);
        dump($search);

        if($search === null){
            throw $this->createNotFoundException('Historique de recherche non trouvé.');
        }

        //getSearch.html.twig à créer
        return $this->render('search/index.html.twig', [
            'search' => $search,
        ]);
    }

    #[Route('/search/add', name: 'create_search')]
    public function addSearch(SearchRepository $repository): Response
    {
        //$search = $repository->findOneBy(['id'=> $id]);
        // dump($search);

        $form = $this->createForm(SearchType::class);
        //New
        return $this->render('search/addsearch.html.twig', [
            'form' => $form
        ]);
    }

}
