<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Service\WeatherApi;

class HelloController extends AbstractController{

    #[Route('/')]
    public function index(WeatherApi $weatherApi){
        $weatherApi->GetWeatherInfosFromCity("Aix les bains");

        return  $this->render('home/index.html.twig');
    }
    
}