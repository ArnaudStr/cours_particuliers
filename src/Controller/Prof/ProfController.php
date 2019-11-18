<?php

namespace App\Controller\Prof;

use DateTime;
use DateTimeZone;
use App\Entity\Prof;
use App\Entity\Seance;
use App\Entity\Message;
// use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Filesystem;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * @Route("/prof")
 */
class ProfController extends AbstractController
{    
    /**
     * Page d'acceuil (planning du prof)
     * @Route("/", name="home_prof")
     */
    public function indexProf()
    {

        return $this->render('prof/calendrierProf.html.twig', [
            'title' => 'Planning'
        ]);
    }

    // Ajout des séances en fonctions des disponibilités du prof, avec un nombre de semaine variable
    public function ajoutSeances($nbSemaines, $disponibilites, ObjectManager $manager, Prof $prof){
        foreach($disponibilites as $jour=>$creneaux) {
            foreach($creneaux as $creneau) {
                for ($i=0; $i<$nbSemaines; $i++) {
                    for($heure=$creneau[0]; $heure<$creneau[1]; $heure++) {

                        $seance = new Seance();
                        $seance->setProf($prof);
                        $dateDebut = new DateTime('now',new DateTimeZone('Europe/Paris'));
                        $dateDebut->modify('next '.$jour.' +'.($i*7).' days');
                        $dateDebut->setTime($heure, 0);
                        
                        $seance->setDateDebut($dateDebut);

                        $manager->persist($seance);
                    }
                }
            }
        }
    }

    // Supprime les séances des disponibilités qui ont été changeés
    public function supprSeances($disponibilites, ObjectManager $manager, Prof $prof){
        foreach($disponibilites as $jour=>$creneaux) {
            foreach($creneaux as $creneau) {

                dump($creneau);
                $seances = $this->getDoctrine()
                ->getRepository(Seance::class)
                ->findToDelete($jour,$creneau[0],$creneau[1]-1, $prof);

                dump($seances);

                foreach($seances as $seance){
                    $manager->remove($seance);

                    $manager->flush();
                }
            }
        }
    }

    public function delFile($dir, $del_file){
        $fsObject = new Filesystem();
        $current_dir_path = getcwd();
            $delTarget = $current_dir_path . "/assets/". $dir ."/". $del_file;
        
            if($del_file){
               return $fsObject->remove($delTarget);
            }
    }
}
