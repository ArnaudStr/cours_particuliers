<?php

namespace App\Controller\Eleve;

use App\Entity\Avis;
use App\Entity\Prof;
use App\Entity\Cours;
use App\Entity\Eleve;
use App\Entity\Seance;
use App\Form\AvisType;
use App\Entity\Message;
use App\Form\EditEleveType;
use App\Entity\DemandeCours;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Session\Session as SessionUser;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Csrf\TokenGenerator\TokenGeneratorInterface;

/**
 * @Route("/eleve")
 */
class EleveController extends AbstractController
{
    // Récupère le nombre de messages non lus
    public function setNbMsgNonLus() {
        $nbMessagesNonLus = $this->getDoctrine()
            ->getRepository(Message::class)
            ->findNbNonLusEleve($this->getUser());

        $session = new SessionUser();
        $session->set('nbMsgNonLus', $nbMessagesNonLus);
    }

    /**
     * @Route("/loginEleve", name="login_eleve")
     */
    public function loginEleve(AuthenticationUtils $authenticationUtils) {
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
        return $this->redirectToRoute("search_course");
    }

    /**
     * @Route("/", name="home_eleve")
     */
    public function indexEleve() {
        $this->setNbMsgNonLus();

        return $this->render('eleve/calendrierEleve.html.twig', [
            'title' => 'Planning'
        ]);
    }

    /**
     * @Route("/showProfileEleve/{id}", name="show_profile_eleve")
     */
    public function showProfileEleve(Eleve $eleve) {
        $this->setNbMsgNonLus();

        // liste des cours avec la prochaine séance
        $allCoursEtProchaineSeance = [];

        // couple [cours, prochainesSeance ]
        $coursEtProchaineSeance = [];

        foreach($eleve->getCours() as $cours){
            $coursEtProchaineSeance['cours'] = $cours;

            $proSeance = $this->getDoctrine()
                ->getRepository(Seance::class)
                ->findNextSeanceEleve($eleve, $cours); 

            if ($proSeance) {
                $coursEtProchaineSeance['seance'] = $proSeance;
            }
            else {
                $coursEtProchaineSeance['seance'] = null;
            }

            array_push($allCoursEtProchaineSeance, $coursEtProchaineSeance);

            $coursEtProchaineSeance = [];
        }

        return $this->render('eleve/showProfileEleve.html.twig', [
            'prochainesSeances' => $allCoursEtProchaineSeance
        ]);
    }   

    /**
     * @Route("/editEleve/{id}", name="edit_eleve")
     */
    public function editEleve(Eleve $eleve, Request $request) {
        $this->setNbMsgNonLus();

        // On récupere l'image avant le passage par le formulaire
        $pictureBeforeForm = $eleve->getPictureFilename();

        $form = $this->createForm(EditEleveType::class, $eleve);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            // Upload de la photo et inscription en BDD du nom de l'image, (si il y a eu une image dans le formulaire)
            if ( $pictureFilename = $form->get("pictureFilename")->getData() ) {
                $filename = md5(uniqid()).'.'.$pictureFilename->guessExtension();
                $pictureFilename->move($this->getParameter('pictures_directory'), $filename);
                $eleve->setPictureFilename($filename);
            }
            else {
                $eleve->setPictureFilename($pictureBeforeForm);
            }

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($eleve);
            $entityManager->flush();

            return $this->redirectToRoute('show_profile_eleve', ['id'=>$eleve->getID()]);
        }

