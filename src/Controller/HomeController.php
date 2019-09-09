<?php

namespace App\Controller;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;


class HomeController extends AbstractController
{

    /**
     * @Route("/", name="home")
     */
    public function index()
    {
        return $this->render('home/index.html.twig', [
            'controller_name' => 'HomeController',
        ]);
    }

    /**
     * @Route("/eleve/", name="home_eleve")
     */
    public function indexEleve()
    {
        return $this->render('eleve/indexEleve.html.twig', [
            'controller_name' => 'HomeController',
        ]);
    }

    /**
     * @Route("/prof/", name="home_prof")
     */
    public function indexProf()
    {
        return $this->render('prof/indexProf.html.twig', [
            'controller_name' => 'HomeController',
        ]);
    }
}
