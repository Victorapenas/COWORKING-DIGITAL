// ARQUIVO: js/calendario.js
//atualização

document.addEventListener('DOMContentLoaded', function() {
    var calendarEl = document.getElementById('calendar');
    
    // Elementos de Filtro
    var filtroTipo = document.getElementById('filtroTipo');
    var filtroStatus = document.getElementById('filtroStatus');

    var calendar = new FullCalendar.Calendar(calendarEl, {
        // --- CABEÇALHO ---
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,timeGridDay,listWeek' 
        },
        
        initialView: 'dayGridMonth', 
        locale: 'pt-br',
        navLinks: true, 
        nowIndicator: true, 
        dayMaxEvents: true, 
        
        // --- REMOÇÃO DA LINHA "DIA TODO" ---
        allDaySlot: false, 

        // --- CONFIGURAÇÃO DA GRADE DE TEMPO ---
        slotMinTime: '06:00:00',
        slotMaxTime: '22:00:00',
        slotDuration: '00:30:00',
        
        // Formato: 09:00
        slotLabelFormat: {
            hour: '2-digit',
            minute: '2-digit',
            omitZeroMinute: false,
            meridiem: false,
            hour12: false
        },
        
        // Formato da hora dentro do evento
        eventTimeFormat: { 
            hour: '2-digit',
            minute: '2-digit',
            meridiem: false,
            hour12: false
        },

        buttonText: {
            today: 'Hoje',
            month: 'Mês',
            week: 'Semana',
            day: 'Dia',
            list: 'Lista'
        },

        // --- CARREGAMENTO DE DADOS ---
        events: function(info, successCallback, failureCallback) {
            var tipo = filtroTipo.value;
            var status = filtroStatus.value;
            var url = '../api/calendario_eventos.php?start=' + info.startStr + '&end=' + info.endStr + '&tipo=' + tipo + '&status=' + status;

            fetch(url)
                .then(resp => resp.json())
                .then(data => successCallback(data))
                .catch(err => failureCallback(err));
        },

        // --- RENDERIZAÇÃO DO CONTEÚDO (HTML DO CARD) ---
        eventContent: function(arg) {
            // Se for visualização de LISTA, deixa o padrão
            if (arg.view.type.includes('list')) {
                return null; 
            }

            let icon = '';
            // Define o ícone
            if (arg.event.classNames.includes('evt-contrato')) icon = '📄';
            else if (arg.event.classNames.includes('evt-projeto')) icon = '🚀';
            else if (arg.event.classNames.includes('evt-tarefa')) icon = '✅';

            let timeText = arg.timeText; 
            
            // Layout interno
            // A classe 'fc-event-main' já tem color: inherit no CSS
            return {
                html: `
                <div style="overflow:hidden; font-size:0.85em; padding:1px; color: inherit;">
                    ${timeText ? '<div style="font-weight:normal; opacity:0.8; font-size:0.8em;">' + timeText + '</div>' : ''}
                    <div style="white-space:nowrap; overflow:hidden; text-overflow:ellipsis; font-weight:800;">
                        <span style="margin-right:3px;">${icon}</span> ${arg.event.title}
                    </div>
                </div>`
            };
        },

        eventClick: function(info) {
            const dataF = info.event.start.toLocaleDateString('pt-BR', { 
                weekday: 'long', day: 'numeric', month: 'long', hour: '2-digit', minute:'2-digit' 
            });
            alert(`DETALHES:\n\n📌 ${info.event.title}\n📅 ${dataF}`);
        }
    });

    calendar.render();

    filtroTipo.addEventListener('change', () => calendar.refetchEvents());
    filtroStatus.addEventListener('change', () => calendar.refetchEvents());
});