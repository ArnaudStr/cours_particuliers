<?php

namespace App\Controller;

use App\Entity\Prof;
use App\Entity\Admin;
use App\Entity\Eleve;
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
     * @Route("/", name="home_admin")
     */
    public function index()
    {
        return $this->render('admin/home.html.twig', [
            'controller_name' => 'HomeController',
        ]);
    }

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
        // if ($this->getUser()) {
        //    $this->redirectToRoute('target_path');
        // }

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/loginAdmin.html.twig', ['last_username' => $lastUsername, 'error' => $error]);
    }

    /**
     * @Route("/logout", name="logout_admin")
     */
    public function logout()
    {
        return $this->redirectToRoute("home");

    }



    /**
     * @Route("/addCategorie", name="add_categorie")
     * @Route("/editCategorie/{id}", name="edit_categorie")
     */
    public function addEditCategorie(Categorie $categorie = null, ObjectManager $manager, Request $request) {
        if(!$categorie) {
            $categorie = new Categorie();
            $title = "Ajout d'une categorie";
        }
 
        else {
            $title = 'Modification de la categorie '.$categorie->getNom();
        }
 
        $form = $this->createForm(CategorieType::class, $categorie);
        
        $form->handleRequest($request);
               
        if($form->isSubmitted() && $form->isValid()) {
            $manager->persist($categorie);
            $manager->flush();
 
            return $this->redirectToRoute('home_admin');
            // return $this->redirectToRoute('showInfoCategorie', ['id' => $categorie->getId()]);
        }
        return $this->render('admin/addEditCategory.html.twig', ['form' => $form->createView(),
            'title' => $title, 'editMode' => $categorie->getId() != null, 'categorie' => $categorie
        ]);
    }

    /**
     * @Route("/deleteCategorie/{id}", name="delete_categorie")
     */
    public function deleteCategorie(Categorie $categorie, ObjectManager $manager) {
        $manager->remove($categorie);
        $manager->flush();
  
        return $this->redirectToRoute('show_list_activites_categories');
    }

    /**
     * @Route("/showListActivitesCategories", name="show_list_activites_categories")
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


    // /**
    //  * @Route("/showListActivites", name="show_list_activites")
    //  */
    // public function showListActivities() {
    //     // On enregistre les formations
    //     $all_activities = $this->getDoctrine()->getRepository(Activite::class)->findAll();
    //     // Appel à la vue d'affichage des formations
    //     return $this->render('admin/showListActivities.html.twig', [
    //         'title' => 'Liste des activités',
    //         'activites' => $all_activities
    //     ]);
    // }


    /**
     * @Route("/addActivite", name="add_activite")
     * @Route("/editActivite/{id}", name="edit_activite")
     */
    public function addEditActivity(Activite $activite = null, ObjectManager $manager, Request $request) {
        if(!$activite) {
            $activite = new Activite();
            $title = "Ajout d'une activite";
        }
 
        else {
            $title = 'Modification de la activite '.$activite->getNom();
        }
 
        $form = $this->createForm(ActiviteType::class, $activite);
        
        $form->handleRequest($request);
               
        if($form->isSubmitted() && $form->isValid()) {
            $manager->persist($activite);
            $manager->flush();
 
            return $this->redirectToRoute('home_admin');
            // return $this->redirectToRoute('showInfoActivite', ['id' => $activite->getId()]);
        }
        return $this->render('admin/addEditActivity.html.twig', ['form' => $form->createView(),
            'title' => $title, 'editMode' => $activite->getId() != null, 'activite' => $activite
        ]);
    }
    
    /**
     * @Route("/deleteActivite/{id}", name="delete_activite")
     */
    public function deleteActivite(Activite $activite, ObjectManager $manager) {
        $manager->remove($activite);
        $manager->flush();
  
        return $this->redirectToRoute('show_list_activites_categories');
    }

    /**
     * @Route("/deleteMessages", name="delete_messages")
     */
    public function deleteMessages(ObjectManager $manager) {

        $messages = $this->getDoctrine()
        ->getRepository(Message::class)
        ->findAllToDelete();  

        foreach ($messages as $message) {
            $manager->remove($message);
        }

        $manager->flush();

        return $this->redirectToRoute('home_admin');

    }

    
    /**
     * @Route("/showListMembers", name="show_list_members")
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
     * @Route("/deleteProf/{id}", name="delete_prof")
     */
    public function deleteProf(Prof $prof, ObjectManager $manager) {
        $manager->remove($prof);
        $manager->flush();
  
        return $this->redirectToRoute('show_list_members');
    }

    /**
     * @Route("/deleteEleve/{id}", name="delete_eleve")
     */
    public function deleteEleve(Eleve $eleve, ObjectManager $manager) {
        $manager->remove($eleve);
        $manager->flush();
  
        return $this->redirectToRoute('show_list_members');
    }
}
