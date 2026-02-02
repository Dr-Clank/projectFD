<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HelloController {

    #[Route('/')]
    public function index(){
        return new Response("test lamastico");
    }
    
}