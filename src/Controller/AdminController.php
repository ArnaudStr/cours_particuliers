<?php

namespace App\Controller;

use App\Entity\Avis;
use App\Entity\Prof;
use App\Entity\Admin;
use App\Entity\Eleve;
use App\Entity\Seance;

use App\Entity\Message;
use App\Entity\Activite;
use App\Entity\Categorie;
use App\Form\ActiviteType;
use App\Form\CategorieType;
use App\Form\RegistrationAdminType;
use App\Security\AdminAuthenticator;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\Security\Guard\GuardAuthenticatorHandler;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

/**
 * @Route("/admin")
 */
class AdminController extends AbstractController
{

    /**
     * @Route("/register", name="register_admin")
     */
    public function register(Request $request, UserPasswordEncoderInterface $passwordEncoder, GuardAuthenticatorHandler $guardHandler, AdminAuthenticator $authenticator): Response
    {
        $user = new Admin();
        $form = $this->createForm(RegistrationAdminType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // encode the plain password
            $user->setPassword(
                $passwordEncoder->encodePassword(
                    $user,
                    $form->get('plainPassword')->getData()
                )
            );

            $user->setRoles(["ROLE_ADMIN"]);

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($user);
            $entityManager->flush();

            // do anything else you need here, like send an email

            return $guardHandler->authenticateUserAndHandleSuccess(
                $user,
                $request,
                $authenticator,
                'admin_security' // firewall name in security.yaml
            );
        }

        return $this->render('registration/registerAdmin.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }

    /**
     * @Route("/login", name="login_admin")
     */
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/loginAdmin.html.twig', ['last_username' => $lastUsername, 'error' => $error]);
    }

    /**
     * @Route("/logout", name="logout_admin")
     * @IsGranted("ROLE_ADMIN")
     */
    public function logout()
    {
        return $this->redirectToRoute("home");
    }

    /**
     * @Route("/addCategorie", name="add_categorie")
     * @Route("/editCategorie/{id}", name="edit_categorie")
     * @IsGranted("ROLE_ADMIN")
     */
    public function addEditCategorie(Categorie $categorie = null, ObjectManager $manager, Request $request) {
        if(!$categorie) {
            $categorie = new Categorie();
            $title = "Ajout d'une categorie";
        }
 
        else {
            $title = 'Modification de la categorie '.$categorie;
        }
 
        $form = $this->createForm(CategorieType::class, $categorie);
        
        $form->handleRequest($request);
               
        if($form->isSubmitted() && $form->isValid()) {
            $manager->persist($categorie);
            $manager->flush();
 
            return $this->redirectToRoute('show_list_activites_categories');
            // return $this->redirectToRoute('showInfoCategorie', ['id' => $categorie->getId()]);
        }
        return $this->render('admin/addEditCategory.html.twig', ['form' => $form->createView(),
            'title' => $title, 'editMode' => $categorie->getId() != null, 'categorie' => $categorie
        ]);
    }

    /**
     * @Route("/deleteCategorie/{id}", name="delete_categorie")
     * @IsGranted("ROLE_ADMIN")
     */
    public function deleteCategorie(Categorie $categorie, ObjectManager $manager) {
        $manager->remove($categorie);
        $manager->flush();
  
        return $this->redirectToRoute('show_list_activites_categories');
    }

    /**
     * @Route("/showListActivitesCategories", name="show_list_activites_categories")
     * @Route("/", name="home_admin")
     * @IsGranted("ROLE_ADMIN")
     */
    public function showListActivitesCategories() {
        // On enregistre les formations
        $all_categories = $this->getDoctrine()->getRepository(Categorie::class)->findAll();
        // Appel à la vue d'affichage des formations
        return $this->render('admin/showListCategories.html.twig', [
            'title' => 'Liste des catégories',
            'categories' => $all_categories
        ]);
    }

    /**
     * @Route("/addActivite", name="add_activite")
     * @Route("/editActivite/{id}", name="edit_activite")
     * @IsGranted("ROLE_ADMIN")
     */
    public function addEditActivity(Activite $activite = null, ObjectManager $manager, Request $request) {
        if(!$activite) {
            $activite = new Activite();
            $title = "Ajout d'une activite";
        }
 
        else {
            $title = 'Modification de l\'activite '.$activite;
        }
 
        $form = $this->createForm(ActiviteType::class, $activite);
        
        $form->handleRequest($request);
               
        if($form->isSubmitted() && $form->isValid()) {
            $manager->persist($activite);
            $manager->flush();
 
            return $this->redirectToRoute('show_list_activites_categories');
            // return $this->redirectToRoute('showInfoActivite', ['id' => $activite->getId()]);
        }
        return $this->render('admin/addEditActivity.html.twig', ['form' => $form->createView(),
            'title' => $title, 'editMode' => $activite->getId() != null, 'activite' => $activite
        ]);
    }
    
    /**
     * @Route("/deleteActivite/{id}", name="delete_activite")
     * @IsGranted("ROLE_ADMIN")
     */
    public function deleteActivite(Activite $activite, ObjectManager $manager) {
        $manager->remove($activite);
        $manager->flush();
  
        return $this->redirectToRoute('show_list_activites_categories');
    }
    
    /**
     * @Route("/showListMembers", name="show_list_members")
     * @IsGranted("ROLE_ADMIN")
     */
    public function showListMembers() {
        // On enregistre les formations
        $profs = $this->getDoctrine()->getRepository(Prof::class)->findAll();
        $eleves = $this->getDoctrine()->getRepository(Eleve::class)->findAll();
        // Appel à la vue d'affichage des formations
        return $this->render('admin/showListMembers.html.twig', [
            'title' => 'Liste des membres',
            'profs' => $profs,
            'eleves' => $eleves,
        ]);
    }

    /**
     * @Route("/deleteProf/{id}", name="delete_prof_admin")
     * @IsGranted("ROLE_ADMIN")
     */
    public function deleteProf(Prof $prof, ObjectManager $manager) {
        $manager->remove($prof);
        $manager->flush();
  
        return $this->redirectToRoute('show_list_members');
    }

    /**
     * @Route("/deleteEleve/{id}", name="delete_eleve_admin")
     * @IsGranted("ROLE_ADMIN")
     */
    public function deleteEleve(Eleve $eleve, ObjectManager $manager) {
        foreach($eleve->getSeances() as $seance){
            $seance->setEleve(null);
            $seance->setCours(null);
            $manager->persist($seance);
        }

        $manager->remove($eleve);
        $manager->flush();
  
        return $this->redirectToRoute('show_list_members');
    }

    /**
     * @Route("/showReviewsProfAdmin/{id}", name="show_reviews_prof_admin")
     * @IsGranted("ROLE_ADMIN")
     */
    public function showReviewsProfAdmin(Prof $prof) {

        return $this->render('admin/showListReviews.html.twig', [
            'title' => 'Liste des avis de '.$prof,
            'prof' => $prof,
        ]);
    }

    /**
     * @Route("/editProfAdmin/{id}", name="edit_prof_admin")
     * @IsGranted("ROLE_ADMIN")
     */
    public function editProfAdmin(Prof $prof) {

        return $this->render('admin/editProfAdmin.html.twig', [
            'title' => 'Profil de '.$prof,
            'prof' => $prof,
        ]);
    }

    /**
     * @Route("/editEleveAdmin/{id}", name="edit_eleve_admin")
     * @IsGranted("ROLE_ADMIN")
     */
    public function editEleveAdmin(Eleve $eleve) {

        return $this->render('admin/editEleveAdmin.html.twig', [
            'title' => 'Profil de '.$eleve,
            'eleve' => $eleve,
        ]);
    }

    /**
     * @Route("/deleteReviewProfAdmin/{id}", name="delete_review_admin")
     * @IsGranted("ROLE_ADMIN")
     */
    public function deleteReviewProfAdmin(Avis $avis, ObjectManager $manager) {

        $manager->remove($avis);

        $manager->flush();

        $noteMoyenne = round($this->getDoctrine()
            ->getRepository(Avis::class)
            ->findNoteMoyenne($avis->getProf()),1);

        $avis->getProf()->setNoteMoyenne($noteMoyenne);

        $manager->persist($avis->getProf());

        $manager->flush();
  
        return $this->redirectToRoute('show_list_members', [
            'title' => 'Liste des avis de '.$avis->getProf(),
            'prof' => $avis->getProf(),
        ]);

    }


    // /**
    //  * @Route("/deleteSeancesPassees", name="delete_seances_passees")
    //  */
    // public function deleteSeancesPassees(ObjectManager $manager) {
    //     $seances = $this->getDoctrine()
    //         ->getRepository(Seance::class)
    //         ->findSeancesLibresPassees();
            
    //     foreach($seances as $seance){
    //         $manager->remove($seance);
    //     }

    //     $manager->flush();

    //     return $this->redirectToRoute('show_list_members');
    // }

    // /**
    //  * @Route("/deleteMessagesPassees", name="delete_messages_passes")
    //  */
    // public function deleteMessagesPasses(ObjectManager $manager) {

    //     // Le nombre de jours à partir duquel on supprime les anciens messages
    //     $nbJours = 15;

    //     $messages = $this->getDoctrine()
    //         ->getRepository(Message::class)
    //         ->deleteMessagesNbJours($nbJours);
            
    //     foreach($messages as $message){
    //         $manager->remove($message);
    //     }

    //     $manager->flush();

    //     return $this->redirectToRoute('show_list_members');
    // }
}

