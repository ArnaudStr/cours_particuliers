<?php

namespace App\Controller\Prof;

use App\Entity\Prof;


use App\Entity\PrixActivite;
use App\Entity\CreneauCours;

use App\Form\CreationCoursType;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * @Route("/prof")
 */
class ProfController extends AbstractController
{

    /**
     * @Route("/addProposeCours/{id}", name="propose_cours")
     */
    public function addEditCoursProf(Prof $prof, PrixActivite $prixActivite = null, CreneauCours $creneauCours = null, ObjectManager $manager, Request $request) {
       
        $prixActivite = new PrixActivite();
        $creneauCours = new CreneauCours();
 
        $form = $this->createForm(CreationCoursType::class);
        
        $form->handleRequest($request);
               
        if($form->isSubmitted() && $form->isValid()) {

            dump($prof);
            $prixActivite->setProf(
                $prof
            );
            $prixActivite->setActivite(
                $form->get('activite')->getData()
            );
            $prixActivite->setPrix(
                $form->get('tarifHoraire')->getData()
            );

            $creneauCours->setProf(
                $prof
            );
            $creneauCours->setActivite(
                $form->get('activite')->getData()
            );
            $creneauCours->setDateDebut(
                $form->get('dateDebut')->getData()
            );
            $creneauCours->setDateFin(
                $form->get('dateFin')->getData()
            );

            $manager->persist($prixActivite);
            $manager->persist($creneauCours);
            $manager->flush();
 
            return $this->redirectToRoute('home_prof');
            // return $this->redirectToRoute('showInfosessionCours', ['id' => $sessionCours->getId()]);
        }
        return $this->render('course/addEditCreationCours.html.twig', ['form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/calendar", name="cours_calendar")
     */
    public function calendar() {
        return $this->render('course/calendar.html.twig', [
            'title' => 'Planning'
        ]);
    }
}
