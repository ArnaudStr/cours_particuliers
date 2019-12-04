<?php

namespace App\Controller\Prof;

use App\Entity\Prof;
use App\Entity\Avis;
use App\Entity\Seance;
use App\Form\EditProfType;
use App\Form\ChangePasswordType;
use App\Controller\Prof\ProfController;
use Rogervila\ArrayDiffMultidimensional;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

/**
 * @Route("/prof")
 * @IsGranted("ROLE_PROF")
 */
class ProfileProfController extends ProfController
{
     /**
     * Profil du prof (page d'accueil)
     * @Route("/", name="home_prof")
     */
    public function showProfileProf()
    {
        $prof = $this->getUser();
     
        $noteMoyenne = $prof->getNoteMoyenne();

        if ($noteMoyenne){
            $nbEtoiles = round($noteMoyenne);
        }
        else $nbEtoiles = null;

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
            'noteMoyenne' => $noteMoyenne,
            'nbEtoiles' => $nbEtoiles,
            'creneauxSemaine' => $creneauxSemaine,
            'title' => 'Votre profil'
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

                if ($pictureBeforeForm!='default_avatar.png'){
                    $this->delFile('pictures',$pictureBeforeForm);
                }
                    $filename = md5(uniqid()).'.'.$pictureFilename->guessExtension();
                    $pictureFilename->move($this->getParameter('pictures_directory'), $filename);
                    $prof->setPictureFilename($filename);
            }

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($prof);
            $entityManager->flush();

            return $this->redirectToRoute('home_prof', [
                // 'id' => $prof->getId()
            ]);
        }

        return $this->render('prof/editProfileProf.html.twig', [
            'editForm' => $form->createView(),
            'title' => 'Modifiez votre profil'
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

            }

            return $this->redirectToRoute('home_prof', [
                'id' => $prof->getId()
            ]);
        }

        return $this->render('security/changePassword.html.twig', array(
            'form' => $form->createView(),
            'title' => 'Changement mot de passe'
        ));
    }


    /**
     * @Route("/editDisponibilitesProf/", name="edit_disponibilites_prof")
     */
    public function editDisponibilitesProf()
    {       
        return $this->render('prof/disposProf.html.twig', [
            'title' => 'Modifiez vos disponibilités'
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

        return $this->redirectToRoute('home_prof', [
            'id' => $prof->getId()
        ]);
    }

    /**
     * @Route("/showReviewsProf/", name="show_reviews_prof")
     */
    public function showReviewsProf()
    {       
        $prof = $this->getUser();

        return $this->render('prof/reviews.html.twig', [
            'title' => 'Mes avis',
            'prof' => $prof,
            'title' => 'Mes avis'
        ]);
    }
}
