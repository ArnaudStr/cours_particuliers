<?php

namespace App\EventListener;

use App\Entity\CreneauCours;
use App\Repository\CreneauCoursRepository;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use CalendarBundle\Entity\Event;
use CalendarBundle\Event\CalendarEvent;

class CalendarListener
{
    private $creneauCoursRepository;
    private $router;

    public function __construct(
        CreneauCoursRepository $creneauCoursRepository,
        UrlGeneratorInterface $router
    ) {
        $this->creneauCoursRepository = $creneauCoursRepository;
        $this->router = $router;
    }

    public function load(CalendarEvent $calendar): void
    {
        $start = $calendar->getStart();
        $end = $calendar->getEnd();
        $filters = $calendar->getFilters();

        // Modify the query to fit to your entity and needs
        // Change formation.beginAt by your start date property
        // $formations = $this->formationRepository
        //     ->createQueryBuilder('formation')
        //     ->where('formation.dateDebut BETWEEN :start and :end')
        //     // ->and('formation.dateDebut BETWEEN :start and :end')
        //     ->setParameter('start', $start->format('Y-m-d H:i:s'))
        //     ->setParameter('end', $end->format('Y-m-d H:i:s'))
        //     ->getQuery()
        //     ->getResult()
        // ;

        $creneauxCours = $this->creneauCoursRepository
        ->createQueryBuilder('creneauCours')
        ->where('creneauCours.dateDebut BETWEEN :start and :end')
        ->orWhere('creneauCours.dateFin BETWEEN :start and :end')
        ->orWhere(':end BETWEEN creneauCours.dateDebut and creneauCours.dateFin')
        ->setParameter('start', $start->format('Y-m-d H:i:s'))
        ->setParameter('end', $end->format('Y-m-d H:i:s'))
        ->getQuery()
        ->getResult();

        foreach ($creneauxCours as $creneauCours) {
            // this create the events with your data (here formation data) to fill calendar
            $creneauCoursEvent = new Event(
                $creneauCours->getProf()->getNom(),
                $creneauCours->getDateDebut(),
                $creneauCours->getDateFin() // If the end date is null or not defined, a all day event is created.
            );

            /*
             * Add custom options to events
             *
             * For more information see: https://fullcalendar.io/docs/event-object
             * and: https://github.com/fullcalendar/fullcalendar/blob/master/src/core/options.ts
             */

            $creneauCoursEvent->setOptions([
                'backgroundColor' => 'orange',
                'borderColor' => 'orange',
                'font-color' => 'black'
            ]);
            // $creneauCoursEvent->addOption(
            //     'url',
            //     $this->router->generate('showInfoSession', [
            //         'id' => $formation->getId(),
            //     ])
            // );

            // finally, add the event to the CalendarEvent to fill the calendar
            $calendar->addEvent($creneauCoursEvent);
        }
    }
}