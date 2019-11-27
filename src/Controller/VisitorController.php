<?php

namespace App\Controller;

use App\Entity\Prof;
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
        $profs = $this->getDoctrine()
            ->getRepository(Prof::class)
            ->findBestFive();  

        $categories = $this->getDoctrine()
            ->getRepository(Categorie::class)
            ->findAll();  

        return $this->render('course/searchCourse.html.twig', [
            'title' => 'Cours à Strasbourg',
            'transparent' => true,
            'profs' => $profs,
            'categories' => $categories,
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

        else if ($activite){
            $nbResultats = count($activite->getCoursS());
        }
            
        return $this->render('course/search.html.twig', [
            'categorie' => $categorie,
            'activite' => $activite,
            'nbResultats' => $nbResultats,
            'title' => $search
        ]);
    }

    /**
     * @Route("/listeCoursVisitorActivite/{id}", name="liste_cours_visitor_activite")
     */
    public function listeCoursVisitorActivite(Activite $activite)
    {
        $nbResultats = count($activite->getCoursS());
        // dd($activite);
                               
        return $this->render('course/search.html.twig', [
            'activite' => $activite,
            'nbResultats' => $nbResultats,
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
