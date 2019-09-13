<?php

namespace App\Controller\Eleve;


use App\Entity\Prof;

use App\Entity\Eleve;
use App\Entity\Message;

use App\Form\MessageType;
use App\Form\EditEleveType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;


/**
 * @Route("/eleve")
 */
class EleveController extends AbstractController
{
    // /**
    //  * @Route("/calendar", name="cours_calendar")
    //  */
    // public function calendar() {
    //     return $this->render('course/calendar.html.twig', [
    //         'title' => 'Planning'
    //     ]);
    // }

    /**
     * @Route("/loginEleve", name="login_eleve")
     */
    public function loginEleve(AuthenticationUtils $authenticationUtils)
    {
        // if ($this->getUser()) {
        //    $this->redirectToRoute('target_path');
        // }

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/loginEleve.html.twig', ['last_username' => $lastUsername, 'error' => $error]);
    }


    /**
     * @Route("/logoutEleve", name="logout_eleve")
     */
    public function logoutEleve() {
        
        return $this->redirectToRoute("home");
        // return $this->redirectToRoute("login_prof");
        // throw new \Exception('This method can be blank - it will be intercepted by the logout key on your firewall');
    }

    /**
     * @Route("/", name="home_eleve")
     */
    public function indexEleve()
    {
        return $this->render('eleve/indexEleve.html.twig', [
        ]);
    }

    /**
     * @Route("/showProfileEleve", name="show_profile_eleve")
     */
    public function showProfileEleve()
    {
        return $this->render('member/showProfile.html.twig', [
            'controller_name' => 'MemberController',
        ]);
    }   

    /**
     * @Route("/editEleve/{id}", name="edit_eleve")
     */
    // public function editEleve(Eleve $eleve, Request $request, UserPasswordEncoderInterface $passwordEncoder): Response
    public function editEleve(Eleve $eleve, Request $request)
    {       

        // On récupere l'image avant le passage par le formulaire
        $pictureBeforeForm = $eleve->getPictureFilename();

        $form = $this->createForm(EditEleveType::class, $eleve);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            // Upload de la photo et inscription en BDD du nom de l'image
            if ( $pictureFilename = $form->get("pictureFilename")->getData() ) {
                $filename = md5(uniqid()).'.'.$pictureFilename->guessExtension();
                $pictureFilename->move($this->getParameter('pictures_directory'), $filename);
                $eleve->setPictureFilename($filename);
            }
            else
            {
                $eleve->setPictureFilename($pictureBeforeForm);
            }

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($eleve);
            $entityManager->flush();

            // dump($pictureFilename);
            // dump($filename);
            // dd($eleve);

            // do anything else you need here, like send an email

            return $this->redirectToRoute('show_profile_eleve');
        }

        return $this->render('eleve/editProfileEleve.html.twig', [
            'editForm' => $form->createView(),
            // 'picture' => $pictureBeforeForm
        ]);
    }



    /**
     * @Route("/sendMessageEleve/{idProf}/{idEleve}", name="send_message_eleve")
     * @ParamConverter("prof", options={"id" = "idProf"})
     * @ParamConverter("eleve", options={"id" = "idEleve"})
     */
    public function sendMessageEleve(Prof $prof, Eleve $eleve, Request $request)
    {

        $form = $this->createForm(MessageType::class);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $message = new Message();
            $message->setProf($prof);
            $message->setEleve($eleve);
            $message->setAuteur($eleve->getUsername());
            $message->setContenu($form->get("contenu")->getData());

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($message);
            $entityManager->flush();

            // do anything else you need here, like send an email

            return $this->redirectToRoute('home_eleve');
        }

        return $this->render('message/sendMessage.html.twig', [
            'messageForm' => $form->createView(),
        ]);
    }

    /**
     * @Route("/showMessagesEleve", name="show_messages_eleve")
     */
    public function showMessagesEleve() {

        return $this->render('eleve/showMessageEleve.html.twig', [
            'title' => 'Planning'
        ]);
    }

    
    /**
     * @Route("/conversationEleve/{idProf}/{idEleve}", name="conversation_eleve")
     * @ParamConverter("prof", options={"id" = "idProf"})
     * @ParamConverter("eleve", options={"id" = "idEleve"})
     */
    public function conversationEleve(Prof $prof, Eleve $eleve) {

        $msgLus = [];
        $msgNonLus = [];
        $entityManager = $this->getDoctrine()->getManager();

        foreach($eleve->getMessages() as $message){
            if ( $message->getAuteur() != $eleve->getUsername() ){
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

        return $this->render('eleve/conversationEleve.html.twig', [
            'prof' => $prof,
            'eleve' => $eleve,
            'msgLus' => $msgLus,
            'msgNonLus' => $msgNonLus,
        ]);
    }
}
