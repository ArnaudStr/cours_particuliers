<?php

namespace App\Controller\Prof;

use DateTime;
use DateTimeZone;
use App\Entity\Prof;

use App\Entity\Cours;
use App\Entity\Eleve;
use App\Entity\Creneau;
use App\Entity\Message;
use App\Entity\Session;
use App\Form\MessageType;
use App\Form\EditProfType;
use App\Form\CreationCoursType;
use App\Entity\DemandeCours;

use Symfony\Component\Filesystem\Filesystem;
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
 * @Route("/prof")
 */
class ProfController extends AbstractController
{    
    /**
     * @Route("/", name="home_prof")
     */
    public function indexProf()
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
        
        return $this->render('prof/calendrierProf.html.twig', [
            'title' => 'Planning'
        ]);
    }

    /**
     * @Route("/loginProf", name="login_prof")
     */
    public function loginProf(AuthenticationUtils $authenticationUtils)
    {
        // if ($this->getUser()) {
        //    $this->redirectToRoute('target_path');
        // }

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/loginProf.html.twig', ['last_username' => $lastUsername, 'error' => $error]);
    }

    /**
     * @Route("/logoutProf", name="logout_prof")
     */
    public function logoutProf() {
        
        $this->clear();

        return $this->redirectToRoute("home");
        // return $this->redirectToRoute("login_prof");
        // throw new \Exception('This method can be blank - it will be intercepted by the logout key on your firewall');
    }

    /**
     * @Route("/showProfileProf/{id}", name="show_profile_prof")
     */
    public function showProfileProf(Prof $prof)
    {
        // dd($prof->getNotes());
        if ($notes = $prof->getNotes()){
            $noteMoyenne = round(array_sum($notes)/count($notes),1);
            $nbEtoiles = round($noteMoyenne);
        }
        else $noteMoyenne = 'Pas encore noté';

        return $this->render('prof/showProfileProf.html.twig', [
            'noteMoyenne' => $noteMoyenne,
            'nbEtoiles' => $nbEtoiles

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
    public function editProf(Prof $prof, Request $request, ObjectManager $manager)
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
            
            // On parcours les disponibilités du prof
            foreach ($prof->getCreneaux() as $creneau){             
                $this->ajoutSessions(4, $creneau, $manager);              
            }
                
            $entityManager->flush();
                 
            // do anything else you need here, like send an email

            return $this->redirectToRoute('show_profile_prof');
        }

        return $this->render('prof/editProfileProf.html.twig', [
            'editForm' => $form->createView(),
            'prof' => $prof
        ]);
    }

    public function ajoutSessions($nbSemaines, Creneau $creneau, ObjectManager $manager){
        for ($i=0; $i<$nbSemaines; $i++){

            $session = new Session();
            $session->setProf($creneau->getProf());

            // On crée la crée la session pour la semaine suivante
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

    /**
     * @Route("/addProposeCours/{idProf}", name="add_propose_cours")
     * @Route("/editProposeCours/{idProf}/{idCours}", name="edit_propose_cours")
     * @ParamConverter("prof", options={"id" = "idProf"})
     * @ParamConverter("cours", options={"id" = "idCours"})
     */
    public function addEditCoursProf(Prof $prof, Cours $cours = null, ObjectManager $manager, Request $request) {
       
        $modif = true;

        // si $creaneauCours est null (add)
        if (!$cours){
            $modif = false;
            $cours = new Cours();
            $cours->setProf($prof);
            $title = 'Ajout d\'un cours';
        }

        else{
            $title = 'Modification de cours '.$cours;
        }

        $form = $this->createForm(CreationCoursType::class, $cours);

        $form->handleRequest($request);
               
        if($form->isSubmitted() && $form->isValid()) {

            // // On met les creneaux dans le cours
            $manager->persist($cours);

            $manager->flush();
 
            return $this->redirectToRoute('home_prof');
            // return $this->redirectToRoute('showInfosessionCours', ['id' => $sessionCours->getId()]);
        }
        return $this->render('course/addEditCreationCours.html.twig', ['form' => $form->createView(),
        'title' => $title, 'editMode' => $modif, 'cours' => $cours
        ]);
    }

    /**
     * @Route("/showListeCours", name="show_liste_cours")
     */
    public function showListeCours() {
        return $this->render('prof/showListeCoursProf.html.twig', [
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

        $session = new SessionUser();
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
                    $session->set('nbMsgNonLus', ($session->get('nbMsgNonLus'))-1);
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

    /**
     * @Route("/calendarProf", name="calendar_prof")
     */
    public function calendarProf() {
        return $this->render('prof/calendrierProf.html.twig', [
            'title' => 'Planning'
        ]);
    }

    /**
     * @Route("/demandeSessionProf", name="demande_sessions_prof")
     */
    public function demandeSessionProf() {
        return $this->render('prof/validationSessions.html.twig', [
            'title' => 'Planning'
        ]);
    }

    /**
     * @Route("/validationSessionProf/{id}/{valider}", name="validation_sessions_prof")
     */
    public function validationSessionProf(DemandeCours $demandeCours, int $valider) {

        $session = $demandeCours->getSession();
        if ($valider == 1) {
            $session->setEleve($demandeCours->getEleve());
            $session->setCours($demandeCours->getCours());
        }

        $demandeCours->setRepondue(true);

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($demandeCours);
        $entityManager->persist($session);
        $entityManager->flush();

        return $this->redirectToRoute('home_prof');

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
            $user = $entityManager->getRepository(Prof::class)->findOneByEmail($email);
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
 
            $user = $entityManager->getRepository(Prof::class)->findOneByToken($token);
            /* @var $user User */
 
            if ($user === null) {
                $this->addFlash('danger', 'Token Inconnu');
                return $this->redirectToRoute('home');
            }
 
            $user->setToken(null);
            $user->setPassword($passwordEncoder->encodePassword($user, $request->request->get('password')));
            $entityManager->flush();
 
            $this->addFlash('notice', 'Mot de passe mis à jour');
 
            return $this->redirectToRoute('login_prof');
        }else {
 
            return $this->render('security/reset_password.html.twig', ['token' => $token]);
        }
 
    }
    
}
