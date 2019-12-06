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

        // $activites = $this->getDoctrine()->getRepository(Activite::class)->findAll();
        $activites = $this->getDoctrine()->getRepository(Activite::class)->findNames();

        $activitesJson = [];

        foreach($activites as $key=>$activite){
            foreach($activite as $key2=>$nom){
                array_push($activitesJson, $nom);
            }
        }
        dump($activitesJson);

        $json = json_encode($activitesJson);

        dd($json);

        // On récupère les 5 avis avec la meilleure note pour l'affichage du slider en bas de page
        $profs = $this->getDoctrine()
            ->getRepository(Prof::class)
            ->findBestFive();  

        // On récupère toutes les catégories pour l'affichage des activités par catégorie
        $categories = $this->getDoctrine()
            ->getRepository(Categorie::class)
            ->findAll();  

        return $this->render('home.html.twig', [
            'title' => 'StrasCours',
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
                               
        return $this->render('course/search.html.twig', [
            'activite' => $activite,
            'nbResultats' => $nbResultats,
            'title' => $activite
        ]);
    }

    /**
     * Affichage des informations d'un cours
     * @Route("/displayCourseVisitor/{id}", name="display_course_visitor")
     */
    public function displayCourseVisitor(Cours $cours)
    {
        return $this->render('course/displayCourse.html.twig', [
            'cours' => $cours,
            'title' => 'Cours de '.$cours,
        ]);
    }

    /**
     * @Route("/allActivitesCategories", name="all_activites_categories")
     */
    public function allActivitesCategories()
    {
        $categories = $this->getDoctrine()->getRepository(Categorie::class)->findAll();
        $activites = $this->getDoctrine()->getRepository(Activite::class)->findAll();

        $json = json_encode($activites);

            dd($json);
            
        return $json;

    }

}
