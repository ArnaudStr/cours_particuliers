<?php

namespace App\EventListener;

use App\Entity\Session;
use App\Repository\SessionRepository;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use CalendarBundle\Entity\Event;
use CalendarBundle\Event\CalendarEvent;

class CalendarListener
{
    private $sessionRepository;
    private $router;

    public function __construct(
        SessionRepository $sessionRepository,
        UrlGeneratorInterface $router
    ) {
        $this->sessionRepository = $sessionRepository;
        $this->router = $router;
    }

    public function load(CalendarEvent $calendar): void
    {
        $start = $calendar->getStart();
        $end = $calendar->getEnd();
        $filters = $calendar->getFilters();

        if (array_key_exists('cours', $filters)) {

            // SESSIONS DISPONIBLES POUR UN COURS
            $sessions = $this->sessionRepository
                ->createQueryBuilder('session')
                ->innerJoin('session.creneau', 'cr')
                ->innerJoin('cr.cours', 'c')
                ->where('session.dateDebut BETWEEN :start and :end')
                ->andWhere('c.id = :id')
                // ->and('formation.dateDebut BETWEEN :start and :end')
                ->setParameter('start', $start->format('Y-m-d H:i:s'))
                ->setParameter('end', $end->format('Y-m-d H:i:s'))
                ->setParameter('id', $filters['cours'])
                ->getQuery()
                ->getResult()
            ;
        }
        else if (array_key_exists('eleve', $filters)) {

            // SESSION D'UN ELEVE
            $sessions = $this->sessionRepository
            ->createQueryBuilder('session')
            ->innerJoin('session.eleve', 'e')
            ->where('session.dateDebut BETWEEN :start and :end')
            ->andWhere('e.id = :id')
            // ->orWhere('session.dateFin BETWEEN :start and :end')
            // ->orWhere(':end BETWEEN session.dateDebut and session.dateFin')
            ->setParameter('start', $start->format('Y-m-d H:i:s'))
            ->setParameter('end', $end->format('Y-m-d H:i:s'))
            ->setParameter('id', $filters['eleve'])
            ->getQuery()
            ->getResult();
        }
        else {

            // SESSIONS D'UN PROF
            $sessions = $this->sessionRepository
            ->createQueryBuilder('session')
            ->innerJoin('session.creneau', 'cr')
            ->innerJoin('cr.cours', 'c')
            ->innerJoin('c.prof', 'p')
            ->where('session.dateDebut BETWEEN :start and :end')
            ->andWhere('p.id = :id')
            // ->and('formation.dateDebut BETWEEN :start and :end')
            ->setParameter('start', $start->format('Y-m-d H:i:s'))
            ->setParameter('end', $end->format('Y-m-d H:i:s'))
            ->setParameter('id', $filters['prof'])
            ->getQuery()
            ->getResult()
            ;
        }


        foreach ($sessions as $session) {
            $sessionEvent=null;

            // Sessions disponibles à l'inscription
            if (array_key_exists('eleve', $filters) && array_key_exists('cours', $filters)) {
                if ( !$session->getEleve() ) {
                    // this create the events with your data (here formation data) to fill calendar
                    $sessionEvent = new Event(
                        "S'inscire",
                        $session->getDateDebut(),
                        $session->getDateFin() // If the end date is null or not defined, a all day event is created.
                    );

                }
            }

            // Sessions d'un eleve
            else if (array_key_exists('eleve', $filters)) {
                $sessionEvent = new Event(
                    $session->getCreneau()->getCours()->getActivite()->getNom().' avec '.$session->getCreneau()->getCours()->getProf()->getNom(),
                    $session->getDateDebut(),
                    $session->getDateFin() // If the end date is null or not defined, a all day event is created.
                );
            }
            // Sessions d'un prof
            else {
                if ( $session->getEleve() ) {
                    $sessionEvent = new Event(
                        $session->getCreneau()->getCours()->getActivite()->getNom().' avec '.$session->getEleve()->getNom(),
                        $session->getDateDebut(),
                        $session->getDateFin() // If the end date is null or not defined, a all day event is created.
                    );
                }
            }
            /*
            * Add custom options to events
            *
            * For more information see: https://fullcalendar.io/docs/event-object
            * and: https://github.com/fullcalendar/fullcalendar/blob/master/src/core/options.ts
            */

            if ($sessionEvent) {
                $sessionEvent->setOptions([
                    'backgroundColor' => 'orange',
                    'borderColor' => 'orange',
                    'font-color' => 'black'
                ]);
            }



            if (array_key_exists('eleve', $filters) && array_key_exists('cours', $filters)) {

                $sessionEvent->addOption(
                    'url',
                    $this->router->generate('validation_inscription_session', [
                        'idSession' => $session->getId(),
                        'idEleve' => $filters['eleve']
                    ])
                );
            }
            // else {
            //     $sessionEvent->addOption(
            //         'url',
            //         $this->router->generate('validation_inscription_session', [
            //             'idSession' => $session->getId(),
            //             'idEleve' => ,
            //         ])
            //     );
            // }

            if ($sessionEvent) {
                // finally, add the event to the CalendarEvent to fill the calendar
                $calendar->addEvent($sessionEvent);
            }
        }
    }
}