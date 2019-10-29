<?php

namespace App\EventListener;

use App\Repository\DemandeCoursRepository;
use App\Repository\ProfRepository;
use App\Repository\SeanceRepository;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use CalendarBundle\Entity\Event;
use CalendarBundle\Event\CalendarEvent;
use DateTime;

class CalendarListener
{
    private $demandeCoursRepository;
    private $seanceRepository;
    private $profRepository;
    private $router;

    public function __construct(
        SeanceRepository $seanceRepository,
        DemandeCoursRepository $demandeCoursRepository,
        ProfRepository $profRepository,
        UrlGeneratorInterface $router
    ) {
        $this->seanceRepository = $seanceRepository;
        $this->demandeCoursRepository = $demandeCoursRepository;
        $this->profRepository = $profRepository;
        $this->router = $router;
    }

    public function load(CalendarEvent $calendar): void
    {
        $start = $calendar->getStart();
        $end = $calendar->getEnd();
        $filters = $calendar->getFilters();

        if (array_key_exists('cours', $filters)) {

            // SEANCES DISPONIBLES POUR UN COURS
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
        else if (array_key_exists('eleve', $filters)) {

            // SEANCES D'UN ELEVE
            $seances = $this->seanceRepository
            ->createQueryBuilder('seance')
            ->innerJoin('seance.eleve', 'e')
            ->where('seance.dateDebut BETWEEN :start and :end')
            ->andWhere('e.id = :id')
            // ->orWhere('seance.dateFin BETWEEN :start and :end')
            // ->orWhere(':end BETWEEN seance.dateDebut and seance.dateFin')
            ->setParameter('start', $start->format('Y-m-d H:i:s'))
            ->setParameter('end', $end->format('Y-m-d H:i:s'))
            ->setParameter('id', $filters['eleve'])
            ->getQuery()
            ->getResult();

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
            $seance = [];

            foreach($dispos as $jour=>$creneaux) {
                foreach($creneaux as $creneau) {
                    $dateDebut = new DateTime($jour.' this week');
                    $dateDebut->setTime($creneau[0], 0);

                    array_push($seance, $dateDebut);

                    $dateFin = new DateTime($jour.' this week');
                    $dateFin->setTime($creneau[1], 0);

                    array_push($seance, $dateFin);

                    array_push($seances, $seance);

                    $seance=[];
                }
            }
        }


        foreach ($seances as $seance) {
            $seanceEvent=null;

            $dateFin = clone $seance->getDateDebut();
            $dateFin->add(new \DateInterval('PT1H'));

            // Seances disponibles à l'inscription (par encore réservées)
            if (array_key_exists('eleve', $filters) && array_key_exists('cours', $filters) && !$seance->getEleve()) {

                // $dateFin = clone $seance->getDateDebut();
                // $dateFin->add(new \DateInterval('PT1H'));

                $seanceEvent = new Event(
                    "S'inscire",
                    $seance->getDateDebut(),
                    $dateFin 
                );

                $seanceEvent->setOptions([
                    'backgroundColor' => 'blue',
                    'borderColor' => 'blue',
                    'textColor' => 'white',
                    'url'=> $this->router->generate('demande_inscription_seance', [
                                'idSeance' => $seance->getId(),
                                'idEleve' => $filters['eleve'],
                                'idCours' => $filters['cours'],
                    ])
                ]); 

            }

            // Seances d'un eleve
            else if (array_key_exists('eleve', $filters) && !array_key_exists('cours', $filters)) {

                // $dateFin = clone $seance->getDateDebut();
                // $dateFin->add(new \DateInterval('PT1H'));

                $seanceEvent = new Event(
                    $seance->getCours()->getActivite()->getNom().' avec '.$seance->getProf()->getNom(),
                    $seance->getDateDebut(),
                    $dateFin // If the end date is null or not defined, a all day event is created.
                );

                $seanceEvent->setOptions([
                    'backgroundColor' => 'orange',
                    'borderColor' => 'orange',
                    'textColor' => 'white',
                    'url' => $this->router->generate('emettre_avis', [
                                'idProf' => $seance->getProf()->getId(),
                                'idEleve' => $filters['eleve']
                    ])
                ]);

            }

            // Seances d'un prof
            else if (array_key_exists('prof', $filters) && !array_key_exists('cours', $filters)) {

                // $dateFin = clone $seance->getDateDebut();
                // $dateFin->add(new \DateInterval('PT1H'));

                // COURS VALIDE
                if ( $seance->getEleve() ) {
                    $seanceEvent = new Event(
                        $seance->getCours()->getActivite()->getNom().' avec '.$seance->getEleve()->getNom(),
                        $seance->getDateDebut(),
                        $dateFin // If the end date is null or not defined, a all day event is created.
                    );

                    $seanceEvent->setOptions([
                        'backgroundColor' => '#1A252F',
                        'borderColor' => '#',
                        'textColor' => 'white'
                    ]);
                }

                // CRENEAU AVEC DEMANDES DE COURS
                else if ( $demandesCours = $this->demandeCoursRepository
                            ->createQueryBuilder('d')
                            ->andWhere('d.seance = :seance')
                            ->andWhere('d.repondue = 0')
                            ->setParameter('seance', $seance)
                            ->getQuery()
                            ->getResult() ) {
                
                    $seanceEvent = new Event(
                        'Créneau libre avec '.count($demandesCours).' demandes de cours',
                        $seance->getDateDebut(),
                        $dateFin // If the end date is null or not defined, a all day event is created.
                    );

                    $seanceEvent->setOptions([
                        'backgroundColor' => 'blue',
                        'borderColor' => 'blue',
                        'textColor' => 'white'
                    ]);
                }

                // Séance disponible avec aucune demande d'élève
                else {
                    // $dateFin = clone $seance->getDateDebut();
                    // $dateFin->add(new \DateInterval('PT1H'));
                    
                    $seanceEvent = new Event(
                        'Creneau libre',
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
                    'backgroundColor' => '#1A252F',
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