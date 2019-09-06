<?php

namespace App\Controller\Prof;

use App\Entity\Activite;
use App\Form\ActivityType;
use App\Form\CategoryType;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * @Route("/prof")
 */
class ProfController extends AbstractController
{

    /**
     * @Route("/add_course", name="add_course")
     * @Route("/edit/course/{id}", name="edit_course")
     */
    public function addEditCourse(Activite $activite = null, ObjectManager $manager, Request $request) {
        if(!$activite) {
            $activite = new Activite();
            $title = "Ajout d'une activite";
        }
 
        else {
            $title = 'Modification de la activite '.$activite;
        }
 
        $form = $this->createForm(ActivityType::class, $activite);
        
        $form->handleRequest($request);
               
        if($form->isSubmitted() && $form->isValid()) {
            $manager->persist($activite);
            $manager->flush();
 
            return $this->redirectToRoute('home_prof');
            // return $this->redirectToRoute('showInfoActivite', ['id' => $activite->getId()]);
        }
        return $this->render('course/addEditCourse.html.twig', ['form' => $form->createView(),
            'title' => $title, 'editMode' => $activite->getId() != null, 'activite' => $activite
        ]);
    }
}
