<?php

namespace App\Controller;

use App\Entity\Search;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\SearchRepository;
use App\Form\SearchType;
use DateTime;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;

class SearchController extends AbstractController
{
    #[Route('/searchs', name: 'searchs')]
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
        $search = $repository->findOneBy(['id' => $id]);
        dump($search);

        if ($search === null) {
            throw $this->createNotFoundException('Historique de recherche non trouvé.');
        }



        //getSearch.html.twig à créer
        return $this->render('search/index.html.twig', [
            'search' => $search,
        ]);
    }


    #[Route('/search/add', name: 'create_search')]
    public function addSearch(Request $request, EntityManagerInterface $manager): Response
    {
        $search = new Search;

        $form = $this->createForm(SearchType::class, $search, ['attr' => ['class' => 'search-form']]);

        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $search->setCity(trim($search->getCity()));
            $search->setLatitude(trim($search->getLatitude()));
            $search->setLongitude(trim($search->getLongitude()));
            if (
                empty($search->getCity()) &&
                (empty($search->getLongitude()) || empty($search->getLatitude()))
            ) {
                $this->addFlash('error', 'Vous devez renseigner une ville OU une longitude et une latitude.');
            } else {
                $search->setSearchDate(new DateTime());
                $manager->persist($search);
                $manager->flush();
            }
        }

        return $this->render('search/addsearch.html.twig', [
            'form' => $form
        ]);
    }
}
