<?php

namespace App\Controller;

use App\Entity\Prof;
use App\Entity\Eleve;
use App\Form\EditProfType;
use App\Form\EditEleveType;
use App\Service\FileUploader;
use App\Form\RegistrationType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Constraints\File;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;


class MemberController extends AbstractController
{

     /**
     * @Route("/register", name="app_register")
     */
    // public function register(Request $request, UserPasswordEncoderInterface $passwordEncoder, GuardAuthenticatorHandler $guardHandler, ProfAuthenticator $authenticator): Response
    public function register(Request $request, UserPasswordEncoderInterface $passwordEncoder): Response
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
    /**
     * @Route("/eleve/edit/{id}", name="edit_eleve")
     */

    public function editEleve(Eleve $eleve, Request $request, UserPasswordEncoderInterface $passwordEncoder, FileUploader $fileUploader): Response
    {       

        $form = $this->createForm(EditEleveType::class, $eleve);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

  
            $pictureFile = $form['pictureFilename']->getData();

            // this condition is needed because the 'brochure' field is not required
            // so the PDF file must be processed only when a file is uploaded
            if ($pictureFile) {
                $pictureFileName = $fileUploader->upload($pictureFile);
                $eleve->setPictureFilename($pictureFileName);
                new File($this->getParameter('pictures_directory').'/'.$eleve->getPictureFilename());

            }

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($eleve);
            $entityManager->flush();

            // do anything else you need here, like send an email

            return $this->redirectToRoute('show_profile_eleve');
        }

        return $this->render('member/editProfileEleve.html.twig', [
            'editForm' => $form->createView(),
        ]);
    }

    /**
     * @Route("/prof/edit/{id}", name="edit_prof")
     */

    public function editProf(Prof $prof, Request $request, UserPasswordEncoderInterface $passwordEncoder, FileUploader $fileUploader): Response
    {       

        $form = $this->createForm(EditProfType::class, $prof);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $pictureFile = $form['pictureFilename']->getData();
            $pictureFileName = $fileUploader->upload($pictureFile);


            // this condition is needed because the 'brochure' field is not required
            // so the PDF file must be processed only when a file is uploaded
            if ($pictureFile && $pictureFileName) {
                $prof->setPictureFilename($pictureFileName);
                dump($pictureFileName);
                dump($prof);
                new File($this->getParameter('pictures_directory').'/'.$prof->getPictureFilename());

            }
                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->persist($prof);
                $entityManager->flush();
    
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