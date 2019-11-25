<?php

namespace App\Controller\Prof;

use App\Entity\Prof;
use App\Entity\Seance;
use App\Form\EditProfType;
use App\Form\ChangePasswordType;
use Rogervila\ArrayDiffMultidimensional;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use App\Controller\Prof\ProfController;
use Symfony\Component\HttpFoundation\File\File;

/**
 * @Route("/prof")
 */
class ProfileProfController extends ProfController
{
     /**
     * Profil du prof
     * @Route("/showProfileProf/", name="show_profile_prof")
     */
    public function showProfileProf()
    {
        $prof = $this->getUser();
        $nbEtoiles = null;
        if ($noteMoyenne = $prof->getNoteMoyenne()){
            $nbEtoiles = round($noteMoyenne);
        }

        // liste des cours avec la prochaine séance
        $allCoursEtProchaineSeance = [];

        // couple [cours, [prochainesSeance] ]
        $coursEtProchaineSeance = [];

        // prochainesSeancces d'un cours
        $prochainesSeancces = [];

        // On rempli le tableau [ [cours, [prochainesSeance]], [cours, [prochainesSeance]], ...] du prof
        foreach($prof->getCoursS() as $cours){
            $coursEtProchaineSeance['cours'] = $cours;

            foreach ($cours->getEleves() as $eleve) {
                $proSeance = $this->getDoctrine()
                    ->getRepository(Seance::class)
                    ->findNextSeanceEleve($eleve, $cours);               
                if ($proSeance) {
                    array_push($prochainesSeancces, $proSeance);
                }
            }

            if(!empty($prochainesSeancces)) {
                $coursEtProchaineSeance['seances'] = $prochainesSeancces;
            }
            else {
                $coursEtProchaineSeance['seances'] = [];
            }

            array_push($allCoursEtProchaineSeance, $coursEtProchaineSeance);

            $prochainesSeancces = [];
            $coursEtProchaineSeance = [];
        }

        $jours = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];

        $creneauxSemaine = [];

        foreach($jours as $jour){
            foreach($prof->getDisponibilites() as $jourC=>$creneauxJour){
                if ($jour == $jourC){
                    $creneauxSemaine[$jour] = $creneauxJour;
                }
            }
        }

        return $this->render('prof/showProfileProf.html.twig', [
            'allCoursEtProchaineSeance' => $allCoursEtProchaineSeance,
            'nbEtoiles' => $nbEtoiles,
            'creneauxSemaine' => $creneauxSemaine
        ]);
    }
   
    /**
     * Modification du profil du prof
     * @Route("/editProfileProf/", name="edit_profile_prof")
     */
    public function editProfileProf(Request $request)
    {       
        $prof = $this->getUser();
        
        $pictureBeforeForm = $prof->getPictureFilename();
        
        $form = $this->createForm(EditProfType::class, $prof);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            
            // Upload de la photo et inscription en BDD du nom de l'image
            if ( $pictureFilename = $form->get("pictureFilename")->getData() ) {
                if ($pictureFilename!='default_avatar.png'){
                    $this->delFile('pictures',$pictureBeforeForm);
                }
                    $filename = md5(uniqid()).'.'.$pictureFilename->guessExtension();
                    $pictureFilename->move($this->getParameter('pictures_directory'), $filename);
                    $prof->setPictureFilename($filename);
            }

            // else
            // {
            //     $prof->setPictureFilename($pictureBeforeForm);
            // }

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($prof);
            $entityManager->flush();

            return $this->redirectToRoute('show_profile_prof', [
                'id' => $prof->getId()
            ]);
        }

        return $this->render('prof/editProfileProf.html.twig', [
            'editForm' => $form->createView(),
        ]);
    }

    /**
     * @Route("/editPasswordProf/", name="edit_password_prof")
     */
    public function editPasswordProf(Request $request, ObjectManager $manager, UserPasswordEncoderInterface $passwordEncoder){

        $prof = $this->getUser();

        // $manager = $this->getDoctrine()->getManager();
        $form = $this->createForm(ChangePasswordType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
     
            // Si l'ancien mot de passe est bon
            if ($passwordEncoder->isPasswordValid($prof, $form->get('oldPassword')->getData())) {
                    
                $newpwd = $form->get('newPassword')['first']->getData();
        
                $newEncodedPassword = $passwordEncoder->encodePassword($prof, $newpwd);
                $prof->setPassword($newEncodedPassword);
        
                //$em->persist($user);
                $manager->flush();

                $this->addFlash('notice', 'Votre mot de passe à bien été changé !');
                die('changé');

                return $this->redirectToRoute('show_profile_prof', [
                    'id' => $prof->getId()
                ]);

            }

            else return $this->redirectToRoute('show_profile_prof', [
                'id' => $prof->getId()
            ]);
        }

        return $this->render('security/changePassword.html.twig', array(
                    'form' => $form->createView(),
        ));
    }


    /**
     * @Route("/editDisponibilitesProf/", name="edit_disponibilites_prof")
     */
    public function editDisponibilitesProf()
    {       
        return $this->render('prof/disposProf.html.twig', [
            'title' => 'Disponibilites prof'
        ]);
    }

     /**
     * @Route("/changementsDispos/", name="changements_dispos")
     */
    public function changementsDispos(ObjectManager $manager) {

        $prof = $this->getUser();

        $dispoAvantModif = $prof->getDisponibilites();
        $nouvellesDispos = json_decode($_COOKIE['dispos'], true);
        
        $prof->setDisponibilites($nouvellesDispos);
        
        $manager->persist($prof);
        
        // Nouvelles dispos
        $toAdd = ArrayDiffMultidimensional::compare($nouvellesDispos, $dispoAvantModif);

        $this->ajoutSeances(4, $toAdd, $manager, $prof);
        
        // Anciennes dispos
        $toDelete = ArrayDiffMultidimensional::compare($dispoAvantModif,$nouvellesDispos);

        $this->supprSeances($toDelete, $manager, $prof);

        $manager->flush();

        return $this->redirectToRoute('show_profile_prof', [
            'id' => $prof->getId()
        ]);
    }

    /**
     * @Route("/showReviewsProf/", name="show_reviews_prof")
     */
    public function showReviewsProf()
    {       
        $prof = $this->getUser();

        return $this->render('/reviews.html.twig', [
            'title' => 'Mes avis',
            'prof' => $prof
        ]);
    }
}
