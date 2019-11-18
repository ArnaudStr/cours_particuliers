<?php

namespace App\Controller\Eleve;

use App\Entity\Eleve;
use App\Entity\Seance;
use App\Form\EditEleveType;
use App\Form\ChangePasswordType;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use App\Controller\Eleve\EleveController;

/**
 * @Route("/eleve")
 */
class ProfileEleveController extends EleveController
{
    /**
     * Profil de l'élève
     * @Route("/showProfileEleve/", name="show_profile_eleve")
     */
    public function showProfileEleve() {

        $eleve = $this->getUser();
        // liste des cours avec la prochaine séance
        $allCoursEtProchaineSeance = [];

        // couple [cours, prochainesSeance ]
        $coursEtProchaineSeance = [];

        foreach($eleve->getCours() as $cours){
            $coursEtProchaineSeance['cours'] = $cours;

            $proSeance = $this->getDoctrine()
                ->getRepository(Seance::class)
                ->findNextSeanceEleve($eleve, $cours); 

            if ($proSeance) {
                $coursEtProchaineSeance['seance'] = $proSeance;
            }
            else {
                $coursEtProchaineSeance['seance'] = null;
            }

            array_push($allCoursEtProchaineSeance, $coursEtProchaineSeance);

            $coursEtProchaineSeance = [];
        }

        return $this->render('eleve/showProfileEleve.html.twig', [
            'prochainesSeances' => $allCoursEtProchaineSeance
        ]);
    }   

    /**
     * Modification des informations
     * @Route("/editEleve/", name="edit_eleve")
     */
    public function editEleve(Request $request) {

        $eleve = $this->getUser();
        // On récupere l'image avant le passage par le formulaire
        $pictureBeforeForm = $eleve->getPictureFilename();

        $form = $this->createForm(EditEleveType::class, $eleve);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            // Upload de la photo et inscription en BDD du nom de l'image, (si il y a eu une image dans le formulaire)
            if ( $pictureFilename = $form->get("pictureFilename")->getData() ) {
                $filename = md5(uniqid()).'.'.$pictureFilename->guessExtension();
                $pictureFilename->move($this->getParameter('pictures_directory'), $filename);
                $eleve->setPictureFilename($filename);
            }
            else {
                $eleve->setPictureFilename($pictureBeforeForm);
            }

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($eleve);
            $entityManager->flush();

            return $this->redirectToRoute('show_profile_eleve', ['id'=>$eleve->getID()]);
        }

        return $this->render('eleve/editProfileEleve.html.twig', [
            'editForm' => $form->createView(),
            // 'picture' => $pictureBeforeForm
        ]);
    }


    /**
     * Changement de password
     * @Route("/editPasswordEleve/", name="edit_password_eleve")
     */
    public function editPasswordEleve(Request $request, ObjectManager $manager, UserPasswordEncoderInterface $passwordEncoder){

        $eleve = $this->getUser();
        // $manager = $this->getDoctrine()->getManager();
        $form = $this->createForm(ChangePasswordType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
     
            // Si l'ancien mot de passe est bon
            if ($passwordEncoder->isPasswordValid($eleve, $form->get('oldPassword')->getData())) {
                    
                $newpwd = $form->get('newPassword')['first']->getData();
        
                $newEncodedPassword = $passwordEncoder->encodePassword($eleve, $newpwd);
                $eleve->setPassword($newEncodedPassword);
        
                //$em->persist($user);
                $manager->flush();

                $this->addFlash('changePasswordOk', 'Votre mot de passe à bien été changé !');

                return $this->redirectToRoute('show_profile_eleve', [
                    'id' => $eleve->getId()
                ]);

            }

            else {

                $this->addFlash('changePasswordError', 'L`\ancien mot de passe n`\est pas valide');

                return $this->redirectToRoute('edit_password_eleve', [
                    'id' => $eleve->getId()
                ]);
            } 
        }

        return $this->render('security/changePassword.html.twig', array(
                    'form' => $form->createView(),
        ));
    }
}
