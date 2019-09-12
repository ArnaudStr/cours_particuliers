<?php

namespace App\Controller\Prof;

use DateTime;


use DateTimeZone;
use App\Entity\Prof;

use App\Entity\Eleve;
use App\Entity\Message;
use App\Entity\Session;
use App\Form\MessageType;
use App\Form\EditProfType;
use App\Entity\CreneauCours;
use App\Form\CreationCoursType;
use Symfony\Component\Filesystem\Filesystem;

use Symfony\Component\HttpFoundation\Request;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;


/**
 * @Route("/prof")
 */
class ProfController extends AbstractController
{

    /**
     * @Route("/showProfile", name="show_profile_prof")
     */
    public function showProfileProf()
    {
        return $this->render('prof/showProfileProf.html.twig', [
            'controller_name' => 'MemberController',
        ]);
    }


    public function delFile($dir, $del_file){
        $fsObject = new Filesystem();
        $current_dir_path = getcwd();
            $delTarget = $current_dir_path . "/assets/". $dir ."/". $del_file;
        
            if($del_file){
               return $fsObject->remove($delTarget);
            }
    }


    /**
     * @Route("/editProf/{id}", name="edit_prof")
     */
    // public function editProf(Prof $prof, Request $request, UserPasswordEncoderInterface $passwordEncoder): Response
    public function editProf(Prof $prof, Request $request)
    {       

        $pictureBeforeForm = $prof->getPictureFilename();
        
        $form = $this->createForm(EditProfType::class, $prof);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            // Upload de la photo et inscription en BDD du nom de l'image
            if ( $pictureFilename = $form->get("pictureFilename")->getData() ){
                $this->delFile('pictures',$pictureBeforeForm);
                $filename = md5(uniqid()).'.'.$pictureFilename->guessExtension();
                $pictureFilename->move($this->getParameter('pictures_directory'), $filename);
                $prof->setPictureFilename($filename);
            }
            else
            {
                $prof->setPictureFilename($pictureBeforeForm);
            }

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($prof);
            $entityManager->flush();

            // dump($pictureFilename);
            // dump($filename);
            // dd($prof);

            // do anything else you need here, like send an email

            return $this->redirectToRoute('show_profile_prof');
        }

        return $this->render('prof/editProfileProf.html.twig', [
            'editForm' => $form->createView(),
            'prof' => $prof
        ]);
    }

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
     * @Route("/conversationProf/{idProf}/{idEleve}", name="conversation_prof")
     * @ParamConverter("prof", options={"id" = "idProf"})
     * @ParamConverter("eleve", options={"id" = "idEleve"})
     */
    public function conversationProf(Prof $prof, Eleve $eleve) {

        $msgLus = [];
        $msgNonLus = [];
        $entityManager = $this->getDoctrine()->getManager();

        foreach($prof->getMessages() as $message){
            if ( $message->getAuteur() != $prof->getUsername() ){
                if ($message->getLu()){
                    array_push($msgLus, $message);
                }
                else {
                    array_push($msgNonLus, $message);
                    $message->setLu(true);
                    $entityManager->persist($message);
                }
            }
        }

        $entityManager->flush();

        return $this->render('prof/conversationProf.html.twig', [
            'prof' => $prof,
            'eleve' => $eleve,
            'msgLus' => $msgLus,
            'msgNonLus' => $msgNonLus,
        ]);
    }
}
