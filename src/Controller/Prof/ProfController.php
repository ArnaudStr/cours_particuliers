<?php

namespace App\Controller\Prof;

use DateTime;


use App\Entity\Prof;
use App\Entity\Session;

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
    public function addEditCoursProf(Prof $prof, ObjectManager $manager, Request $request) {
       
        $creneauCours = new CreneauCours();
        $creneauCours->setProf($prof);
 
        $form = $this->createForm(CreationCoursType::class, $creneauCours);
        
        $form->handleRequest($request);
               
        if($form->isSubmitted() && $form->isValid()) {

            $manager->persist($creneauCours);
            foreach ($creneauCours->getCreneaux() as $creneau){

                // On prévoit les créneaux pour le prochain mois
                for ($i=0; $i<4; $i++){

                    $session = new Session();
                    $session->setProf($prof);
                    $session->setActivite($creneauCours->getActivite());

                    $dateDebut = new DateTime();
                    $dateDebut->modify('next '.$creneau->getJour().' +'.($i*7).' days');
                    $dateDebut->setTime($creneau->getHeureDebut()->format('H'), $creneau->getHeureDebut()->format('i'));
                    $session->setDateDebut($dateDebut);

                    $dateFin = new DateTime();
                    $dateFin->modify('next '.$creneau->getJour().' +'.($i*7).' days');

                    $dateFin->setTime($creneau->getHeureFin()->format('H'), $creneau->getHeureFin()->format('i'));
                    $session->setDateFin($dateFin);

                    $manager->persist($session);


                }
            }

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

    /**
     * @Route("/show_course/{id}", name="showCourse")
     */
    public function inscriptionSession() {
        return $this->render('course/showCourse.html.twig', [
            'title' => 'Planning'
        ]);
    }
}
