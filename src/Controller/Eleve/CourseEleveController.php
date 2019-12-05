<?php

namespace App\Controller\Eleve;

use App\Entity\Cours;
use App\Entity\Seance;
use App\Entity\Activite;
use App\Entity\Categorie;
use App\Entity\DemandeCours;
use App\Controller\Eleve\EleveController;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

/**
 * @Route("/eleve")
 * @IsGranted("ROLE_ELEVE")
 */
class CourseEleveController extends EleveController
{

    /**
     * @Route("/demandesSeanceEleve/{id}", name="demandes_seance_eleve")
     */
    public function demandesSeanceEleve(Seance $seance) {
      
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
     * @Route("/cancelDemandeEleve/{id}", name="cancel_demande_eleve")
     */
    public function cancelDemandeEleve(DemandeCours $demande, ObjectManager $manager) {
      
        $manager->remove($demande);
        $manager->flush();

        return $this->redirectToRoute('home_eleve', [
        ]);
    }

    /**
     * Recherche de cours (avec searchbar)
     * @Route("/searchCourseEleve", name="search_course_eleve")
     */
    public function searchCourseEleve()
    {
        $categories = $this->getDoctrine()
            ->getRepository(Categorie::class)
            ->findAll();  

        return $this->render('home.html.twig', [
            'title' => 'StrasCours',
            'transparent' => true,
            'categories' => $categories,
        ]);
    }

    /**
     * @Route("/listeCoursEleveSearch", name="liste_cours_eleve_search")
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
     * Affichage des informations d'un cours
     * @Route("/displayCourseEleve/{id}", name="display_course_eleve")
     */
    public function displayCoursEleve(Cours $cours)
    {
        $noteMoyenne = $cours->getProf()->getNoteMoyenne();
        if ($noteMoyenne){
            $nbEtoiles = round($noteMoyenne);
        }
        else {
            $nbEtoiles = null;
        }
        
        return $this->render('course/displayCourse.html.twig', [
            'title' => 'Cours de '.$cours,
            'cours' => $cours,
            'nbEtoiles' => $nbEtoiles,
        ]);
    }

    // /**
    //  * Inscription à un cours
    //  * @Route("/inscriptionCoursEleve/{idProf}/{idCours}", name="inscription_cours_eleve")
    //  * @ParamConverter("prof", options={"id" = "idProf"})
    //  * @ParamConverter("cours", options={"id" = "idCours"})
    //  */
    // public function inscriptionCoursEleve(Prof $prof, Cours $cours) {
        
    //     return $this->render('course/inscriptionCours.html.twig', [
    //         'prof' => $prof,
    //         'cours' => $cours,
    //     ]);
    // }
    
    /**
     * Envoie une demande d'inscription au prof pour le cours en question
     * @Route("/demandeInscriptionSeance/{idSeance}/{idCours}", name="demande_inscription_seance")
     * @ParamConverter("seance", options={"id" = "idSeance"})
     * @ParamConverter("cours", options={"id" = "idCours"})
     */
    public function demandeInscriptionSeance(Seance $seance, Cours $cours, \Swift_Mailer $mailer) {

        $eleve = $this->getUser();
        // Inscription élève au cours
        $demandeCours = new DemandeCours();

        $demandeCours->setSeance($seance);
        $demandeCours->setEleve($eleve);
        $demandeCours->setCours($cours);

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($demandeCours);
        $entityManager->flush();

        $this->addFlash('demande', 'Votre demande d\'inscription à la séance a été enregistré et est en attente de réponse. Vous pouvez la retrouver dans votre planning.');

        $url = $this->generateUrl('demandes_seance_prof', array('id' => $seance->getId()), UrlGeneratorInterface::ABSOLUTE_URL);

        $message = (new \Swift_Message('Nouvelle demande d\'inscription'))
            ->setFrom('arnaud6757@gmail.com')
            ->setTo($seance->getProf()->getEmail())
            ->setBody(
                "Bonjour ".$cours->getProf().".<br/>Vous avez reçu une demande d'inscription à un cours de ".$cours." pour le ".$seance->getDateDebut()->format('d-m-Y H:i')." : <a href='". $url ."'>Cliquez ici</a>",
                'text/html'
            );

        $mailer->send($message);

        return $this->redirectToRoute('display_course_eleve', [
            'id' => $cours->getId()
        ]);
    }


    /**
     * @Route("/listeCoursEleveActivite/{id}", name="liste_cours_eleve_activite")
     */
    public function listeCoursEleveActivite(Activite $activite)
    {
        $nbResultats = count($activite->getCoursS());
        // dd($activite);
                               
        return $this->render('course/search.html.twig', [
            'activite' => $activite,
            'nbResultats' => $nbResultats,
            'title' => $activite
        ]);
    }
}
