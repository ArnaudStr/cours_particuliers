<?php

namespace App\Controller;

use App\Entity\Prof;
use App\Entity\Eleve;
use App\Form\EditProfType;
use App\Form\EditEleveType;
use App\Service\FileUploader;
use App\Form\RegistrationType;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Form\ResetPasswordType;
use App\Service\MailerService;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;


class MemberController extends AbstractController
{

     /**
     * @Route("/register", name="app_register")
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

                $route = $this->redirectToRoute('app_login_eleve');
            }

            else {

                $user = new Prof();

                $user->setConfirmationToken($this->generateToken());

                $token = $user->getConfirmationToken();

                $email = $user->getEmail();

                $username = $user->getUsername();

                $mailerService->sendToken($mailer, $token, $email, $username, 'registration.html.twig');

                $this->addFlash('user-error', 'Votre inscription a été validée, vous aller recevoir un email de confirmation pour activer votre compte et pouvoir vous connecté');

                $user->setRoles(["ROLE_PROF"]);

                $route = $this->redirectToRoute('app_login_prof');
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


    /**
     * @Route("/confirm_account/{token}/{username}", name="confirm_account")
     */
    public function confirmAccount($token, $username): Response
    {
        $em = $this->getDoctrine()->getManager();
        $user = $em->getRepository(Prof::class)->findOneBy(['username' => $username]);
        $tokenExist = $user->getConfirmationToken();
        if($token === $tokenExist) {
           $user->setConfirmationToken(null);
           $user->setEnabled(true);
           $em->persist($user);
           $em->flush();
           return $this->redirectToRoute('app_login_prof');
        } else {
            return $this->render('registration/token-expire.html.twig');
        }
    }
    /**
     * @Route("/send_confirmation_token", name="send_confirmation_token")
     */
    public function sendConfirmationToken(Request $request, MailerService $mailerService, \Swift_Mailer $mailer): RedirectResponse
    {
        $em = $this->getDoctrine()->getManager();
        $email = $request->request->get('email');
        $user = $this->getDoctrine()->getRepository(Prof::class)->findOneBy(['email' => $email]);
        if($user === null) {
            $this->addFlash('not-user-exist', 'utilisateur non trouvé');
            return $this->redirectToRoute('app_register');
        }
        $user->setConfirmationToken($this->generateToken());
        $em->persist($user);
        $em->flush();
        $token = $user->getConfirmationToken();
        $email = $user->getEmail();
        $username = $user->getUsername();
        $mailerService->sendToken($mailer, $token, $email, $username, 'register.html.twig');
        return $this->redirectToRoute('app_login_prof');
    }

    private function generateToken()
    {
        return rtrim(strtr(base64_encode(random_bytes(32)), '+/', '-_'), '=');
    }

    /**
     * @Route("/eleve/edit/{id}", name="edit_eleve")
     */
    public function editEleve(Eleve $eleve, Request $request, UserPasswordEncoderInterface $passwordEncoder): Response
    {       

        // On récupere l'image avant le passage par le formulaire
        $pictureBeforeForm = $eleve->getPictureFilename();

        $form = $this->createForm(EditEleveType::class, $eleve);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            // Upload de la photo et inscription en BDD du nom de l'image
            if ( $pictureFilename = $form->get("pictureFilename")->getData() ) {
                $filename = md5(uniqid()).'.'.$pictureFilename->guessExtension();
                $pictureFilename->move($this->getParameter('pictures_directory'), $filename);
                $eleve->setPictureFilename($filename);
            }
            else
            {
                $eleve->setPictureFilename($pictureBeforeForm);
            }

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($eleve);
            $entityManager->flush();

            // dump($pictureFilename);
            // dump($filename);
            // dd($eleve);

            // do anything else you need here, like send an email

            return $this->redirectToRoute('show_profile_eleve');
        }

        return $this->render('member/editProfileEleve.html.twig', [
            'editForm' => $form->createView(),
            'picture' => $pictureBeforeForm
        ]);
    }

    /**
     * @Route("/prof/edit/{id}", name="edit_prof")
     */

    public function editProf(Prof $prof, Request $request, UserPasswordEncoderInterface $passwordEncoder): Response
    {       

        $pictureBeforeForm = $prof->getPictureFilename();
        
        $form = $this->createForm(EditProfType::class, $prof);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            // Upload de la photo et inscription en BDD du nom de l'image
            if ( $pictureFilename = $form->get("pictureFilename")->getData() ){
                $filename = md5(uniqid()).'.'.$pictureFilename->guessExtension();
                $pictureFilename->move($this->getParameter('pictures_directory'), $filename);
                $prof->setPictureFilename($filename);
            }
            else
            {
                $prof->setPictureFilename($pictureBeforeForm);
            }

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($prof);
            $entityManager->flush();

            // dump($pictureFilename);
            // dump($filename);
            dd($prof);

            // do anything else you need here, like send an email

            return $this->redirectToRoute('show_profile_prof');
        }

        return $this->render('member/editProfileProf.html.twig', [
            'editForm' => $form->createView(),
        ]);
    }

    /**
     * @Route("/prof/showProfile", name="show_profile_prof")
     */
    public function showProfileProf()
    {
        return $this->render('member/showProfile.html.twig', [
            'controller_name' => 'MemberController',
        ]);
    }

    /**
     * @Route("/eleve/showProfile", name="show_profile_eleve")
     */
    public function showProfileEleve()
    {
        return $this->render('member/showProfile.html.twig', [
            'controller_name' => 'MemberController',
        ]);
    }
    
 
}
