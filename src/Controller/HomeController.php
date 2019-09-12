<?php

namespace App\Controller;

use App\Entity\Eleve;
use App\Entity\Message;
use Doctrine\Common\Persistence\ObjectManager;
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

    // /**
    //  * @Route("/eleve/home/{id}", name="home_eleve")
    //  */
    // public function indexEleve(Eleve $eleve)
    // {
    //     $nbMessagesNonLus = $this->getDoctrine()
    //     ->getRepository(Message::class)
    //     ->findNonLusEleve($eleve);

    //     return $this->render('eleve/indexEleve.html.twig', [
    //         'nonLus' => $nbMessagesNonLus[1],
    //     ]);
    // }


    /**
     * @Route("/eleve/", name="home_eleve")
     */
    public function indexEleve()
    {
        return $this->render('eleve/indexEleve.html.twig', [
        ]);
    }

    /**
     * @Route("/prof/", name="home_prof")
     */
    public function indexProf()
    {
        return $this->render('prof/indexProf.html.twig', [
        ]);
    }
}
