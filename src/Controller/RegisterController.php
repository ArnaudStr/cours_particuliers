<?php

namespace App\Controller;

use App\Entity\Prof;
use App\Entity\Eleve;
use App\Form\RegistrationType;
use App\Service\MailerService;
use Symfony\Component\HttpFoundation\Request;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;


class RegisterController extends AbstractController
{

     /**
     * @Route("/register", name="register")
     */
    public function register(Request $request, UserPasswordEncoderInterface $passwordEncoder,  MailerService $mailerService, \Swift_Mailer $mailer): Response
    // public function register(Request $request, UserPasswordEncoderInterface $passwordEncoder): Response
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

                // $token = $user->getConfirmationToken();

                // $email = $user->getEmail();

                // $username = $user->getUsername();

                // $mailerService->sendToken($mailer, $token, $email, $username, 'registration.html.twig');

                // $this->addFlash('user-error', 'Votre inscription a été validée, vous aller recevoir un email de confirmation pour activer votre compte et pouvoir vous connecté');

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

    // /**
    //  * @Route("/confirm_account/{token}/{username}", name="confirm_account")
    //  */
    // public function confirmAccount($token, $username): Response
    // {
    //     $em = $this->getDoctrine()->getManager();
    //     $user = $em->getRepository(Prof::class)->findOneBy(['username' => $username]);
    //     $tokenExist = $user->getConfirmationToken();
    //     if($token === $tokenExist) {
    //        $user->setConfirmationToken(null);
    //        $user->setEnabled(true);
    //        $em->persist($user);
    //        $em->flush();
    //        return $this->redirectToRoute('login_prof');
    //     } else {
    //         return $this->render('registration/token-expire.html.twig');
    //     }
    // }
    // /**
    //  * @Route("/send_confirmation_token", name="send_confirmation_token")
    //  */
    // public function sendConfirmationToken(Request $request, MailerService $mailerService, \Swift_Mailer $mailer): RedirectResponse
    // {
    //     $em = $this->getDoctrine()->getManager();
    //     $email = $request->request->get('email');
    //     $user = $this->getDoctrine()->getRepository(Prof::class)->findOneBy(['email' => $email]);
    //     if($user === null) {
    //         $this->addFlash('not-user-exist', 'utilisateur non trouvé');
    //         return $this->redirectToRoute('register');
    //     }
    //     $user->setConfirmationToken($this->generateToken());
    //     $em->persist($user);
    //     $em->flush();
    //     $token = $user->getConfirmationToken();
    //     $email = $user->getEmail();
    //     $username = $user->getUsername();
    //     $mailerService->sendToken($mailer, $token, $email, $username, 'register.html.twig');
    //     return $this->redirectToRoute('login_prof');
    // }

    private function generateToken()
    {
        return rtrim(strtr(base64_encode(random_bytes(32)), '+/', '-_'), '=');
    }

}
