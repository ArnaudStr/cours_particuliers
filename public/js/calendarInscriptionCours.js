document.addEventListener('DOMContentLoaded', () => {
    var calendarEl = document.getElementById('calendar-holder');
    var elevejs = document.querySelector('[data-entry-ideleve]').dataset.entryIdeleve;
    var coursjs = document.querySelector('[data-entry-idcours]').dataset.entryIdcours;
    var profjs = document.querySelector('[data-entry-idprof]').dataset.entryIdprof;


    var calendar = new FullCalendar.Calendar(calendarEl, {
        defaultView: 'timeGridWeek',
        editable: false,
        eventSources: [
            {
                url: "/fc-load-events",
                method: "POST",
                extraParams: {
                    filters: JSON.stringify({ eleve:elevejs, cours:coursjs, prof:profjs })
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