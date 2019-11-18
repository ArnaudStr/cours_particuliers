document.addEventListener('DOMContentLoaded', () => {
    var calendarEl = document.getElementById('calendar-holder');
    var profjs = document.querySelector('[data-entry-idprof]').dataset.entryIdprof;

    var calendar = new FullCalendar.Calendar(calendarEl, {
        locale: 'fr',
        defaultView: 'timeGridWeek',
        selectable: true,
        // editable: true,
        // droppable: true, // this allows things to be dropped onto the calendar
        eventOverlap: false,
        eventDurationEditable: false,
        height:'auto',
        allDaySlot: false,
        minTime:'05:00:00',
        maxTime:'24:00:00',
        slotDuration: '01:00:00',
        buttonText: 
            { 
                today:    'Aujourd\'hui',
                month:    'Mois',
                week:     'Semaine',
                day:      'Jour',
                list:     'list'
            
            },
        firstDay:1,
        
        eventSources: [
            {
                url: "/fc-load-events",
                method: "POST",
                extraParams: {
                    filters: JSON.stringify({ prof:profjs })
                },
                failure: () => {
                    // alert("There was an error while fetching FullCalendar!");
                },
            },
        ],
        header: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,timeGridDay',
        },
        plugins: [ 'interaction', 'dayGrid', 'timeGrid' ], // https://fullcalendar.io/docs/plugin-index
        timeZone: 'UTC',
    });

    calendar.render();
});