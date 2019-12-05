<?php

namespace App\Controller;

use App\Entity\Prof;
use App\Entity\Eleve;
use App\Form\RegistrationType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Csrf\TokenGenerator\TokenGeneratorInterface;

class RegisterController extends AbstractController
{
     /**
     * @Route("/register/{isEleve}", name="register")
     */
    public function register(Request $request, UserPasswordEncoderInterface $passwordEncoder, \Swift_Mailer $mailer,
    TokenGeneratorInterface $tokenGenerator, int $isEleve): Response
    {       
        $form = $this->createForm(RegistrationType::class);
        $form->handleRequest($request);
        $token = $tokenGenerator->generateToken();
    
        // Si le formulaire d'inscription est valide
        if ($form->isSubmitted() && $form->isValid()) {

            // Si la personne s'enregistre en tant qu'élève
            if ( $isEleve != 0 ) {

                $email = $form->get('email')->getData();

                $eleve = $this->getDoctrine()
                    ->getRepository(Eleve::class)
                    ->findOneByEmail($email);

                // Teste si l'élève existe déjà 
                if ($eleve) {

                    if ($eleve->getAConfirme()) {
                        $this->addFlash('alreadyExists','Vous êtes déjà enregistré, vous pouvez vous connecter');
                    }
                    else {

                        $url = $this->generateUrl('app_confirm_account', array('token' => $token), UrlGeneratorInterface::ABSOLUTE_URL);

                        $message = (new \Swift_Message('Validez votre inscription à Strascours'))
                        ->setFrom('arnaud6757@gmail.com')
                        ->setTo($eleve->getEmail())
                        ->setBody(
                            "Bonjour ".$eleve.".<br/>Voici le lien pour confirmer votre inscription : <a href='". $url ."'>Confirmer mon compte</a>",
                            'text/html'
                        );
            
                        $mailer->send($message);

                        $this->addFlash('confirm','Vous devez confirmer votre compte, un nouvel email de confirmation vous a été envoyé');
                    }

                    return $this->redirectToRoute('login_eleve');
                }

                $user = new Eleve();

                $user->setToken($token);

                $user->setRoles(["ROLE_ELEVE"]);

                $route = $this->redirectToRoute('login_eleve');
            }

            // Sinon il s'agit d'une inscription d'un prof
            else {

                $email = $form->get('email')->getData();

                $prof = $this->getDoctrine()
                    ->getRepository(Prof::class)
                    ->findOneByEmail($email);

                // Teste si le prof existe déjà
                if ($prof) {

                    if ($prof->getAConfirme()) {
                        $this->addFlash('alreadyExists','Vous êtes déjà enregistré, vous pouvez vous connecter');
                    }
                    else {

                        $url = $this->generateUrl('app_confirm_account', array('token' => $token), UrlGeneratorInterface::ABSOLUTE_URL);

                        $message = (new \Swift_Message('Validez votre inscription à Strascours'))
                        ->setFrom('arnaud6757@gmail.com')
                        ->setTo($prof->getEmail())
                        ->setBody(
                            "Bonjour ".$prof.".<br/>Voici le lien pour confirmer votre inscription : <a href='". $url ."'>Confirmer mon compte</a>",
                            'text/html'
                        );
            
                        $mailer->send($message);

                        $this->addFlash('confirm','Vous devez confirmer votre compte, un nouvel mail de confirmation vous a été envoyé');

                    }

                    return $this->redirectToRoute('login_prof');
                }

                $user = new Prof();

                $user->setToken($token);

                $user->setRoles(["ROLE_PROF"]);

                $route = $this->redirectToRoute('login_prof');
            }
    
            // On défini l'image par défaut
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
                mb_strtoupper($form->get('nom')->getData(), 'UTF-8')
            );

            $user->setPrenom(
                ucfirst(mb_strtolower($form->get('prenom')->getData(), 'UTF-8'))
            );
            
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($user);
            $entityManager->flush();

            $url = $this->generateUrl('app_confirm_account', array('token' => $token), UrlGeneratorInterface::ABSOLUTE_URL);

            $message = (new \Swift_Message('Validez votre inscription à Strascours'))
                ->setFrom('arnaud6757@gmail.com')
                ->setTo($user->getEmail())
                ->setBody(
                    "Bonjour ".$user.".<br/>Voici le lien pour confirmer votre inscription : <a href='". $url ."'>Confirmer mon compte</a>",
                    'text/html'
                );

            $mailer->send($message);

            $this->addFlash('confirm', 'Vous avez reçu un email de validation, veuillez confirmer votre compte');

            if ($isEleve != 0 && $isEleve != 1){
                $this->get('session')->set('cours', $isEleve);
            }
            return $route;
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form->createView(),
            'isEleve' => $isEleve
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
            $this->addFlash('confirm', 'Votre compté a été activé, veuillez vous connecter');
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
