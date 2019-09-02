<?php

namespace App\Controller;

use App\Entity\Prof;
use App\Entity\Eleve;
use App\Form\RegistrationType;
use App\Security\ProfAuthenticator;
use App\Security\EleveAuthenticator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Guard\GuardAuthenticatorHandler;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class RegistrationController extends AbstractController
{
  
    /**
     * @Route("/register", name="app_register")
     */
    public function register(Request $request, UserPasswordEncoderInterface $passwordEncoder, GuardAuthenticatorHandler $guardHandler, ProfAuthenticator $authenticator): Response
    {       

        $form = $this->createForm(RegistrationType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            if ( $form->get('isEleve')->getData() ) {

                $user = new Eleve();

                $user->setRoles(["ROLE_ELEVE"]);

                $route = $this->redirectToRoute('app_login_eleve');
            }

            else {

                $user = new Prof();

                $user->setRoles(["ROLE_PROF"]);

                $route = $this->redirectToRoute('app_login_prof');
            }

                $user->setUsername(
                    $form->get('username')->getData()
                );
    
                // encode the plain password
                $user->setPassword(
                    $passwordEncoder->encodePassword(
                        $user,
                        $form->get('plainPassword')->getData()
                    )
                );

                $user->setEmail(
                    $form->get('email')->getData()
                );

                $user->setNom(
                    $form->get('nom')->getData()
                );

                $user->setPrenom(
                    $form->get('prenom')->getData()
                );

                $user->setAdresse(
                    $form->get('adresse')->getData()
                );
    
                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->persist($user);
                $entityManager->flush();
    
                // do anything else you need here, like send an email

                return $route;
            }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }
}
