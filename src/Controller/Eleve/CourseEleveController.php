<?php

namespace App\Controller\Eleve;

use App\Entity\Prof;
use App\Entity\Cours;
use App\Entity\Eleve;
use App\Entity\Seance;
use App\Entity\Activite;
use App\Entity\Categorie;
use App\Entity\DemandeCours;
use App\Controller\Eleve\EleveController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

/**
 * @Route("/eleve")
 */
class CourseEleveController extends EleveController
{

    /**
     * Recherche de cours (avec searchbar)
     * @Route("/searchCourseEleve", name="search_course_eleve")
     */
    public function searchCourseEleve()
    {
        $this->setNbMsgNonLus();

        return $this->render('course/searchCourse.html.twig', [
            'title' => 'Cours à Strasbourg'
        ]);
    }

    /**
     * @Route("/eleve/listeCoursEleveSearch", name="liste_cours_eleve_search")
     */
    public function listeCoursEleveSearch(Request $request)
    {

        $search = $request->query->get('s');

        $nbResultats = 0;

        $categorie = $this->getDoctrine()
            ->getRepository(Categorie::class)
            ->findOneWithSearch(ucfirst($search));  
                    
        $activite = $this->getDoctrine()
            ->getRepository(Activite::class)
            ->findOneWithSearch(ucfirst($search));   

        if ($categorie){
            foreach($categorie->getActivites() as $activite)
            $nbResultats += count($activite->getCoursS());
        }
        else {
            $nbResultats = count($activite->getCoursS());
        }
            
        return $this->render('search/search.html.twig', [
            'categorie' => $categorie,
            'activite' => $activite,
            'recherche' => $search,
            'nbResultats' => $nbResultats,
        ]);
    }

    /**
     * Affichage des informations d'un cours
     * @Route("/displayCourseEleve/{id}", name="display_course_eleve")
     */
    public function displayCoursEleve(Cours $cours)
    {
        $this->setNbMsgNonLus();

        $nbEtoiles = null;
        if ($noteMoyenne = $cours->getProf()->getNoteMoyenne()){
            $nbEtoiles = round($noteMoyenne);
        }
        else $noteMoyenne = 'Pas encore noté';

        return $this->render('course/displayCourse.html.twig', [
            'cours' => $cours,
            'noteMoyenne' => $noteMoyenne,
            'nbEtoiles' => $nbEtoiles,
        ]);
    }

    /**
     * Inscription à un cours
     * @Route("/inscriptionCoursEleve/{idProf}/{idCours}", name="inscription_cours_eleve")
     * @ParamConverter("prof", options={"id" = "idProf"})
     * @ParamConverter("cours", options={"id" = "idCours"})
     */
    public function inscriptionCoursEleve(Prof $prof, Cours $cours) {
        $this->setNbMsgNonLus();
        
        return $this->render('course/inscriptionCours.html.twig', [
            'prof' => $prof,
            'cours' => $cours,
        ]);
    }
    
    /**
     * Envoie une demande d'inscription au prof pour le cours en question
     * @Route("/demandeInscriptionSeance/{idSeance}/{idEleve}/{idCours}", name="demande_inscription_seance")
     * @ParamConverter("seance", options={"id" = "idSeance"})
     * @ParamConverter("eleve", options={"id" = "idEleve"})
     * @ParamConverter("cours", options={"id" = "idCours"})
     */
    public function demandeInscriptionSeance(Seance $seance, Eleve $eleve, Cours $cours) {
        $this->setNbMsgNonLus();

        // Inscription élève au cours
        $demandeCours = new DemandeCours();

        $demandeCours->setSeance($seance);
        $demandeCours->setEleve($eleve);
        $demandeCours->setCours($cours);
        $demandeCours->setModeCours('test');

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($demandeCours);
        $entityManager->flush();
            return $this->render('eleve/calendrierEleve.html.twig', [
                'title' => 'Planning'
        ]);
    }
}