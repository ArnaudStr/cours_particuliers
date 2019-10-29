<?php

namespace App\Controller\Prof;

use DateTime;
use DateTimeZone;
use App\Entity\Prof;

use App\Entity\Cours;
use App\Entity\Eleve;
use App\Entity\Message;
use App\Entity\Seance;
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
use Rogervila\ArrayDiffMultidimensional;

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
        $nbMessagesNonLus = $this->getDoctrine()
        ->getRepository(Message::class)
        ->findNbNonLusProf($this->getUser());

        $session = new SessionUser();
        $session->set('nbMsgNonLus', $nbMessagesNonLus);

        // dd($session->get('nbMsgNonLus'));

        return $this->render('prof/calendrierProf.html.twig', [
            'title' => 'Planning'
        ]);
    }

    /**
     * @Route("/loginProf", name="login_prof")
     */
    public function loginProf(AuthenticationUtils $authenticationUtils)
    {
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

        return $this->redirectToRoute("search_course");
        // return $this->redirectToRoute("login_prof");
        // throw new \Exception('This method can be blank - it will be intercepted by the logout key on your firewall');
    }

    /**
     * @Route("/showProfileProf/{id}", name="show_profile_prof")
     */
    public function showProfileProf(Prof $prof)
    {
        $nbEtoiles = null;
        if ($noteMoyenne = $prof->getNoteMoyenne()){
            $nbEtoiles = round($noteMoyenne);
        }

        $prochaineSeanceCours = [];
        foreach($prof->getCoursS() as $cours){
            foreach ($cours->getEleves() as $eleve) {

                $proSeance = $this->getDoctrine()
                    ->getRepository(Seance::class)
                    ->findNextSeanceEleve($eleve, $cours);               

                array_push($prochaineSeanceCours, $proSeance);
            }
        }

        return $this->render('prof/showProfileProf.html.twig', [
            'prochainesSeances' => $prochaineSeanceCours,
            'nbEtoiles' => $nbEtoiles,
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
     * @Route("/editProfileProf/{id}", name="edit_profile_prof")
     */
    public function editProfileProf(Prof $prof, Request $request, ObjectManager $manager)
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

            $manager->persist($prof);

            $manager->flush();

            return $this->redirectToRoute('show_profile_prof', [
                'id' => $prof->getId()
            ]);
        }

        return $this->render('prof/editProfileProf.html.twig', [
            'editForm' => $form->createView(),
            'prof' => $prof
        ]);
    }

    /**
     * @Route("/editDisponibilitesProf/{id}", name="edit_disponibilites_prof")
     */
    public function editDisponibilitesProf(Prof $prof, Request $request, ObjectManager $manager)
    {       


        return $this->render('prof/disposProf.html.twig', [
            'title' => 'Disponibilites prof'
        ]);

    }

    public function ajoutSeances($nbSemaines, $disponibilites, ObjectManager $manager, Prof $prof){
        foreach($disponibilites as $jour=>$creneaux) {
            foreach($creneaux as $creneau) {
                for ($i=0; $i<$nbSemaines; $i++) {
                    for($heure=$creneau[0]; $heure<$creneau[1]; $heure++) {

                        $seance = new Seance();
                        $seance->setProf($prof);
                        $dateDebut = new DateTime('now',new DateTimeZone('Europe/Paris'));
                        $dateDebut->modify('next '.$jour.' +'.($i*7).' days');
                        $dateDebut->setTime($heure, 0);
                        
                        $seance->setDateDebut($dateDebut);

                        $manager->persist($seance);
                    }
                }
            }
        }
    }

    public function supprSeances($disponibilites, ObjectManager $manager, Prof $prof){
        foreach($disponibilites as $jour=>$creneaux) {
            foreach($creneaux as $creneau) {

                dump($creneau);
                $seances = $this->getDoctrine()
                ->getRepository(Seance::class)
                ->findToDelete($jour,$creneau[0],$creneau[1]-1, $prof);

                dump($seances);

                foreach($seances as $seance){
                    $manager->remove($seance);

                    $manager->flush();
                }
            }
        }

        // dd($seances);
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
            // return $this->redirectToRoute('showInfoseanceCours', ['id' => $seanceCours->getId()]);
        }
        return $this->render('course/addEditCreationCours.html.twig', ['form' => $form->createView(),
        'title' => $title, 'editMode' => $modif, 'cours' => $cours
        ]);
    }

    /**
     * @Route("/changementsDispos/{id}", name="changements_dispos")
     */
    public function changementsDispos(Prof $prof,  ObjectManager $manager) {

        $dispoAvantModif = $prof->getDisponibilites();
        $nouvellesDispos = json_decode($_COOKIE['dispos'], true);
        
        $prof->setDisponibilites($nouvellesDispos);
        
        $manager->persist($prof);
        
        // $manager->flush();


        // dump($dispoAvantModif);
        // dump($nouvellesDispos);

        // Nouvelles dispos
        // dump($toAdd = ArrayDiffMultidimensional::compare($nouvellesDispos, $dispoAvantModif));
        $toAdd = ArrayDiffMultidimensional::compare($nouvellesDispos, $dispoAvantModif);

        // dd($toAdd);
        $this->ajoutSeances(4, $toAdd, $manager, $prof);
        
        // Anciennes dispos
        // dd($toDelete = ArrayDiffMultidimensional::compare($dispoAvantModif,$nouvellesDispos));
        $toDelete = ArrayDiffMultidimensional::compare($dispoAvantModif,$nouvellesDispos);

        dump($toDelete);
        $this->supprSeances($toDelete, $manager, $prof);

        $manager->flush();

        // unset($_COOKIE["test"]);
        // setcookie("test", '', time() - 3600);


        return $this->redirectToRoute('show_profile_prof', [
            'id' => $prof->getId()
        ]);
    }

    /**
     * @Route("/ajouterSeances/{id}", name="ajouter_seances")
     */
    public function ajouterSeances() {
        return $this->render('prof/calendrierProf.html.twig', [
            'title' => 'Planning'
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
    public function inscriptionSeance() {
        return $this->render('course/showCourse.html.twig', [
            'title' => 'Planning'
        ]);
    }

    /**
     * @Route("/showMessagesProf/{id}", name="show_messages_prof")
     */
    public function showMessagesProf(Prof $prof) {

        // Conversations entre le prof et chaque eleve
        $allConversations = $this->getDoctrine()
            ->getRepository(Message::class)
            ->findAllConversationsProf($prof);

        // tableau [ [eleve, nombreMessagesNonLus],  [eleve, nombreMessagesNonLus], ...] 
        $allConversationsNbMsgNonLus = [];

        foreach($allConversations as $conversation){
            $eleve =  $conversation->getEleve();

            $nbMsgNonLus = $this->getDoctrine()
                ->getRepository(Message::class)
                ->findNbNonLusProfEleve($prof, $eleve);

            // On ajoute l'élève et le nombre de messages non lus
            array_push($allConversationsNbMsgNonLus, ['eleve' => $eleve, 'nbMsg' => $nbMsgNonLus]);      
        }

        return $this->render('prof/showMessageProf.html.twig', [
            'allConversations' => $allConversationsNbMsgNonLus
        ]);
    }

    
    /**
     * @Route("/sendMessageProf/{idProf}/{idEleve}", name="send_message_prof")
     * @ParamConverter("prof", options={"id" = "idProf"})
     * @ParamConverter("eleve", options={"id" = "idEleve"})
     */
    public function sendMessageProf(Prof $prof, Eleve $eleve)
    {
        $contenu = $_POST['text'];
        $message = new Message();
        $message->setProf($prof);
        $message->setEleve($eleve);
        $message->setAuteur($prof->getUsername());
        $message->setContenu($contenu);

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($message);
        $entityManager->flush();

        return $this->redirectToRoute('conversation_prof', ['idProf' => $prof->getId(), 'idEleve' => $eleve->getId()]);
    }

    /**
     * @Route("/conversationProf/{idProf}/{idEleve}/", name="conversation_prof")
     * @ParamConverter("prof", options={"id" = "idProf"})
     * @ParamConverter("eleve", options={"id" = "idEleve"})
     */
    public function conversationProf(Prof $prof, Eleve $eleve) {

        $session = new SessionUser();

        $allMsg = $this->getDoctrine()
            ->getRepository(Message::class)
            ->findConversation($eleve, $prof);

        $msgLus = $this->getDoctrine()
            ->getRepository(Message::class)
            ->findConversationLusProf($eleve, $prof);

        $msgNonLus = $this->getDoctrine()
            ->getRepository(Message::class)
            ->findConversationNonLusProf($eleve, $prof);

        $msgEnvoyes = $this->getDoctrine()
            ->getRepository(Message::class)
            ->findConversationEnvoyesProf($eleve, $prof);

        $entityManager = $this->getDoctrine()->getManager();

        foreach($msgNonLus as $message){
            $message->setLu(true);
            $entityManager->persist($message);
        }

        $nbMessagesNonLus = $this->getDoctrine()
            ->getRepository(Message::class)
            ->findNbNonLusProf($prof);

        $session->set('nbMsgNonLus', $nbMessagesNonLus);

        $entityManager->flush();

        return $this->render('prof/conversationProf.html.twig', [
            'prof' => $prof,
            'eleve' => $eleve,
            'allMsg' => $allMsg,
            'msgLus' => $msgLus,
            'msgNonLus' => $msgNonLus,
            'msgEnvoyes' => $msgEnvoyes
        ]);
    }

    /**     
     * @Route("/conversationProf/{idProf}/{idEleve}/ajax", name="conversation_prof_ajax")
     * @ParamConverter("prof", options={"id" = "idProf"})
     * @ParamConverter("eleve", options={"id" = "idEleve"})
     */
    public function ajaxProf(Prof $prof, Eleve $eleve) {
    
        $msgNonLus = $this->getDoctrine()
        ->getRepository(Message::class)
        ->findConversationNonLusProf($eleve, $prof);

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
     * @Route("/calendarProf", name="calendar_prof")
     */
    public function calendarProf() {
        return $this->render('prof/calendrierProf.html.twig', [
            'title' => 'Planning'
        ]);
    }

    /**
     * @Route("/demandeSeanceProf", name="demande_seances_prof")
     */
    public function demandeSeanceProf() {
        return $this->render('prof/validationSeances.html.twig', [
            'title' => 'Planning'
        ]);
    }

    /**
     * @Route("/validationSeanceProf/{id}/{valider}", name="validation_seances_prof")
     */
    public function validationSeanceProf(DemandeCours $demandeCours, int $valider) {

        $seance = $demandeCours->getSeance();
        if ($valider == 1) {
            $seance->setEleve($demandeCours->getEleve());
            $seance->setCours($demandeCours->getCours());
            if (!$seance->getCours()->getEleves()->contains($demandeCours->getEleve())){
                $seance->getCours()->addEleve($demandeCours->getEleve());
            };
        }

        $demandeCours->setRepondue(true);

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($demandeCours);
        $entityManager->persist($seance);
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
 
            $user = $entityManager->getRepository(Prof::class)->findOneByToken($token);
            /* @var $user User */
 
            if ($user === null) {
                $this->addFlash('danger', 'Token Inconnu');
                return $this->redirectToRoute('login_prof');
            }
            else if ($user->getTokenExpire()<new DateTime('now',new DateTimeZone('Europe/Paris'))){
                $this->addFlash('danger', 'Votre token de changement de mot de passe a expiré');
                return $this->redirectToRoute('login_prof');
            }
 
            $user->setToken(null);
            $user->setTokenExpire(null);
            $user->setPassword($passwordEncoder->encodePassword($user, $request->request->get('password')));
            $entityManager->flush();
 
            $this->addFlash('notice', 'Mot de passe mis à jour');
 
            return $this->redirectToRoute('login_prof');
        }else {
 
            return $this->render('security/reset_password.html.twig', ['token' => $token]);
        }
 
    }
    
}
