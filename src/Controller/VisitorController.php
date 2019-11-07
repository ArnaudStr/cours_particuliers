<?php

namespace App\Controller;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class VisitorController extends AbstractController
{

    // /**
    //  * @Route("/", name="home")
    //  */
    // public function index()
    // {
    //     return $this->render('visitor/index.html.twig', [
    //         'controller_name' => 'HomeController'
    //     ]);
    // }

    /**
     * @Route("/", name="search_course")
     */
    public function searchCourse()
    {
        
        return $this->render('course/searchCourse.html.twig', [
            'title' => 'Cours à Strasbourg',
            'transparent' => true
        ]);
    }

    // /**
    //  * @Route("/eleve/home/{id}", name="home_eleve")
    //  */
    // public function indexEleve(Eleve $eleve)
    // {
        // $nbMessagesNonLus = $this->getDoctrine()
        // ->getRepository(Message::class)
        // ->findNonLusEleve($eleve);

    //     return $this->render('eleve/indexEleve.html.twig', [
    //         'nonLus' => $nbMessagesNonLus[1],
    //     ]);
    // }

}
