// TO DO : boutons pour scroll les heures de la journée!
var minTimeTab =[]
minTimeTab.push('00:00:00', '06:00:00', '15:00:00')
var pos=1

var tabBtn = document.querySelectorAll('.btnA')
console.log(tabBtn)
tabBtn.forEach(function(e){
    console.log(tabBtn)
    e.addEventListener('click', function(r){
        console.log(pos)
        if (r.dataset.btn === 'up'){
            pos--
            console.log(pos)
        }

        if (r.dataset.btn === 'down'){
            pos++
            console.log(pos)
        }
    })
})

document.addEventListener('DOMContentLoaded', () => {
    var calendarEl = document.getElementById('calendar-holder');
    var profjs = document.querySelector('[data-entry-idprof]').dataset.entryIdprof;

    var calendar = new FullCalendar.Calendar(calendarEl, {
        locale: 'fr',
        defaultView: 'timeGridWeek',
        height:'auto',
        allDaySlot: false,
        minTime:'07:00:00',
        maxTime:'15:00:00',
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
            // left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,timeGridDay',
        },
        plugins: [ 'interaction', 'dayGrid', 'timeGrid' ], // https://fullcalendar.io/docs/plugin-index
        timeZone: 'UTC',
    });
    calendar.render();
});