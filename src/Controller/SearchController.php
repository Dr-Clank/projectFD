<?php

namespace App\Controller;

use App\Entity\Search;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\SearchRepository;
use App\Form\SearchType;
use App\Service\WeatherApi;
use DateTime;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;

class SearchController extends AbstractController
{
    #[Route('/searchs', name: 'searchs')]
    public function index(SearchRepository $repository): Response
    {
        $searchs = $repository->findAllByTimeOrder();
        //dd($searchs);
        return $this->render('search/index.html.twig', [
            'searchHistory' => $searchs,
        ]);
    }


    #[Route('/search/{id<\d+>}', name: 'get_search')]
    public function getSearch(SearchRepository $repository, $id, WeatherApi $weatherApi): Response
    {
        $search = $repository->findOneBy(['id' => $id]);
        
        $hourlyData = $weatherApi->getWeatherInfosFromCoordinate($search->getLatitude(), $search->getLongitude());
        $dailyAverages = $weatherApi->getDailyAverages($hourlyData);

        if ($search === null) {
            throw $this->createNotFoundException('Recherche non trouvé.');
        }
        
        return $this->render('search/getsearch.html.twig', [
            'search' => $search,
            'dailyAverages' => $dailyAverages
        ]);
    }


    #[Route('/search/add', name: 'create_search')]
    public function addSearch(Request $request, EntityManagerInterface $manager, WeatherApi $weatherApi): Response
    {
        $search = new Search;

        $form = $this->createForm(SearchType::class, $search, ['attr' => ['class' => 'search-form']]);

        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $search->setCity(trim($search->getCity()));
            $search->setLatitude(trim($search->getLatitude()));
            $search->setLongitude(trim($search->getLongitude()));

            //Vérifie si le formulaire contient bien une ville ou une Longitude + Latitude
            if (empty($search->getCity()) && (empty($search->getLongitude()) || empty($search->getLatitude()))) {
                $this->addFlash('error', 'Vous devez renseigner une ville OU une longitude et une latitude.');
            } else {
                $searchTemp = $weatherApi->getCityInfos($search->getCity());
                
                if($searchTemp['is_empty'] != true){
                    $search->setLatitude($searchTemp['latitude']);
                    $search->setLongitude($searchTemp['longitude']);
                }
                $search->setSearchDate(new DateTime());
                $manager->persist($search);
                $manager->flush();
                return $this->redirectToRoute('get_search', ['id'=> $search->getId()]);
            }
        }

        return $this->render('search/addsearch.html.twig', [
            'form' => $form
        ]);
    }
    
    #[Route('/search/{id<\d+>}/delete', name: 'delete_search')]
    public function deleteSearch($id, SearchRepository $repository, EntityManagerInterface $manager, Request $request ): Response
    {
        $search = $repository->findOneBy(['id' => $id]);

        if ($search === null) {
            throw $this->createNotFoundException('Recherche non trouvé.');
        }

        if($request->isMethod('POST')){
            $manager->remove($search);
            $manager->flush();

            $this->addFlash('notice','La recheche à correctement été supprimé.');

            return $this->redirectToRoute('searchs');
        }
        
        return $this->render('search/deletesearch.html.twig', [
            'search' => $search
        ]);
    }

}
