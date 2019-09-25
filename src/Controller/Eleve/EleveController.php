<?php

namespace App\Controller\Eleve;


use App\Entity\Avis;

use App\Entity\Prof;
use App\Entity\Cours;
use App\Entity\DemandeCours;
use App\Entity\Eleve;
use App\Form\AvisType;
use App\Entity\Message;
use App\Entity\Session;
use App\Form\MessageType;
use App\Form\EditEleveType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Csrf\TokenGenerator\TokenGeneratorInterface;
use Symfony\Component\HttpFoundation\Session\Session as SessionUser;



/**
 * @Route("/eleve")
 */
class EleveController extends AbstractController
{
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
        $nbMsgNonLus = 0;

        foreach($this->getUser()->getMessages() as $message){
            if ( $message->getAuteur() != $this->getUser()->getUsername() ){
                if (!$message->getLu()){
                    $nbMsgNonLus++;
                }
            }
        }

        $session = new SessionUser();
        $session->set('nbMsgNonLus', $nbMsgNonLus);

        return $this->render('eleve/indexEleve.html.twig', [
        ]);
    }

    /**
     * @Route("/showProfileEleve", name="show_profile_eleve")
     */
    public function showProfileEleve()
    {
        return $this->render('eleve/showProfileEleve.html.twig', [
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

    /**
     * @Route("/inscriptionCoursEleve/{idProf}/{idCours}", name="inscription_cours_eleve")
     * @ParamConverter("prof", options={"id" = "idProf"})
     * @ParamConverter("cours", options={"id" = "idCours"})
     */
    public function inscriptionCoursEleve(Prof $prof, Cours $cours) {

        return $this->render('course/inscriptionCours.html.twig', [
            'prof' => $prof,
            'cours' => $cours,
        ]);
    }
    
    /**
     * @Route("/demandeInscriptionSession/{idSession}/{idEleve}/{idCours}", name="demande_inscription_session")
     * @ParamConverter("session", options={"id" = "idSession"})
     * @ParamConverter("eleve", options={"id" = "idEleve"})
     * @ParamConverter("cours", options={"id" = "idCours"})
     */
    public function demandeInscriptionSession(Session $session, Eleve $eleve, Cours $cours) {

        // Inscription élève au cours
        // $session->setEleve($eleve);
        // $session->setCours($cours);
        $demandeCours = new DemandeCours();
        $demandeCours->setSession($session);
        $demandeCours->setEleve($eleve);
        $demandeCours->setCours($cours);

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($demandeCours);
        $entityManager->flush();
            return $this->render('eleve/indexEleve.html.twig', [
                'title' => 'Planning'
        ]);
    }

    /**
     * @Route("/calendarEleve", name="calendar_eleve")
     */
    public function calendarEleve() {
        return $this->render('eleve/calendrierEleve.html.twig', [
            'title' => 'Planning'
        ]);
    }

    /**
     * @Route("/emettreAvis/{idEleve}/{idProf}", name="emettre_avis")
     * @ParamConverter("eleve", options={"id" = "idEleve"})
     * @ParamConverter("prof", options={"id" = "idProf"})
     */
    public function emettreAvis(Eleve $eleve, Prof $prof, Request $request) {

        $avis = new Avis();

        // dd($avis);

        $form = $this->createForm(AvisType::class, $avis);

        $form->handleRequest($request);

  
        if ($form->isSubmitted() && $form->isValid()) {

            $avis->setProf($prof);
            $avis->setEleve($eleve);

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($avis);
            $prof->addNote($form->get('note')->getData());
            $entityManager->persist($prof);

            $entityManager->flush();

            // do anything else you need here, like send an email

            return $this->redirectToRoute('home_eleve');
        }

        return $this->render('eleve/emettreAvis.html.twig', [
            'title' => 'Avis',
            'form' => $form->createView(),
        ]);
    }


    /**
     * @Route("/forgotten_password", name="app_forgotten_password")
     */
    public function forgottenPassword(
        Request $request,
        \Swift_Mailer $mailer,
        TokenGeneratorInterface $tokenGenerator
    )
    {
 
        if ($request->isMethod('POST')) {
 
            $email = $request->request->get('email');
 
            $entityManager = $this->getDoctrine()->getManager();
            $user = $entityManager->getRepository(Eleve::class)->findOneByEmail($email);
            /* @var $user User */
 
            if ($user === null) {
                $this->addFlash('danger', 'Email Inconnu');
                return $this->redirectToRoute('home');
            }
            $token = $tokenGenerator->generateToken();
 
            try{
                $user->setToken($token);
                $entityManager->flush();
            } catch (\Exception $e) {
                $this->addFlash('warning', $e->getMessage());
                return $this->redirectToRoute('home');
            }
 
            $url = $this->generateUrl('app_reset_password', array('token' => $token), UrlGeneratorInterface::ABSOLUTE_URL);

            $message = (new \Swift_Message('Forgot Password'))
                ->setFrom('arnaud6757@gmail.com')
                // ->setFrom('arnaud.straumann@free.fr')
                ->setTo($user->getEmail())
                ->setBody(
                    "blablabla voici le token pour reseter votre mot de passe : " . $url,
                    'text/html'
                );
 
            $mailer->send($message);

            $this->addFlash('notice', 'Mail envoyé');

            return $this->redirectToRoute('home');
        }
 
        return $this->render('security/forgotten_password.html.twig');
    }

    /**
     * @Route("/reset_password/{token}", name="app_reset_password")
     */
    public function resetPassword(Request $request, string $token, UserPasswordEncoderInterface $passwordEncoder)
    {
 
        if ($request->isMethod('POST')) {
            $entityManager = $this->getDoctrine()->getManager();
 
            $user = $entityManager->getRepository(Eleve::class)->findOneByToken($token);
            /* @var $user User */
 
            if ($user === null) {
                $this->addFlash('danger', 'Token Inconnu');
                return $this->redirectToRoute('home');
            }
 
            $user->setToken(null);
            $user->setPassword($passwordEncoder->encodePassword($user, $request->request->get('password')));
            $entityManager->flush();
 
            $this->addFlash('notice', 'Mot de passe mis à jour');
 
            return $this->redirectToRoute('login_eleve');
        }else {
 
            return $this->render('security/reset_password.html.twig', ['token' => $token]);
        }
 
    }

 }
