<?php

namespace App\Controller;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/home')]
class HomeController extends AbstractController
{

    #[Route('/homevide', name: 'homevide')]
    public function home(): Response
    {
        return $this->render('home/PageVideHome.html.twig');
    }

    #[Route('/home', name: 'homepage')]
    public function index(): Response
    {
        return $this->render('home/index.html.twig');
    }




}