        return $this->render('eleve/editProfileEleve.html.twig', [
            'editForm' => $form->createView(),
            // 'picture' => $pictureBeforeForm
        ]);
    }


    /**
     * @Route("/editPasswordEleve/{id}", name="edit_password_eleve")
     */
    public function editPasswordEleve(Eleve $eleve, Request $request, ObjectManager $manager, UserPasswordEncoderInterface $passwordEncoder){

        // $manager = $this->getDoctrine()->getManager();
        $form = $this->createForm(ChangePasswordType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
     
            // Si l'ancien mot de passe est bon
            if ($passwordEncoder->isPasswordValid($eleve, $form->get('oldPassword')->getData())) {
                    
                $newpwd = $form->get('newPassword')['first']->getData();
        
                $newEncodedPassword = $passwordEncoder->encodePassword($eleve, $newpwd);
                $eleve->setPassword($newEncodedPassword);
        
                //$em->persist($user);
                $manager->flush();

                $this->addFlash('notice', 'Votre mot de passe à bien été changé !');
                die('changé');

                return $this->redirectToRoute('show_profile_eleve', [
                    'id' => $eleve->getId()
                ]);

            }

            else return $this->redirectToRoute('show_profile_eleve', [
                'id' => $eleve->getId()
            ]);
        }

        return $this->render('security/changePassword.html.twig', array(
                    'form' => $form->createView(),
        ));
    }

    /**
     * @Route("/showMessagesEleve/{id}", name="show_messages_eleve")
     */
    public function showMessagesEleve(Eleve $eleve) {
        $this->setNbMsgNonLus();

        // Conversations entre le prof et chaque eleve
        $allConversations = $this->getDoctrine()
            ->getRepository(Message::class)
            ->findAllConversationsEleve($eleve);

        // tableau [ [prof, nombreMessagesNonLus],  [prof, nombreMessagesNonLus], ...] 
        $allConversationsNbMsgNonLus = [];

        foreach($allConversations as $conversation){
            $prof =  $conversation->getProf();

            $nbMsgNonLus = $this->getDoctrine()
                ->getRepository(Message::class)
                ->findNbNonLusEleveProf($prof, $eleve);

            // On ajoute l'élève et le nombre de messages non lus
            array_push($allConversationsNbMsgNonLus, ['prof' => $prof, 'nbMsg' => $nbMsgNonLus]);      
        }

        return $this->render('eleve/showMessageEleve.html.twig', [
            'allConversations' => $allConversationsNbMsgNonLus
        ]);
    }

    /**
     * @Route("/sendMessageEleve/{idProf}/{idEleve}", name="send_message_eleve")
     * @ParamConverter("prof", options={"id" = "idProf"})
     * @ParamConverter("eleve", options={"id" = "idEleve"})
     */
    public function sendMessageEleve(Prof $prof, Eleve $eleve)
    {
        $this->setNbMsgNonLus();
        
        $contenu = $_POST['text'];
        $message = new Message();
        $message->setProf($prof);
        $message->setEleve($eleve);
        $message->setAuteur($eleve->getUsername());
        $message->setContenu($contenu);

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($message);
        $entityManager->flush();

        return $this->redirectToRoute('conversation_eleve', ['idProf' => $prof->getId(), 'idEleve' => $eleve->getId()]);
    }

    /**
     * @Route("/conversationEleve/{idEleve}/{idProf}/", name="conversation_eleve")
     * @ParamConverter("eleve", options={"id" = "idEleve"})
     * @ParamConverter("prof", options={"id" = "idProf"})
     */
    public function conversationEleve(Eleve $eleve, Prof $prof) {
        $this->setNbMsgNonLus();

        $session = new SessionUser();

        $allMsg = $this->getDoctrine()
            ->getRepository(Message::class)
            ->findConversation($eleve, $prof);

        $msgLus = $this->getDoctrine()
            ->getRepository(Message::class)
            ->findConversationLusEleve($eleve, $prof);

        $msgNonLus = $this->getDoctrine()
            ->getRepository(Message::class)
            ->findConversationNonLusEleve($eleve, $prof);

        $msgEnvoyes = $this->getDoctrine()
            ->getRepository(Message::class)
            ->findConversationEnvoyesEleve($eleve, $prof);

        $entityManager = $this->getDoctrine()->getManager();

        foreach($msgNonLus as $message){
            $message->setLu(true);
            $entityManager->persist($message);
        }

        $nbMessagesNonLus = $this->getDoctrine()
            ->getRepository(Message::class)
            ->findNbNonLusEleve($eleve);

        $session->set('nbMsgNonLus', $nbMessagesNonLus);

        $entityManager->flush();

        return $this->render('eleve/conversationEleve.html.twig', [
            'prof' => $prof,
            'eleve' => $eleve,
            'allMsg' => $allMsg,
            'msgLus' => $msgLus,
            'msgNonLus' => $msgNonLus,
            'msgEnvoyes' => $msgEnvoyes
        ]);
    }

    /**     
     * @Route("/conversationEleve/{idEleve}/{idProf}/ajax", name="conversation_eleve_ajax")
     * @ParamConverter("prof", options={"id" = "idProf"})
     * @ParamConverter("eleve", options={"id" = "idEleve"})
     */
    public function ajaxEleve(Prof $prof, Eleve $eleve) {
    
        $msgNonLus = $this->getDoctrine()
            ->getRepository(Message::class)
            ->findConversationNonLusEleve($eleve, $prof);

        $nouveauMessage = false;

        if ($msgNonLus){
            $nouveauMessage = true;
        }   

        return $this->render('prof/test.html.twig', [
            'prof' => $prof,
            'eleve' => $eleve,
            'nouveauMessage' => $nouveauMessage,
        ]);
    }
    
    /**
     * @Route("/inscriptionCoursEleve/{idProf}/{idCours}", name="inscription_cours_eleve")
     * @ParamConverter("prof", options={"id" = "idProf"})
     * @ParamConverter("cours", options={"id" = "idCours"})
     */
    public function inscriptionCoursEleve(Prof $prof, Cours $cours) {
        $this->setNbMsgNonLus();
        
        return $this->render('course/inscriptionCours.html.twig', [
            'prof' => $prof,
            'cours' => $cours,
        ]);
    }
    
    /**
     * @Route("/demandeInscriptionSeance/{idSeance}/{idEleve}/{idCours}", name="demande_inscription_seance")
     * @ParamConverter("seance", options={"id" = "idSeance"})
     * @ParamConverter("eleve", options={"id" = "idEleve"})
     * @ParamConverter("cours", options={"id" = "idCours"})
     */
    public function demandeInscriptionSeance(Seance $seance, Eleve $eleve, Cours $cours) {
        $this->setNbMsgNonLus();

        // Inscription élève au cours
        $demandeCours = new DemandeCours();

        $demandeCours->setSeance($seance);
        $demandeCours->setEleve($eleve);
        $demandeCours->setCours($cours);
        $demandeCours->setModeCours('test');

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($demandeCours);
        $entityManager->flush();
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
        $this->setNbMsgNonLus();

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
            $noteMoyenne = round(array_sum($prof->getNotes())/count($prof->getNotes()),1);
            $prof->setNoteMoyenne($noteMoyenne);
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
                $date = new DateTime('now',new DateTimeZone('Europe/Paris'));
                $date->add(new \DateInterval('P1D'));
    
                $user->setTokenExpire(
                    $date
                );
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
                return $this->redirectToRoute('login_eleve');
            }
            else if ($user->getTokenExpire()<new DateTime('now',new DateTimeZone('Europe/Paris'))){
                $this->addFlash('danger', 'Votre token de changement de mot de passe a expiré');
                return $this->redirectToRoute('login_eleve');
            }
 
            $user->setToken(null);
            $user->setTokenExpire(null);
            $user->setPassword($passwordEncoder->encodePassword($user, $request->request->get('password')));
            $entityManager->flush();
 
            $this->addFlash('notice', 'Mot de passe mis à jour');
 
            return $this->redirectToRoute('login_eleve');
        }else {
 
            return $this->render('security/reset_password.html.twig', ['token' => $token]);
        }
    }

    /**
     * @Route("/voirProfilProf/{id}", name="voir_profil_prof")
     */
    public function voirProfilProf(Prof $prof)
    {
        $this->setNbMsgNonLus();

        $nbEtoiles = null;
        if ($notes = $prof->getNotes()){
            $noteMoyenne = round(array_sum($notes)/count($notes),1);
            $nbEtoiles = round($noteMoyenne);
        }
 
        return $this->render('prof/pagePubliqueProf.html.twig', [
            'prof' => $prof,
            'nbEtoiles' => $nbEtoiles,
        ]);
 
    }

    /**
     * @Route("/searchCourseEleve", name="search_course_eleve")
     */
    public function searchCourseEleve()
    {
        $this->setNbMsgNonLus();

        return $this->render('course/searchCourse.html.twig', [
        ]);
    }

    /**
     * @Route("/displayCourseEleve/{id}", name="display_course_eleve")
     */
    public function displayCoursEleve(Cours $cours)
    {
        $this->setNbMsgNonLus();

        $nbEtoiles = null;
        if ($noteMoyenne = $cours->getProf()->getNoteMoyenne()){
            $nbEtoiles = round($noteMoyenne);
        }
        else $noteMoyenne = 'Pas encore noté';

        return $this->render('course/displayCourse.html.twig', [
            'cours' => $cours,
            'noteMoyenne' => $noteMoyenne,
            'nbEtoiles' => $nbEtoiles,
        ]);
    }

    
    /**
     * @Route("/leaflet", name="leaflet")
     */
    public function leaflet() {
        return $this->render('leaflet.html.twig');

    }

}
