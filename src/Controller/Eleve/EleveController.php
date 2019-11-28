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

/**
 * @Route("/eleve")
 */
class EleveController extends AbstractController
{
   
    /**
     * Page d'acceuil, avec le planning de l'élève
     * @Route("/", name="home_eleve")
     */
    public function indexEleve() {

        return $this->render('eleve/calendrierEleve.html.twig', [
            'title' => 'Planning'
        ]);
    }

    /**
     * Affichage du profil public d'un prof
     * @Route("/voirProfilProf/{id}", name="voir_profil_prof")
     */
    public function voirProfilProf(Prof $prof) {
        $nbEtoiles = null;
        if ($notes = $prof->getNotes()){
            $noteMoyenne = round(array_sum($notes)/count($notes),1);
            $nbEtoiles = round($noteMoyenne);
        }
 
        return $this->render('prof/pagePubliqueProf.html.twig', [
            'prof' => $prof,
            'nbEtoiles' => $nbEtoiles,
        ]);
    }

    /**
     * @Route("/emettreAvis/{id}", name="emettre_avis")
     */
    public function emettreAvis(Prof $prof, Request $request) {
        $eleve = $this->getUser();

        $avis = new Avis();

        $form = $this->createForm(AvisType::class, $avis);

        $form->handleRequest($request);
  
        if ($form->isSubmitted() && $form->isValid()) {

            $avis->setProf($prof);
            $avis->setEleve($eleve);

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($avis);
            $prof->addNote($form->get('note')->getData());
            $noteMoyenne = round(array_sum($prof->getNotes())/count($prof->getNotes()),1);
            $prof->setNoteMoyenne($noteMoyenne);
            $entityManager->persist($prof);

            $entityManager->flush();

            return $this->redirectToRoute('home_eleve');
        }

        return $this->render('eleve/emettreAvis.html.twig', [
            'title' => 'Avis',
            'prof' => $prof,
            'form' => $form->createView(),
        ]);
    }

    /**
     * Affiche la liste des cours du prof (avec possibilité de les modifier)
     * @Route("/showDemandesEleve", name="show_demandes_eleve")
     */
    public function showDemandesEleve(Eleve $eleve) {

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
