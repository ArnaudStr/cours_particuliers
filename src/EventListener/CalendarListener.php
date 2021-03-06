<?php

namespace App\EventListener;

use DateTime;
use CalendarBundle\Entity\Event;
use App\Repository\ProfRepository;
use App\Repository\SeanceRepository;
use CalendarBundle\Event\CalendarEvent;
use App\Repository\DemandeCoursRepository;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Security;


class CalendarListener
{
    private $demandeCoursRepository;
    private $seanceRepository;
    private $profRepository;
    private $router;
    private $user;

    public function __construct(
        SeanceRepository $seanceRepository,
        DemandeCoursRepository $demandeCoursRepository,
        ProfRepository $profRepository,
        UrlGeneratorInterface $router,
        Security $security
        // EntityManager $em
    ) {
        $this->seanceRepository = $seanceRepository;
        $this->demandeCoursRepository = $demandeCoursRepository;
        $this->profRepository = $profRepository;
        $this->router = $router;
        $this->user = $security->getUser();
        // $this->em = $em;
        
    }

    public function load(CalendarEvent $calendar): void
    {
        $start = $calendar->getStart();
        $end = $calendar->getEnd();
        $filters = $calendar->getFilters();

        if (array_key_exists('cours', $filters) && array_key_exists('eleve', $filters)) {

            // SEANCES DISPONIBLES POUR UN COURS
            $seancesS = $this->seanceRepository
                ->createQueryBuilder('seance')
                ->innerJoin('seance.prof', 'p')
                // ->innerJoin('seance.id', 's')
                ->where('seance.dateDebut BETWEEN :start and :end')
                ->andWhere('p.id = :idProf')
                // ->andWhere('s.id = :idSeance')
                // ->and('formation.dateDebut BETWEEN :start and :end')
                ->setParameter('start', $start->format('Y-m-d H:i:s'))
                ->setParameter('end', $end->format('Y-m-d H:i:s'))
                ->setParameter('idProf', $filters['prof'])
                ->getQuery()
                ->getResult()
            ;

            $demandes = $this->demandeCoursRepository
                ->createQueryBuilder('demandeSeance')
                // ->select('demandeSeance.seance')
                ->innerJoin('demandeSeance.eleve', 'e')
                ->innerJoin('demandeSeance.seance', 's')
                // ->innerJoin('s', 'demandeSeance.seance')
                // ->innerJoin('demandeSeance.seance', 's')
                ->where('s.dateDebut BETWEEN :start and :end')
                ->andWhere('e.id = :id')
                // ->andWhere(' = :id')
                ->setParameter('start', $start->format('Y-m-d H:i:s'))
                ->setParameter('end', $end->format('Y-m-d H:i:s'))
                ->setParameter('id', $filters['eleve'])
                ->getQuery()
                ->getResult();

            $seancesDemandes = [];
            $seances = [];
            
            foreach ($demandes as $demande) {
                array_push($seancesDemandes, $demande->getSeance());
            }

            foreach ($seancesS as $seance) {
                if (!in_array($seance, $seancesDemandes)) {
                    array_push($seances, $seance);
                }
            }

            // $seances = array_diff($seances, $seancesDemandes);

        }
        else if (array_key_exists('eleve', $filters)) {

            // SEANCES D'UN ELEVE
            $seances = $this->seanceRepository
                ->createQueryBuilder('seance')
                ->innerJoin('seance.eleve', 'e')
                ->where('seance.dateDebut BETWEEN :start and :end')
                ->andWhere('e.id = :id')
                ->setParameter('start', $start->format('Y-m-d H:i:s'))
                ->setParameter('end', $end->format('Y-m-d H:i:s'))
                ->setParameter('id', $filters['eleve'])
                ->getQuery()
                ->getResult();

            $demandes = $this->demandeCoursRepository
                ->createQueryBuilder('demandeSeance')
                ->innerJoin('demandeSeance.eleve', 'e')
                ->innerJoin('demandeSeance.seance', 's')
                ->where('s.dateDebut BETWEEN :start and :end')
                ->andWhere('e.id = :id')
                ->setParameter('start', $start->format('Y-m-d H:i:s'))
                ->setParameter('end', $end->format('Y-m-d H:i:s'))
                ->setParameter('id', $filters['eleve'])
                ->getQuery()
                ->getResult();
            
            foreach($demandes as $demande){
                array_push($seances, $demande->getSeance());
            }

        }

        else if (array_key_exists('prof', $filters)){

            // SEANCES D'UN PROF
            $seances = $this->seanceRepository
                ->createQueryBuilder('seance')
                ->innerJoin('seance.prof', 'p')
                ->where('seance.dateDebut BETWEEN :start and :end')
                ->andWhere('p.id = :id')
                // ->and('formation.dateDebut BETWEEN :start and :end')
                ->setParameter('start', $start->format('Y-m-d H:i:s'))
                ->setParameter('end', $end->format('Y-m-d H:i:s'))
                ->setParameter('id', $filters['prof'])
                ->getQuery()
                ->getResult()
                ;

        }

        // DISPONIBILITES (sous forme de créneaux) d'un prof
        else {
            $prof = $this->profRepository
                ->createQueryBuilder('p')
                ->andWhere('p.id = :id')
                ->setParameter('id', $filters['profDispos'])
                ->getQuery()
                ->getOneOrNullResult()
                ;

            $dispos = $prof->getDisponibilites();

            $seances = [];
            $seanceTmp = [];

            foreach($dispos as $jour=>$creneaux) {
                foreach($creneaux as $creneau) {
                    $dateDebut = new DateTime($jour.' this week');
                    $dateDebut->setTime($creneau[0], 0);

                    array_push($seanceTmp, $dateDebut);

                    $dateFin = new DateTime($jour.' this week');
                    $dateFin->setTime($creneau[1], 0);

                    array_push($seanceTmp, $dateFin);

                    array_push($seances, $seanceTmp);

                    $seanceTmp=[];
                }
            }
        }


        foreach ($seances as $seance) {
            $seanceEvent=null;

            if (!array_key_exists('profDispos', $filters)) {
                $dateFin = clone $seance->getDateDebut();
                $dateFin->add(new \DateInterval('PT1H'));
            }

            // Seances disponibles à l'inscription (par encore réservées)
            if (array_key_exists('eleve', $filters) && array_key_exists('cours', $filters) && !$seance->getEleve()) {

                // if (!in_array($seance, $filters['eleve']->getSeances())) {

                    $seanceEvent = new Event(
                        "S'inscrire",
                        $seance->getDateDebut(),
                        $dateFin 
                    );

                    $seanceEvent->setOptions([
                        'backgroundColor' => 'blue',
                        'textColor' => 'white',
                        'url'=> $this->router->generate('demande_inscription_seance', [
                                    'idSeance' => $seance->getId(),
                                    'idCours' => $filters['cours'],
                        ])
                    ]); 
                // }

            }

            // Seances d'un eleve
            else if (array_key_exists('eleve', $filters) && !array_key_exists('cours', $filters)) {

                if ( $seance->getEleve() ) {
                    $seanceEvent = new Event(
                        $seance->getCours()->getActivite().' avec '.$seance->getProf(),
                        $seance->getDateDebut(),
                        $dateFin // If the end date is null or not defined, a all day event is created.
                    );

                    $seanceEvent->setOptions([
                        'backgroundColor' => 'blue',
                        'textColor' => 'white',
                        'url' => $this->router->generate('emettre_avis', [
                                    'id' => $seance->getProf()->getId(),
                        ])
                    ]);
                }


                else {
                
                    $demandesCours = $this->demandeCoursRepository
                        ->createQueryBuilder('d')
                        ->andWhere('d.seance = :seance')
                        ->setParameter('seance', $seance)
                        ->getQuery()
                        ->getResult()
                    ;
                    $seanceEvent = new Event(
                        count($demandesCours).' demandes de cours',
                        $seance->getDateDebut(),
                        $dateFin // If the end date is null or not defined, a all day event is created.
                    );

                    $seanceEvent->setOptions([
                        'backgroundColor' => '#2196f3',
                        'textColor' => 'white',
                        'url' => $this->router->generate('demandes_seance_eleve', [
                            'id' => $seance->getId()
                        ])
                    ]);
                }
                
            }

            // Seances d'un prof
            else if (array_key_exists('prof', $filters) && !array_key_exists('cours', $filters)) {
                // COURS VALIDé
                if ( $seance->getEleve() ) {
                    $seanceEvent = new Event(
                        $seance->getCours()->getActivite().' avec '.$seance->getEleve(),
                        $seance->getDateDebut(),
                        $dateFin // If the end date is null or not defined, a all day event is created.
                    );

                    $seanceEvent->setOptions([
                        'backgroundColor' => 'blue',
                        
                        // 'borderColor' => '#',
                        'textColor' => 'white',
                    ]);
                }

                // CRENEAU AVEC DEMANDES DE COURS
                else if ( $demandesCours = $this->demandeCoursRepository
                            ->createQueryBuilder('d')
                            ->andWhere('d.seance = :seance')
                            ->setParameter('seance', $seance)
                            ->getQuery()
                            ->getResult() ) {
                
                    $seanceEvent = new Event(
                        count($demandesCours).' demandes de cours',
                        $seance->getDateDebut(),
                        $dateFin // If the end date is null or not defined, a all day event is created.
                    );

                    $seanceEvent->setOptions([
                        'backgroundColor' => '#2196f3',
                        'textColor' => 'white',
                        'url' => $this->router->generate('demandes_seance_prof', [
                            'id' => $seance->getId()
                        ])
                    ]);
                }

                // Séance disponible avec aucune demande d'élève
                else {
                    $seanceEvent = new Event(
                        'Séance libre',
                        $seance->getDateDebut(),
                        $dateFin // If the end date is null or not defined, a all day event is created.
                    );

                    $seanceEvent->setOptions([
                        'backgroundColor' => '#76818D',
                        'borderColor' => '#76818D',
                        'textColor' => 'white'
                    ]);
                }
            }

            // disponibilités d'un prof 
            else if (array_key_exists('profDispos', $filters)) {
                $seanceEvent = new Event(
                    'Creneau',
                    $seance[0],
                    $seance[1] // If the end date is null or not defined, a all day event is created.
                );

                $seanceEvent->setOptions([
                    'backgroundColor' => '##2196f3',
                    'borderColor' => '#',
                    'textColor' => 'white'
                ]);
            }

            if ($seanceEvent) {
                // finally, add the event to the CalendarEvent to fill the calendar
                $calendar->addEvent($seanceEvent);
            }
        }
    }
}