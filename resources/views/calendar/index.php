<?php
// Calendar View
ob_start();
?>

<!-- Header -->
<div class="mb-6">
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-3">
            <span class="material-symbols-outlined text-4xl text-primary">calendar_month</span>
            <div>
                <h2 class="text-3xl font-bold text-text-light dark:text-text-dark">Kalender</h2>
                <p class="text-text-muted-light dark:text-text-muted-dark">Termine & Events verwalten</p>
            </div>
        </div>
        <button onclick="openNewEventModal()" 
                class="flex items-center gap-2 bg-primary hover:bg-teal-600 text-white px-6 py-3 rounded-lg transition-colors shadow-lg">
            <span class="material-symbols-outlined">add</span>
            <span>Neuer Termin</span>
        </button>
    </div>
</div>

<!-- Messages -->
<?php if (isset($_SESSION['error'])): ?>
    <div class="mb-6 bg-red-50 dark:bg-red-900/20 border-2 border-red-500 text-red-800 dark:text-red-200 px-6 py-4 rounded-lg">
        <?= htmlspecialchars($_SESSION['error']) ?>
    </div>
    <?php unset($_SESSION['error']); ?>
<?php endif; ?>

<?php if (isset($_SESSION['success'])): ?>
    <div class="mb-6 bg-green-50 dark:bg-green-900/20 border border-green-200 text-green-800 dark:text-green-200 px-6 py-4 rounded-lg">
        <?= htmlspecialchars($_SESSION['success']) ?>
    </div>
    <?php unset($_SESSION['success']); ?>
<?php endif; ?>

<!-- Calendar -->
<div class="bg-card-light dark:bg-card-dark rounded-xl shadow-lg p-6">
    <div id="calendar"></div>
</div>

<!-- Modal: Neuer Termin -->
<div id="eventModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-card-light dark:bg-card-dark rounded-xl shadow-2xl max-w-2xl w-full mx-4 max-h-[90vh] overflow-y-auto">
        <div class="p-6 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
            <h3 class="text-2xl font-bold text-text-light dark:text-text-dark" id="modalTitle">Neuer Termin</h3>
            <button onclick="closeEventModal()" class="text-text-muted-light dark:text-text-muted-dark hover:text-text-light dark:hover:text-text-dark">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>
        
        <form id="eventForm" class="p-6 space-y-4">
            <input type="hidden" id="eventId" name="id">
            
            <div>
                <label class="block text-sm font-medium text-text-light dark:text-text-dark mb-2">Titel *</label>
                <input type="text" id="eventTitle" name="title" required class="w-full px-4 py-2 bg-bg-light dark:bg-bg-dark border border-gray-300 dark:border-gray-600 rounded-lg">
            </div>
            
            <div>
                <label class="block text-sm font-medium text-text-light dark:text-text-dark mb-2">Beschreibung</label>
                <textarea id="eventDescription" name="description" rows="3" class="w-full px-4 py-2 bg-bg-light dark:bg-bg-dark border border-gray-300 dark:border-gray-600 rounded-lg"></textarea>
            </div>
            
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-text-light dark:text-text-dark mb-2">Start *</label>
                    <input type="datetime-local" id="eventStart" name="start" required class="w-full px-4 py-2 bg-bg-light dark:bg-bg-dark border border-gray-300 dark:border-gray-600 rounded-lg">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-text-light dark:text-text-dark mb-2">Ende *</label>
                    <input type="datetime-local" id="eventEnd" name="end" required class="w-full px-4 py-2 bg-bg-light dark:bg-bg-dark border border-gray-300 dark:border-gray-600 rounded-lg">
                </div>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-text-light dark:text-text-dark mb-2">Ort</label>
                <input type="text" id="eventLocation" name="location" class="w-full px-4 py-2 bg-bg-light dark:bg-bg-dark border border-gray-300 dark:border-gray-600 rounded-lg">
            </div>
            
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-text-light dark:text-text-dark mb-2">Kategorie</label>
                    <select id="eventCategory" name="category" class="w-full px-4 py-2 bg-bg-light dark:bg-bg-dark border border-gray-300 dark:border-gray-600 rounded-lg">
                        <option value="">Keine</option>
                        <option value="meeting">Meeting</option>
                        <option value="deadline">Deadline</option>
                        <option value="appointment">Termin</option>
                        <option value="vacation">Urlaub</option>
                        <option value="other">Sonstiges</option>
                    </select>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-text-light dark:text-text-dark mb-2">Farbe</label>
                    <input type="color" id="eventColor" name="color" value="#14b8a6" class="w-full h-10 px-2 py-1 bg-bg-light dark:bg-bg-dark border border-gray-300 dark:border-gray-600 rounded-lg">
                </div>
            </div>
            
            <div class="flex items-center">
                <input type="checkbox" id="eventAllDay" name="allDay" class="rounded">
                <label for="eventAllDay" class="ml-2 text-sm">Ganztägig</label>
            </div>
            
            <div class="flex justify-end gap-3 pt-4">
                <button type="button" onclick="closeEventModal()" class="px-6 py-2 bg-gray-200 dark:bg-gray-700 text-text-light dark:text-text-dark rounded-lg hover:bg-gray-300 dark:hover:bg-gray-600">
                    Abbrechen
                </button>
                <button type="button" id="deleteEventBtn" onclick="deleteEvent()" class="hidden px-6 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg">
                    Löschen
                </button>
                <button type="submit" class="px-6 py-2 bg-primary hover:bg-teal-600 text-white rounded-lg">
                    Speichern
                </button>
            </div>
        </form>
    </div>
