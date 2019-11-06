<?php

namespace App\Controller\Prof;

use App\Entity\Prof;
use App\Entity\Cours;
use App\Entity\Seance;
use App\Entity\DemandeCours;
use App\Form\CreationCoursType;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use App\Controller\Prof\ProfController;

/**
 * @Route("/prof")
 */
class CourseProfController extends ProfController
{
    /**
     * Création ou modification d'un cours
     * @Route("/addProposeCours/{idProf}", name="add_propose_cours")
     * @Route("/editProposeCours/{idProf}/{idCours}", name="edit_propose_cours")
     * @ParamConverter("prof", options={"id" = "idProf"})
     * @ParamConverter("cours", options={"id" = "idCours"})
     */
    public function addEditCoursProf(Prof $prof, Cours $cours = null, ObjectManager $manager, Request $request) {
       
        $this->setNbMsgNonLus();

        $modif = true;

        // si $creaneauCours est null (add)
        if (!$cours){
            $modif = false;
            $cours = new Cours();
            $cours->setProf($prof);
            $title = 'Ajout d\'un cours';
        }

        else{
            $title = 'Modification de cours '.$cours;
        }

        $form = $this->createForm(CreationCoursType::class, $cours);

        $form->handleRequest($request);
               
        if($form->isSubmitted() && $form->isValid()) {

            // // On met les creneaux dans le cours
            $manager->persist($cours);

            $manager->flush();
 
            return $this->redirectToRoute('home_prof');
            // return $this->redirectToRoute('showInfoseanceCours', ['id' => $seanceCours->getId()]);
        }
        return $this->render('course/addEditCreationCours.html.twig', ['form' => $form->createView(),
        'title' => $title, 'editMode' => $modif, 'cours' => $cours
        ]);
    }

    /**
     * Affiche la liste des cours du prof (avec possibilité de les modifier)
     * @Route("/showListeCours", name="show_liste_cours")
     */
    public function showListeCours() {
        return $this->render('prof/showListeCoursProf.html.twig', [
            'title' => 'Planning'
        ]);
    }

    /**
     * @Route("/demandesSeanceProf/{id}", name="demandes_seance_prof")
     */
    public function demandesSeanceProf(Seance $seance) {
      
        $this->setNbMsgNonLus();

        $demandesCours = $this->getDoctrine()
            ->getRepository(DemandeCours::class)
            ->findBySeance($seance);  

            return $this->render('prof/demandesSeance.html.twig', [
                'title' => 'Demande d\'inscription à une séance',
                'seance' => $seance,
                'demandesCours' => $demandesCours,
            ]);
    }

    /**
     * @Route("/validationSeanceProf/{id}/{valider}", name="validation_seances_prof")
     */
    public function validationSeanceProf(DemandeCours $demandeCours, int $valider) {

        $this->setNbMsgNonLus();

        $seance = $demandeCours->getSeance();
        if ($valider == 1) {
            $seance->setEleve($demandeCours->getEleve());
            $seance->setCours($demandeCours->getCours());

            // On ajoute l'élève au cours si il n'y est pas encore (pour pouvoir afficher la liste des élèves pour un cours)
            if (!$seance->getCours()->getEleves()->contains($demandeCours->getEleve())){
                $seance->getCours()->addEleve($demandeCours->getEleve());
            };
        }

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->remove($demandeCours);
        $entityManager->persist($seance);
        $entityManager->flush();

        return $this->redirectToRoute('demandes_seance_prof', ['id' => $seance->getId()]);
    }
}
