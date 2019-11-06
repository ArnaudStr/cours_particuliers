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

/**
 * @Route("/prof")
 */
class ProfileProfController extends ProfController
{
     /**
     * Profil du prof
     * @Route("/showProfileProf/{id}", name="show_profile_prof")
     */
    public function showProfileProf(Prof $prof)
    {
        // dd($prof->getDisponibilites());
        $this->setNbMsgNonLus();

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
        // dump($prof->getDisponibilites());

        foreach($jours as $jour){
            foreach($prof->getDisponibilites() as $jourC=>$creneauxJour){
                if ($jour == $jourC){
                    $creneauxSemaine[$jour] = $creneauxJour;
                }
            }
        }

        // dd($creneauxSemaine);

        return $this->render('prof/showProfileProf.html.twig', [
            'allCoursEtProchaineSeance' => $allCoursEtProchaineSeance,
            'nbEtoiles' => $nbEtoiles,
            'creneauxSemaine' => $creneauxSemaine
        ]);
    }
   
    public function delFile($dir, $del_file){
        $fsObject = new Filesystem();
        $current_dir_path = getcwd();
            $delTarget = $current_dir_path . "/assets/". $dir ."/". $del_file;
        
            if($del_file){
               return $fsObject->remove($delTarget);
            }
    }

    /**
     * Modification du profil du prof
     * @Route("/editProfileProf/{id}", name="edit_profile_prof")
     */
    public function editProfileProf(Prof $prof, Request $request, ObjectManager $manager)
    {       
        $this->setNbMsgNonLus();

        $pictureBeforeForm = $prof->getPictureFilename();
        
        $form = $this->createForm(EditProfType::class, $prof);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            // Upload de la photo et inscription en BDD du nom de l'image
            if ( $pictureFilename = $form->get("pictureFilename")->getData() ){
                $this->delFile('pictures',$pictureBeforeForm);
                $filename = md5(uniqid()).'.'.$pictureFilename->guessExtension();
                $pictureFilename->move($this->getParameter('pictures_directory'), $filename);
                $prof->setPictureFilename($filename);
            }
            else
            {
                $prof->setPictureFilename($pictureBeforeForm);
            }

            $manager->persist($prof);

            $manager->flush();

            return $this->redirectToRoute('show_profile_prof', [
                'id' => $prof->getId()
            ]);
        }

        return $this->render('prof/editProfileProf.html.twig', [
            'editForm' => $form->createView(),
            'prof' => $prof
        ]);
    }

    /**
     * @Route("/editPasswordProf/{id}", name="edit_password_prof")
     */
    public function editPasswordProf(Prof $prof, Request $request, ObjectManager $manager, UserPasswordEncoderInterface $passwordEncoder){

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
     * @Route("/editDisponibilitesProf/{id}", name="edit_disponibilites_prof")
     */
    public function editDisponibilitesProf(Prof $prof, Request $request, ObjectManager $manager)
    {       
        $this->setNbMsgNonLus();


        return $this->render('prof/disposProf.html.twig', [
            'title' => 'Disponibilites prof'
        ]);

    }

     /**
     * @Route("/changementsDispos/{id}", name="changements_dispos")
     */
    public function changementsDispos(Prof $prof,  ObjectManager $manager) {

        $this->setNbMsgNonLus();

        // dd(json_decode($_COOKIE['dispos'], true));

        $dispoAvantModif = $prof->getDisponibilites();
        $nouvellesDispos = json_decode($_COOKIE['dispos'], true);
        
        $prof->setDisponibilites($nouvellesDispos);

        // dd($prof->getDisponibilites());
        
        $manager->persist($prof);
        
        // Nouvelles dispos
        $toAdd = ArrayDiffMultidimensional::compare($nouvellesDispos, $dispoAvantModif);

        $this->ajoutSeances(4, $toAdd, $manager, $prof);
        
        // Anciennes dispos
        $toDelete = ArrayDiffMultidimensional::compare($dispoAvantModif,$nouvellesDispos);

        // dump($toDelete);
        $this->supprSeances($toDelete, $manager, $prof);

        $manager->flush();

        return $this->redirectToRoute('show_profile_prof', [
            'id' => $prof->getId()
        ]);
    }
}