</div>

<!-- FullCalendar CSS & JS -->
<link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/locales/de.global.min.js"></script>

<script>
let calendar;
let currentEvent = null;

document.addEventListener('DOMContentLoaded', function() {
    const calendarEl = document.getElementById('calendar');
    
    calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        locale: 'de',
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,timeGridDay'
        },
        buttonText: {
            today: 'Heute',
            month: 'Monat',
            week: 'Woche',
            day: 'Tag'
        },
        events: '/api/calendar/events',
        editable: true,
        selectable: true,
        selectMirror: true,
        dayMaxEvents: true,
        
        select: function(info) {
            openNewEventModal(info.startStr, info.endStr);
        },
        
        eventClick: function(info) {
            openEditEventModal(info.event);
        },
        
        eventDrop: function(info) {
            updateEventDates(info.event);
        },
        
        eventResize: function(info) {
            updateEventDates(info.event);
        }
    });
    
    calendar.render();
});

function openNewEventModal(start = null, end = null) {
    currentEvent = null;
    document.getElementById('modalTitle').textContent = 'Neuer Termin';
    document.getElementById('eventForm').reset();
    document.getElementById('eventId').value = '';
    document.getElementById('deleteEventBtn').classList.add('hidden');
    
    if (start) {
        document.getElementById('eventStart').value = start.slice(0, 16);
        document.getElementById('eventEnd').value = end ? end.slice(0, 16) : start.slice(0, 16);
    }
    
    document.getElementById('eventModal').classList.remove('hidden');
}

function openEditEventModal(event) {
    currentEvent = event;
    document.getElementById('modalTitle').textContent = 'Termin bearbeiten';
    document.getElementById('eventId').value = event.id;
    document.getElementById('eventTitle').value = event.title;
    document.getElementById('eventDescription').value = event.extendedProps.description || '';
    document.getElementById('eventStart').value = event.start.toISOString().slice(0, 16);
    document.getElementById('eventEnd').value = event.end ? event.end.toISOString().slice(0, 16) : '';
    document.getElementById('eventLocation').value = event.extendedProps.location || '';
    document.getElementById('eventCategory').value = event.extendedProps.category || '';
    document.getElementById('eventColor').value = event.backgroundColor;
    document.getElementById('eventAllDay').checked = event.allDay;
    document.getElementById('deleteEventBtn').classList.remove('hidden');
    
    document.getElementById('eventModal').classList.remove('hidden');
}

function closeEventModal() {
    document.getElementById('eventModal').classList.add('hidden');
    currentEvent = null;
}

document.getElementById('eventForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const formData = new FormData(e.target);
    const data = Object.fromEntries(formData.entries());
    
    // Checkbox-Wert manuell setzen (wird sonst nicht übertragen wenn unchecked)
    data.allDay = document.getElementById('eventAllDay').checked;
    
    // Konvertiere datetime-local zu ISO-Format mit Sekunden
    if (data.start) {
        data.start = data.start + ':00'; // Füge Sekunden hinzu
    }
    if (data.end) {
        data.end = data.end + ':00';
    }
    
    const eventId = document.getElementById('eventId').value;
    const url = eventId ? `/api/calendar/events/${eventId}` : '/api/calendar/events';
    const method = eventId ? 'PUT' : 'POST';
    
    console.log('Sending data:', data);
    
    try {
        const response = await fetch(url, {
            method: method,
            headers: { 
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify(data)
        });
        
        // Prüfe Content-Type der Response
        const contentType = response.headers.get('content-type');
        console.log('Response Content-Type:', contentType);
        
        if (!contentType || !contentType.includes('application/json')) {
            const text = await response.text();
            console.error('Non-JSON response:', text);
            alert('Fehler: Server hat kein JSON zurückgegeben. Siehe Console für Details.');
            return;
        }
        
        const result = await response.json();
        console.log('Response:', result);
        
        if (response.ok && result.success) {
            closeEventModal();
            calendar.refetchEvents();
        } else {
            alert('Fehler beim Speichern: ' + (result.error || 'Unbekannter Fehler'));
            console.error('Error response:', result);
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Fehler beim Speichern: ' + error.message);
    }
});

async function deleteEvent() {
    if (!currentEvent || !confirm('Termin wirklich löschen?')) return;
    
    try {
        const response = await fetch(`/api/calendar/events/${currentEvent.id}`, {
            method: 'DELETE'
        });
        
        if (response.ok) {
            closeEventModal();
            calendar.refetchEvents();
        } else {
            alert('Fehler beim Löschen');
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Fehler beim Löschen');
    }
}

async function updateEventDates(event) {
    try {
        await fetch(`/api/calendar/events/${event.id}`, {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                start: event.start.toISOString(),
                end: event.end ? event.end.toISOString() : event.start.toISOString()
            })
        });
    } catch (error) {
        console.error('Error:', error);
        event.revert();
    }
}
</script>

<?php
$content = ob_get_clean();
$pageTitle = 'Kalender';
$title = 'Kalender - Business Manager';
require __DIR__ . '/../layouts/app.php';
?>
