<?php

namespace App\Controller;

use App\Entity\Prof;
use App\Entity\Eleve;
use App\Form\RegistrationType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Csrf\TokenGenerator\TokenGeneratorInterface;


class RegisterController extends AbstractController
{
     /**
     * @Route("/register", name="register")
     */
    public function register(Request $request, UserPasswordEncoderInterface $passwordEncoder, \Swift_Mailer $mailer,
    TokenGeneratorInterface $tokenGenerator): Response
    {       

        $form = $this->createForm(RegistrationType::class);
        $form->handleRequest($request);
        $token = $tokenGenerator->generateToken();

    
        if ($form->isSubmitted() && $form->isValid()) {

            if ( $form->get('isEleve')->getData() ) {
                $allEleves = $this->getDoctrine()
                ->getRepository(Eleve::class)
                ->findAll();
                $email = $form->get('email')->getData();

                // TEST SI L'EMAIL EXISTE DEJA
                foreach($allEleves as $eleve){ 
                    if($eleve->getEmail() == $email) {

                        if ($eleve->getAConfirme()) {
                            $this->addFlash('info','Vous êtes déjà register');
                            
                        }
                        else {

                            $url = $this->generateUrl('app_confirm_account', array('token' => $token), UrlGeneratorInterface::ABSOLUTE_URL);

                            $message = (new \Swift_Message('Forgot Password'))
                            ->setFrom('arnaud6757@gmail.com')
                            ->setTo($eleve->getEmail())
                            ->setBody(
                                "Voici le lien pour confirmer votre inscription : <a href='". $url ."'>Confirmer mon compte</a>",
                                'text/html'
                            );
                
                            $mailer->send($message);

                            $this->addFlash('info','Vous devez confirmer votre compte, un nouveau mail de confirmation vous a été envoyé');

                        }

                        return $this->redirectToRoute('login_eleve');
                    }
                }

                $user = new Eleve();

                $user->setToken($token);

                $user->setRoles(["ROLE_ELEVE"]);

                $route = $this->redirectToRoute('login_eleve');
            }

            else {

                $allProfs = $this->getDoctrine()
                ->getRepository(Prof::class)
                ->findAll();
                $email = $form->get('email')->getData();

                // TEST SI L'EMAIL EXISTE DEJA
                foreach($allProfs as $prof){ 
                    if($prof->getEmail() == $email) {

                        if ($prof->getAConfirme()) {
                            $this->addFlash('info','Vous êtes déjà register');
                            
                        }
                        else {

                            $url = $this->generateUrl('app_confirm_account', array('token' => $token), UrlGeneratorInterface::ABSOLUTE_URL);

                            $message = (new \Swift_Message('Lien pour valider votre inscription'))
                            ->setFrom('arnaud6757@gmail.com')
                            ->setTo($prof->getEmail())
                            ->setBody(
                                "Voici le lien pour confirmer votre inscription : <a href='". $url ."'>Confirmer mon compte</a>",
                                'text/html'
                            );
                
                            $mailer->send($message);

                            $this->addFlash('info','Vous devez confirmer votre compte, un nouveau mail de confirmation vous a été envoyé');

                        }

                        return $this->redirectToRoute('login_prof');
                    }
                }


                $user = new Prof();

                $user->setToken($token);

                $user->setRoles(["ROLE_PROF"]);

                $route = $this->redirectToRoute('login_prof');
            }
    
            $user->setPictureFilename('default_avatar.png');

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
            
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($user);
            $entityManager->flush();


            $url = $this->generateUrl('app_confirm_account', array('token' => $token), UrlGeneratorInterface::ABSOLUTE_URL);

            $message = (new \Swift_Message('Lien pour valider votre inscription'))
            ->setFrom('arnaud6757@gmail.com')
            ->setTo($user->getEmail())
            ->setBody(
                "Voici le lien pour confirmer votre inscription : <a href='". $url ."'>Confirmer mon compte</a>",
                'text/html'
            );

            $mailer->send($message);

            $this->addFlash('confirm', 'Vous avez reçu un email de validation de validation, veuillez confirmer votre compte');

            return $route;
        }

        // $this->get('session')->getFlashBag()->add('error', 'Une erreur est survenue.');

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }

    /**
     * @Route("/confirmAccount/{token}", name="app_confirm_account")
     */
    public function confirmAccount(string $token)
    {
        $isEleve = false;
        $entityManager = $this->getDoctrine()->getManager();

        if ($user = $entityManager->getRepository(Eleve::class)->findOneByToken($token)) {
            $isEleve = true;
        }
        else {
            $user = $entityManager->getRepository(Prof::class)->findOneByToken($token);
        }

        if ($user === null) {
            $this->addFlash('danger', 'Token Inconnu');
            return $this->redirectToRoute('register');
        }
        else {
            $this->addFlash('confirmation', 'Votre compté a été activé, veuillez vous connecter');
        }

        $user->setToken(null);
        $user->setAConfirme(true);
        $entityManager->flush();

        $this->addFlash('notice', 'Compte activé');


        if ($isEleve) {
            return $this->redirectToRoute('login_eleve');
        }
        else {
            return $this->redirectToRoute('login_prof');
        }
    }

}
