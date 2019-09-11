<?php

namespace App\Controller;

use App\Entity\Activite;
use App\Entity\Categorie;
use Symfony\Component\HttpFoundation\Request;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class SearchController extends AbstractController {

    /**
     * @Route("/eleve/search/result", name="search_result_eleve")
     */
    public function search(Request $request)
    {

        $search = $request->query->get('s');
        // dd($search);
        $categories = $this->getDoctrine()
            ->getRepository(Categorie::class)
            ->findAllWithSearch(ucfirst($search));  
                    
        $activites = $this->getDoctrine()
            ->getRepository(Activite::class)
            ->findAllWithSearch(ucfirst($search));
        

        return $this->render('search/search.html.twig', [
            'categories' => $categories,
            'activites' => $activites
        ]);
    }

}

