<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    /**
     * @Route("/eleve/loginEleve", name="app_login_eleve")
     */
    public function loginEleve(AuthenticationUtils $authenticationUtils): Response
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
     * @Route("/prof/loginProf", name="app_login_prof")
     */
    public function loginProf(AuthenticationUtils $authenticationUtils): Response
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
     * @Route("/prof/logoutProf", name="app_logout_prof")
     */
    public function logoutProf() {
        
        return $this->redirectToRoute("home");
        // return $this->redirectToRoute("app_login_prof");
        // throw new \Exception('This method can be blank - it will be intercepted by the logout key on your firewall');
    }

    /**
     * @Route("/eleve/logoutEleve", name="app_logout_eleve")
     */
    public function logoutEleve() {
        
        return $this->redirectToRoute("home");
        // return $this->redirectToRoute("app_login_eleve");
        // throw new \Exception('This method can be blank - it will be intercepted by the logout key on your firewall');
    }
}
