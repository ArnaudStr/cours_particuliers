<?php

namespace App\Controller;

use App\Entity\Activite;
use App\Entity\Categorie;
use Symfony\Component\HttpFoundation\Request;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class SearchController extends AbstractController {

    /**
     * @Route("/eleve/searchCoursEleve", name="search_cours_eleve")
     */
    public function search(Request $request)
    {

        $searchActivite = $request->query->get('s');
        // $searchLocalisation = $request->query->get('p');
        // dd($search);
        $categories = $this->getDoctrine()
            ->getRepository(Categorie::class)
            ->findAllWithSearch(ucfirst($searchActivite));  
                    
        $activites = $this->getDoctrine()
            ->getRepository(Activite::class)
            ->findAllWithSearch(ucfirst($searchActivite));

        // $profs = $this->getDoctrine()
        //     ->getRepository(Prof::class)
        //     ->findAllWithSearch(ucfirst($searchLocalisation));
        

        return $this->render('search/search.html.twig', [
            'categories' => $categories,
            'activites' => $activites,
            'recherche' => $searchActivite
            // 'profs' => $profs
        ]);
    }

}

