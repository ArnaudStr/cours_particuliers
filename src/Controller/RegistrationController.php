<?php

namespace App\Controller;

use App\Entity\Prof;
use App\Entity\Eleve;
use App\Form\RegistrationProfType;
use App\Form\RegistrationEleveType;
use App\Security\ProfAuthenticator;
use App\Security\UserAuthenticator;
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
     * @Route("/register/eleve", name="app_register_eleve")
     */
    public function registerEleve(Request $request, UserPasswordEncoderInterface $passwordEncoder, GuardAuthenticatorHandler $guardHandler, EleveAuthenticator $authenticator): Response
    {
        $user = new Eleve();
        $form = $this->createForm(RegistrationEleveType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $user->setRoles(["ROLE_USER"]);

            $user->setRoles(["ROLE_ELEVE"]);

            // encode the plain password
            $user->setPassword(
                $passwordEncoder->encodePassword(
                    $user,
                    $form->get('plainPassword')->getData()
                )
            );

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($user);
            $entityManager->flush();

            // do anything else you need here, like send an email

            return $guardHandler->authenticateUserAndHandleSuccess(
                $user,
                $request,
                $authenticator,
                'eleve_security' // firewall name in security.yaml
            );
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }

    // /**
    //  * @Route("/register/prof", name="app_register_prof")
    //  */
    // public function registerProf(Request $request, UserPasswordEncoderInterface $passwordEncoder, GuardAuthenticatorHandler $guardHandler, ProfAuthenticator $authenticator): Response
    // {
    //     $user = new Prof();
    //     $form = $this->createForm(RegistrationProfType::class, $user);
    //     $form->handleRequest($request);

    //     if ($form->isSubmitted() && $form->isValid()) {



    //         $user->setRoles(["ROLE_PROF"]);
        

    //         // encode the plain password
    //         $user->setPassword(
    //             $passwordEncoder->encodePassword(
    //                 $user,
    //                 $form->get('plainPassword')->getData()
    //             )
    //         );

    //         $entityManager = $this->getDoctrine()->getManager();
    //         $entityManager->persist($user);
    //         $entityManager->flush();

    //         // do anything else you need here, like send an email

    //         return $guardHandler->authenticateUserAndHandleSuccess(
    //             $user,
    //             $request,
    //             $authenticator,
    //             'prof_security' // firewall name in security.yaml
    //         );
    //     }

    //     return $this->render('registration/register.html.twig', [
    //         'registrationForm' => $form->createView(),
    //     ]);
    // }

    /**
     * @Route("/register", name="app_register")
     */
    public function register(Request $request, UserPasswordEncoderInterface $passwordEncoder, GuardAuthenticatorHandler $guardHandler, ProfAuthenticator $authenticator): Response
    {       

        $form = $this->createForm(RegistrationProfType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            if ( $form->get('isEleve')->getData() ) {

                $user = new Eleve();

                $user->setRoles(["ROLE_ELEVE"]);

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
    
                return $guardHandler->authenticateUserAndHandleSuccess(
                    $user,
                    $request,
                    $authenticator,
                    'eleve_security' // firewall name in security.yaml
                );
            }
        
            else {

                $user = new Prof();

                $user->setRoles(["ROLE_PROF"]);

                // encode the plain password
                $user->setPassword(
                    $passwordEncoder->encodePassword(
                        $user,
                        $form->get('plainPassword')->getData()
                    )
                );

                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->persist($user);
                $entityManager->flush();

                // do anything else you need here, like send an email

                return $guardHandler->authenticateUserAndHandleSuccess(
                    $user,
                    $request,
                    $authenticator,
                    'prof_security' // firewall name in security.yaml
                );
            }
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }
}
