<?php

namespace App\Controller\Eleve;


use App\Entity\Seance;
use App\Form\EditEleveType;
use App\Form\ChangePasswordType;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use App\Controller\Eleve\EleveController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;


/**
 * @Route("/eleve")
 * @IsGranted("ROLE_ELEVE")
 */
class ProfileEleveController extends EleveController
{
    /**
     * Profil de l'élève (page d'accueil)
     * @Route("/", name="home_eleve")
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

            $coursEtProchaineSeance['nextSeance'] = $proSeance;

            $lastSeance = $this->getDoctrine()
                ->getRepository(Seance::class)
                ->findLastSeanceEleve($eleve, $cours); 

            $coursEtProchaineSeance['lastSeance'] = $lastSeance;

            array_push($allCoursEtProchaineSeance, $coursEtProchaineSeance);

            $coursEtProchaineSeance = [];
        }

        // dd($allCoursEtProchaineSeance);

        return $this->render('eleve/showProfileEleve.html.twig', [
            'allCoursEtProchaineSeance' => $allCoursEtProchaineSeance,
            'title' => 'Votre profil'
        ]);
    }   

    /**
     * Modification des informations
     * @Route("/editProfileEleve/", name="edit_profile_eleve")
     */
    public function editProfileEleve(Request $request) {

        $eleve = $this->getUser();
        // On récupere l'image avant le passage par le formulaire
        $pictureBeforeForm = $eleve->getPictureFilename();
        dd($pictureBeforeForm);

        $form = $this->createForm(EditEleveType::class, $eleve);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            // Upload de la photo et inscription en BDD du nom de l'image, (si il y a eu une image dans le formulaire)
            if ( $pictureFilename = $form->get("pictureFilename")->getData() ) {
                if ($pictureBeforeForm!='default_avatar.png'){
                    $this->delFile('pictures',$pictureBeforeForm);
                }
                $filename = md5(uniqid()).'.'.$pictureFilename->guessExtension();
                $pictureFilename->move($this->getParameter('pictures_directory'), $filename);
                $eleve->setPictureFilename($filename);
            }

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($eleve);
            $entityManager->flush();

            return $this->redirectToRoute('home_eleve', ['id'=>$eleve->getID()]);
        }

        return $this->render('eleve/editProfileEleve.html.twig', [
            'editForm' => $form->createView(),
            'title' => 'Modifez votre profil'
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

                return $this->redirectToRoute('home_eleve', [
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
                    'title' => 'Changement mot de passe'
        ));
    }
}
