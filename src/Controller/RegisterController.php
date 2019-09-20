<?php

namespace App\Controller;

use App\Entity\Prof;
use App\Entity\Eleve;
use App\Form\RegistrationType;
use App\Service\MailerService;
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

        if ($form->isSubmitted() && $form->isValid()) {

            if ( $form->get('isEleve')->getData() ) {

                $user = new Eleve();

                $user->setRoles(["ROLE_ELEVE"]);

                $route = $this->redirectToRoute('login_eleve');
            }

            else {

                $user = new Prof();

                $user->setConfirmationToken($this->generateToken());

                $user->setRoles(["ROLE_PROF"]);

                $route = $this->redirectToRoute('login_prof');
            }
    
            $user->setPictureFilename('default.jpg');

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

            $token = $tokenGenerator->generateToken();

            $user->setResetToken($token);
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($user);
            $entityManager->flush();


            $url = $this->generateUrl('app_confirm_account', array('token' => $token), UrlGeneratorInterface::ABSOLUTE_URL);

            $message = (new \Swift_Message('Forgot Password'))
            ->setFrom('arnaud6757@gmail.com')
            // ->setFrom('arnaud.straumann@free.fr')
            ->setTo($user->getEmail())
            ->setBody(
                "blablabla voici le token pour confirmer votre inscription : " . $url,
                'text/html'
            );

            $mailer->send($message);

            $this->addFlash('user-error', 'Votre inscription a été validée, vous allez recevoir un mail de confirmation pour activer votre compte');

            return $route;
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }

    /**
     * @Route("/confirmAccount/{token}", name="app_confirm_account")
     */
    public function confirmAccount(string $token)
    { 
        $entityManager = $this->getDoctrine()->getManager();

        $user = $entityManager->getRepository(Eleve::class)->findOneByResetToken($token);
        /* @var $user User */

        if ($user === null) {
            $this->addFlash('danger', 'Token Inconnu');
            return $this->redirectToRoute('home');
        }

        $user->setResetToken(null);
        $user->setAConfirme(true);
        $entityManager->flush();

        $this->addFlash('notice', 'Compte activé');

        return $this->redirectToRoute('login_eleve');

    }

}
