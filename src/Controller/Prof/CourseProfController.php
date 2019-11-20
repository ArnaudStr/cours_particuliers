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
     * @Route("/addProposeCours", name="add_propose_cours")
     * @Route("/editProposeCours/{idCours}", name="edit_propose_cours")
     * @ParamConverter("cours", options={"id" = "idCours"})
     */
    public function addEditCoursProf(Cours $cours = null, ObjectManager $manager, Request $request) {
       
        $prof = $this->getUser();   // On récupère le prof en session
        $modif = true;              // Modification d'un cours existant (par défaut)

        // si $cours est null (ajout d'un cours)
        if (!$cours){
            $modif = false;
            $cours = new Cours();
            $cours->setProf($prof);
            $title = 'Ajout d\'un cours';
        }

        else{
            $title = 'Modification de cours '.$cours;
        }

        // on prépare le formulaire
        $form = $this->createForm(CreationCoursType::class, $cours);

        // Récupère les données du formulaires 
        $form->handleRequest($request); 
               
        // Vérifie si le formulaire a été remplis et validé
        if($form->isSubmitted() && $form->isValid()) {

            // Sauvegarde l'objet avant de l'envoyer en base de données 
            $manager->persist($cours);

            // Execute la requête d'ajout/modif en base de données
            $manager->flush();
 
            // Une fois le cours ajouté en base de données, on redigire le prof vers la liste de ses cours
            return $this->redirectToRoute('show_liste_cours');
        }

        // Sinon c'est que le formulaire n'est pas valide ou n'a pas encore été remplis, on l'affiche donc
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
            'title' => 'Mes cours'
        ]);
    }

    /**
     * @Route("/demandesSeanceProf/{id}", name="demandes_seance_prof")
     */
    public function demandesSeanceProf(Seance $seance) {
      
        if ($seance->getProf() == $this->getUser()) {
            $demandesCours = $this->getDoctrine()
                ->getRepository(DemandeCours::class)
                ->findBySeance($seance);  

                return $this->render('prof/demandesSeance.html.twig', [
                    'title' => 'Demande d\'inscription à une séance',
                    'seance' => $seance,
                    'demandesCours' => $demandesCours,
                ]);
        }

    }

    /**
     * @Route("/validationSeanceProf/{id}/{valider}", name="validation_seances_prof")
     */
    public function validationSeanceProf(DemandeCours $demandeCours, int $valider) {

        $seance = $demandeCours->getSeance();
        if ($seance->getProf() == $this->getUser()) {

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
}
