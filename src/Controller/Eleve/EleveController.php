<?php

namespace App\Controller\Eleve;

use App\Entity\Eleve;
use App\Entity\Avis;
use App\Entity\Prof;
use App\Form\AvisType;
use App\Entity\Message;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

/**
 * @Route("/eleve")
 */
class EleveController extends AbstractController
{
   
    /**
     * Page d'acceuil, avec le planning de l'élève
     * @Route("/", name="home_eleve")
     */
    public function indexEleve() {

        return $this->render('eleve/calendrierEleve.html.twig', [
            'title' => 'Planning'
        ]);
    }

    /**
     * Affichage du profil public d'un prof
     * @Route("/voirProfilProf/{id}", name="voir_profil_prof")
     */
    public function voirProfilProf(Prof $prof)
    {
        $nbEtoiles = null;
        if ($notes = $prof->getNotes()){
            $noteMoyenne = round(array_sum($notes)/count($notes),1);
            $nbEtoiles = round($noteMoyenne);
        }
 
        return $this->render('prof/pagePubliqueProf.html.twig', [
            'prof' => $prof,
            'nbEtoiles' => $nbEtoiles,
        ]);
 
    }

    /**
     * @Route("/emettreAvis/{idEleve}/{idProf}", name="emettre_avis")
     * @ParamConverter("eleve", options={"id" = "idEleve"})
     * @ParamConverter("prof", options={"id" = "idProf"})
     */
    public function emettreAvis(Eleve $eleve, Prof $prof, Request $request) {
        $avis = new Avis();

        $form = $this->createForm(AvisType::class, $avis);

        $form->handleRequest($request);
  
        if ($form->isSubmitted() && $form->isValid()) {

            $avis->setProf($prof);
            $avis->setEleve($eleve);

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($avis);
            $prof->addNote($form->get('note')->getData());
            $noteMoyenne = round(array_sum($prof->getNotes())/count($prof->getNotes()),1);
            $prof->setNoteMoyenne($noteMoyenne);
            $entityManager->persist($prof);

            $entityManager->flush();

            return $this->redirectToRoute('home_eleve');
        }

        return $this->render('eleve/emettreAvis.html.twig', [
            'title' => 'Avis',
            'form' => $form->createView(),
        ]);
    }
}
