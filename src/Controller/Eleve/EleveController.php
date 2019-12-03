<?php

namespace App\Controller\Eleve;

use App\Entity\Avis;
use App\Entity\Prof;
use App\Entity\Eleve;
use App\Form\AvisType;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @Route("/eleve")
 */
class EleveController extends AbstractController
{
   
    /**
     * Planning de l'élève
     * @Route("/planningEleve", name="planning_eleve")
     * @IsGranted("ROLE_ELEVE")
     */
    public function planningEleve() {

        return $this->render('eleve/calendrierEleve.html.twig', [
            'title' => 'Planning'
        ]);
    }

    /**
     * Affichage du profil public d'un prof
     * @Route("/voirProfilProf/{id}", name="voir_profil_prof")
     * @IsGranted("ROLE_ELEVE")
     */
    public function voirProfilProf(Prof $prof) {
        $nbEtoiles = null;
    
        if ($prof->getNoteMoyenne()){
            $nbEtoiles = round($prof->getNoteMoyenne());
        }
 
        return $this->render('prof/pagePubliqueProf.html.twig', [
            'prof' => $prof,
            'nbEtoiles' => $nbEtoiles,
            'title' => ''.$prof
        ]);
    }

    /**
     * @Route("/emettreAvis/{id}", name="emettre_avis")
     * @IsGranted("ROLE_ELEVE")
     */
    public function emettreAvis(Prof $prof, Request $request) {
        $eleve = $this->getUser();

        $avis = $this->getDoctrine()
            ->getRepository(Avis::class)
            ->findAvis($prof, $eleve);

        if($avis){
            $this->addFlash('avis', 'Vous avez déjà envoyé un avis à '.$prof);
            
            return $this->redirectToRoute('planning_eleve');
        }

        else {
            $avis = new Avis();

            $form = $this->createForm(AvisType::class, $avis);

            $form->handleRequest($request);
    
            if ($form->isSubmitted() && $form->isValid()) {

                $avis->setProf($prof);
                $avis->setEleve($eleve);

                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->persist($avis);

                $entityManager->flush();

                $noteMoyenne = round($this->getDoctrine()
                    ->getRepository(Avis::class)
                    ->findNoteMoyenne($prof),1);

                    // dd($noteMoyenne);

                $prof->setNoteMoyenne($noteMoyenne);

                $entityManager->persist($prof);

                $entityManager->flush();

                return $this->redirectToRoute('planning_eleve');
            }
        }

        return $this->render('eleve/emettreAvis.html.twig', [
            'title' => 'Avis sur '.$prof,
            'prof' => $prof,
            'form' => $form->createView(),
        ]);
    }

    /**
     * Affiche la liste des demandes en attente
     * @Route("/showDemandesEleve", name="show_demandes_eleve")
     * @IsGranted("ROLE_ELEVE")
     */
    public function showDemandesEleve() {

        $eleve = $this->getUser();

        return $this->render('eleve/demandesEleve.html.twig', [
            'title' => 'Mes demandes d\'inscription',
            'eleve' => $eleve
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

}
