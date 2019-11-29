document.addEventListener('DOMContentLoaded', () => {
    var calendarEl = document.getElementById('calendar-holder');
    var profjs = document.querySelector('[data-entry-idprof]').dataset.entryIdprof;

    var Draggable = FullCalendarInteraction.Draggable;
    var containerEl = document.getElementById('external-events');

    // initialize the external events
    new Draggable(containerEl, {
        itemSelector: '.fc-event',
        eventData: function(eventEl) {
        return {
            title: eventEl.innerText
        };
        }
    });

    var calendar = new FullCalendar.Calendar(calendarEl, {
        locale: 'fr',
        defaultView: 'timeGridWeek',

        editable: true,
        droppable: true, // this allows things to be dropped onto the calendar
        eventClick: function(info) {
            var del = confirm('Voulez-vous vraiment supprimer ce créneau ?');
            if(del) {
                info.event.remove();
            }
          },

        slotDuration: '01:00:00',
        eventOverlap: false,
        height: 'auto',
        allDaySlot: false,
        minTime:'05:00:00',
        maxTime:'24:00:00',
        firstDay:1,
        
        eventSources: [
            {
                url: "/fc-load-events",
                method: "POST",
                extraParams: {
                    filters: JSON.stringify({ profDispos:profjs })
                },
                failure: () => {
                    // alert("There was an error while fetching FullCalendar!");
                },
            },
        ],

        header: {
            left: '',
            center: '',
            right: '',
        },

        plugins: [ 'interaction', 'dayGrid', 'timeGrid' ], // https://fullcalendar.io/docs/plugin-index
        timeZone: 'UTC',

    });

    calendar.render();
});

