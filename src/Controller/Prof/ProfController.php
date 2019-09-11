<?php

namespace App\Controller\Prof;

use DateTime;


use DateTimeZone;
use App\Entity\Prof;

use App\Entity\Eleve;
use App\Entity\Message;
use App\Entity\Session;
use App\Form\MessageType;
use App\Entity\CreneauCours;
use App\Form\CreationCoursType;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\Common\Persistence\ObjectManager;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;


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

                    $dateDebut = new DateTime('now',new DateTimeZone('Europe/Paris'));
                    $dateDebut->modify('next '.$creneau->getJour().' +'.($i*7).' days');
                    $dateDebut->setTime($creneau->getHeureDebut()->format('H'), $creneau->getHeureDebut()->format('i'));
                    $session->setDateDebut($dateDebut);

                    $dateFin = new DateTime('now',new DateTimeZone('Europe/Paris'));
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

        /**
     * @Route("/sendMessageProf/{idProf}/{idEleve}", name="send_message_prof")
     * @ParamConverter("prof", options={"id" = "idProf"})
     * @ParamConverter("eleve", options={"id" = "idEleve"})
     */
    public function sendMessageProf(Prof $prof, Eleve $eleve, Request $request)
    {

        $form = $this->createForm(MessageType::class);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $message = new Message();
            $message->setProf($prof);
            $message->setEleve($eleve);
            $message->setAuteur($prof->getUsername());
            $message->setContenu($form->get("contenu")->getData());

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($message);
            $entityManager->flush();

            // do anything else you need here, like send an email

            return $this->redirectToRoute('home_prof');
        }

        return $this->render('message/sendMessage.html.twig', [
            'messageForm' => $form->createView(),
        ]);
    }


    /**
     * @Route("/showMessagesProf", name="show_messages_prof")
     */
    public function showMessagesProf() {

        return $this->render('prof/showMessageProf.html.twig', [
            'title' => 'Planning'
        ]);
    }

    /**
     * @Route("/conversationProf/{idEleve}", name="conversation_prof")
     * @ParamConverter("eleve", options={"id" = "idEleve"})
     */
    public function conversationProf(Eleve $eleve) {

        return $this->render('prof/conversationProf.html.twig', [
            'eleve' => $eleve,
        ]);
    }
}
