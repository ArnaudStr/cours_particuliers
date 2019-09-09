<?php

namespace App\Controller;

use App\Entity\Admin;
use App\Entity\Activite;
use App\Entity\Categorie;
use App\Form\ActiviteType;
use App\Form\CategorieType;
use App\Form\RegistrationFormType;
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
     * @Route("/", name="admin_home")
     */
    public function index()
    {
        return $this->render('admin/home.html.twig', [
            'controller_name' => 'HomeController',
        ]);
    }

    /**
     * @Route("/register", name="app_admin_register")
     */
    public function register(Request $request, UserPasswordEncoderInterface $passwordEncoder, GuardAuthenticatorHandler $guardHandler, AdminAuthenticator $authenticator): Response
    {
        $user = new Admin();
        $form = $this->createForm(RegistrationFormType::class, $user);
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

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }

    /**
     * @Route("/login", name="app_admin_login")
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

        return $this->render('security/login.html.twig', ['last_username' => $lastUsername, 'error' => $error]);
    }

    /**
     * @Route("/logout", name="app_admin_logout")
     */
    public function logout()
    {
        return $this->redirectToRoute("home");

    }

    /**
     * @Route("/add/categorie", name="add_categorie")
     * @Route("/edit/categorie/{id}", name="edit_categorie")
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
 
            return $this->redirectToRoute('admin_home');
            // return $this->redirectToRoute('showInfoCategorie', ['id' => $categorie->getId()]);
        }
        return $this->render('course/addEditCategory.html.twig', ['form' => $form->createView(),
            'title' => $title, 'editMode' => $categorie->getId() != null, 'categorie' => $categorie
        ]);
    }

    /**
     * @Route("/add_activite", name="add_activite")
     * @Route("/edit/activite/{id}", name="edit_activite")
     */
    public function addEditActivity(Activite $activite = null, ObjectManager $manager, Request $request) {
        if(!$activite) {
            $activite = new Activite();
            $title = "Ajout d'une activite";
        }
 
        else {
            $title = 'Modification de la activite '.$activite;
        }
 
        $form = $this->createForm(ActiviteType::class, $activite);
        
        $form->handleRequest($request);
               
        if($form->isSubmitted() && $form->isValid()) {
            $manager->persist($activite);
            $manager->flush();
 
            return $this->redirectToRoute('admin_home');
            // return $this->redirectToRoute('showInfoActivite', ['id' => $activite->getId()]);
        }
        return $this->render('course/addEditActivity.html.twig', ['form' => $form->createView(),
            'title' => $title, 'editMode' => $activite->getId() != null, 'activite' => $activite
        ]);
    }
}
