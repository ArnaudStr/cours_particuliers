<?php

namespace App\Controller;

use App\Entity\Cours;
use App\Entity\Activite;
use App\Entity\Categorie;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class VisitorController extends AbstractController
{
     /**
     * @Route("/", name="search_course")
     */
    public function searchCourse()
    {
        return $this->render('course/searchCourse.html.twig', [
            'title' => 'Cours à Strasbourg',
            'transparent' => true
        ]);
    }

    /**
     * @Route("/listeCoursVisitorSearch", name="liste_cours_visitor_search")
     */
    public function listeCoursVisitorSearch(Request $request)
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
            'visitor' => true
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
     * Affichage des informations d'un cours
     * @Route("/displayCourseVisitor/{id}", name="display_course_visitor")
     */
    public function displayCourseVisitor(Cours $cours)
    {
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
}
