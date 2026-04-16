import './bootstrap';
import { getFetchHeaders } from './api.js';
let currentDate = new Date();
let events = [];
let selectedFilterDate = null;

// Laravel backend base URL
const API_BASE = document.querySelector('meta[name="api-base-url"]')?.content || '/api/events';

// DOM Elements
const calendarGrid = document.getElementById('calendar-grid');
const monthYearText = document.getElementById('calendar-month-year');
const prevMonthBtn = document.getElementById('prev-month');
const nextMonthBtn = document.getElementById('next-month');
const eventModal = document.getElementById('event-modal');
const viewModal = document.getElementById('view-modal');
const eventForm = document.getElementById('event-form');
const eventListContainer = document.getElementById('event-list-container');
const timeSelect = document.getElementById('event-time');
const searchInput = document.getElementById('search-input');
const filterTimeDropdown = document.getElementById('filter-time-dropdown');
const statusChips = document.querySelectorAll('#status-filters .chip');
const selectedDayOption = document.getElementById('selected-day-option');
let activeStatusFilter = 'upcoming';

let countdownInterval = null;

// Initialization
document.addEventListener('DOMContentLoaded', () => {
    generateTimeOptions();
    fetchEvents();
    
    // Close modal on click outside or ESC
    document.querySelectorAll('.modal-overlay').forEach(overlay => {
        overlay.addEventListener('mousedown', (e) => {
            if (e.target === overlay) {
                overlay.classList.remove('active');
            }
        });
    });
    
    window.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            document.querySelectorAll('.modal-overlay').forEach(overlay => {
                overlay.classList.remove('active');
            });
        }
    });

    prevMonthBtn.addEventListener('click', () => {
        currentDate.setMonth(currentDate.getMonth() - 1);
        renderCalendar();
    });
    
    nextMonthBtn.addEventListener('click', () => {
        currentDate.setMonth(currentDate.getMonth() + 1);
        renderCalendar();
    });
    
    eventForm.addEventListener('submit', handleEventSubmit);
    
    searchInput.addEventListener('input', () => {
        if(searchInput.value && filterTimeDropdown.value === 'selected-day') {
            filterTimeDropdown.value = 'all';
        }
        renderEventList();
    });
    
    filterTimeDropdown.addEventListener('change', () => {
        if(filterTimeDropdown.value !== 'selected-day') {
            selectedFilterDate = null;
        }
        renderEventList();
    });

    statusChips.forEach(chip => {
        chip.addEventListener('click', () => {
            statusChips.forEach(c => c.classList.remove('active'));
            chip.classList.add('active');
            activeStatusFilter = chip.getAttribute('data-status');
            renderEventList();
        });
    });
    
    document.getElementById('event-date').addEventListener('change', updateTimeAvailability);
});

function formatTimeToAmer(timeStr) {
    if (!timeStr) return '';
    const [h, m] = timeStr.split(':');
    const hours = parseInt(h, 10);
    const suffix = hours >= 12 ? 'PM' : 'AM';
    const ampmHours = hours % 12 || 12;
    return `${ampmHours}:${m} ${suffix}`;
}

function getFullDateString(dateStr) {
    const dObj = new Date(`${dateStr}T00:00:00`);
    const mNames = ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"];
    return `${mNames[dObj.getMonth()]} ${dObj.getDate()}, ${dObj.getFullYear()}`;
}

async function fetchEvents() {
    try {
        const response = await fetch(API_BASE);
        const result = await response.json();
        if (result.status === 'success') {
            events = result.data.map(e => ({...e, status: e.status || 'upcoming'}));
            events.sort((a, b) => new Date(`${a.date}T${a.time}`) - new Date(`${b.date}T${b.time}`));
            renderCalendar();
            renderEventList();
            updateBentoStats();
            updateNextMeeting();
        }
    } catch (e) {
        console.error('Failed to fetch events', e);
    }
}

function renderCalendar() {
    calendarGrid.innerHTML = '';
    
    const year = currentDate.getFullYear();
    const month = currentDate.getMonth();
    
    const monthNames = ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"];
    monthYearText.textContent = `${monthNames[month]} ${year}`;
    
    const firstDay = new Date(year, month, 1).getDay();
    const daysInMonth = new Date(year, month + 1, 0).getDate();
    
    for(let i=0; i<firstDay; i++) {
        const cell = document.createElement('div');
        calendarGrid.appendChild(cell);
    }
    
    for(let i=1; i<=daysInMonth; i++) {
        const cell = document.createElement('div');
        cell.className = 'calendar-day glass';
        cell.style.cssText = 'padding: 15px 10px; text-align: center; border-radius: 12px; cursor: pointer; position: relative; display:flex; flex-direction:column; justify-content:center; align-items:center; transition: all 0.2s ease;';
        
        const dayText = document.createElement('div');
        dayText.textContent = i;
        dayText.style.fontWeight = '700';
        dayText.style.fontSize = 'var(--font-size-lg)';
        cell.appendChild(dayText);
        
        const cellDate = `${year}-${String(month+1).padStart(2, '0')}-${String(i).padStart(2, '0')}`;
        
        if (cellDate === new Date().toISOString().split('T')[0]) {
            cell.style.border = '2px solid var(--primary-blue)';
            cell.style.backgroundColor = 'rgba(255, 255, 255, 0.95)';
            dayText.style.color = 'var(--primary-blue)';
        }
        
        const dayEvents = events.filter(e => e.date === cellDate);
        
        if (dayEvents.length > 0) {
            const dotContainer = document.createElement('div');
            dotContainer.style.cssText = 'position: absolute; bottom: 8px; left: 50%; transform: translateX(-50%); display: flex; gap: 4px;';
            
            dayEvents.forEach((evt, idx) => {
                if(idx > 2) return;
                const dot = document.createElement('div');
                dot.style.cssText = 'width: 8px; height: 8px; background-color: var(--accent); border-radius: 50%; transition: transform 0.2s ease;';
                dotContainer.appendChild(dot);
            });
            cell.appendChild(dotContainer);
        }
        
        cell.addEventListener('mouseenter', () => { 
            if (cellDate !== new Date().toISOString().split('T')[0]) {
                cell.style.background = 'white'; 
            }
            cell.style.transform = 'translateY(-2px)'; 
            cell.style.boxShadow = '0 10px 15px -3px rgba(37,99,235,0.1)';
        });
        cell.addEventListener('mouseleave', () => { 
            if (cellDate !== new Date().toISOString().split('T')[0]) {
                cell.style.background = 'rgba(255, 255, 255, 0.85)'; 
            }
            cell.style.transform = 'translateY(0)'; 
            cell.style.boxShadow = 'var(--box-shadow)';
        });
        
        cell.addEventListener('click', () => {
            if (dayEvents.length > 0) {
                const modal = document.getElementById('day-events-modal');
                document.getElementById('day-events-title').textContent = `Events for ${i} ${monthNames[month]}`;
                const list = document.getElementById('day-events-list');
                list.innerHTML = '';
                list.style.cssText = 'display: flex; flex-direction: column; gap: 15px; margin-top: 20px;';
                
                dayEvents.forEach(e => {
                    const card = document.createElement('div');
                    card.className = 'glass';
                    card.style.cssText = 'padding: 20px; border-radius: var(--border-radius-md); box-shadow: var(--box-shadow); cursor: pointer; text-align: left; transition: transform 0.2s ease;';
                    let dotClass = 'status-upcoming';
                    if (e.status === 'completed') dotClass = 'status-completed';
                    if (e.status === 'cancelled') dotClass = 'status-cancelled';
                    card.innerHTML = `
                        <h3 style="font-size: 24px; font-weight: 700; color: var(--primary-blue); margin-bottom: 8px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 100%; display: flex; align-items: center; gap: 8px;">
                            <div class="status-dot ${dotClass}"></div>
                            ${e.title}
                        </h3>
                        <div style="color: var(--text-muted); font-size: var(--font-size-base); display: flex; align-items: center; gap: 5px;">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                            ${formatTimeToAmer(e.time)}
                        </div>
                    `;
                    card.onmouseenter = () => card.style.transform = 'translateY(-2px)';
                    card.onmouseleave = () => card.style.transform = 'translateY(0)';
                    card.onclick = () => {
                        closeModal('day-events-modal');
                        openViewModal(e.id);
                    };
                    list.appendChild(card);
                });
                modal.classList.add('active');
            } else {
                selectedFilterDate = cellDate;
                openModal('add');
                document.getElementById('event-date').value = cellDate;
            }
        });
        
        calendarGrid.appendChild(cell);
    }
}

function updateBentoStats() {
    const today = new Date();
    const currentMonth = today.getMonth();
    const currentYear = today.getFullYear();
    
    const firstDayOfWeek = new Date(today);
    firstDayOfWeek.setDate(today.getDate() - today.getDay());
    firstDayOfWeek.setHours(0,0,0,0);
    
    const lastDayOfWeek = new Date(firstDayOfWeek);
    lastDayOfWeek.setDate(firstDayOfWeek.getDate() + 6);
    lastDayOfWeek.setHours(23,59,59,999);
    
    let monthCount = 0;
    let weekCount = 0;
    
    events.forEach(e => {
        const [y, m, d] = e.date.split('-');
        const evtDate = new Date(y, m-1, d);
        
        if (evtDate.getMonth() === currentMonth && evtDate.getFullYear() === currentYear) {
            monthCount++;
        }
        if (evtDate >= firstDayOfWeek && evtDate <= lastDayOfWeek) {
            weekCount++;
        }
    });
    
    document.getElementById('stats-month').textContent = monthCount;
}

function updateNextMeeting() {
    if (countdownInterval) clearInterval(countdownInterval);
    const bento = document.getElementById('next-meeting-bento');
    
    const now = new Date();
    const upcomingEvents = events.filter(e => {
        if (e.status !== 'upcoming') return false;
        const evtTime = new Date(`${e.date}T${e.time}:00`);
        return evtTime > now;
    });

    if (upcomingEvents.length === 0) {
        bento.innerHTML = `
            <h3 style="color: var(--primary-blue); font-size: 14px; text-transform: uppercase; letter-spacing: 1px; font-weight: 700; margin-bottom: 5px;">NEXT MEETING</h3>
            <div style="font-size: 20px; font-weight: 700; color: var(--text-muted); line-height: 1.2; text-align: center;">No upcoming meetings</div>
        `;
        return;
    }

    const nextEvent = upcomingEvents[0];
    const targetTime = new Date(`${nextEvent.date}T${nextEvent.time}:00`).getTime();

    const updateCountdown = () => {
        const currentTime = new Date().getTime();
        const diff = targetTime - currentTime;

        if (diff <= 0) {
            clearInterval(countdownInterval);
            fetchEvents(); // Re-fetch to update states
            return;
        }

        const mins = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
        const hours = Math.floor(diff / (1000 * 60 * 60));
        
        let timeStr = `${mins} mins`;
        if (hours > 0) timeStr = `${hours}h ${mins}m`;
        if (hours > 24) {
            const days = Math.floor(hours / 24);
            timeStr = `${days} days`;
        }

        bento.innerHTML = `
            <h3 style="color: var(--primary-blue); font-size: 14px; text-transform: uppercase; letter-spacing: 1px; font-weight: 700; margin-bottom: 5px;">NEXT MEETING</h3>
            <div style="color: #ef4444; font-weight: 600; font-size: 16px; margin-bottom: 5px;">Starts in ${timeStr}</div>
            <div style="font-size: 24px; font-weight: 700; color: var(--text-main); line-height: 1.2; text-align: center; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 100%; cursor: pointer;" onclick="openViewModal('${nextEvent.id}')">${nextEvent.title}</div>
        `;
    };

    updateCountdown();
    countdownInterval = setInterval(updateCountdown, 60000); // Update every minute
}

function generateTimeOptions() {
    timeSelect.innerHTML = '';
    for(let i=8; i<=18; i++) {
        for(let m of ['00', '30']) {
            const val = `${String(i).padStart(2, '0')}:${m}`;
            const opt = document.createElement('option');
            opt.value = val;
            opt.textContent = formatTimeToAmer(val);
            timeSelect.appendChild(opt);
        }
    }
}

function updateTimeAvailability() {
    const selectedDate = document.getElementById('event-date').value;
    const currentEventId = document.getElementById('event-id').value;
    
    const dayEvents = events.filter(e => e.date === selectedDate);
    const existingTimes = {};
    dayEvents.forEach(e => {
        if (e.id !== currentEventId) {
            existingTimes[e.time] = e.title;
        }
    });
    
    Array.from(timeSelect.options).forEach(opt => {
        const baseAmPm = formatTimeToAmer(opt.value);
        if (existingTimes[opt.value]) {
            opt.disabled = true;
            let bookedStr = existingTimes[opt.value];
            if(bookedStr.length > 15) {
                bookedStr = bookedStr.substring(0, 15) + '...';
            }
            opt.textContent = `${baseAmPm} - (Booked: ${bookedStr})`;
            opt.style.color = '#94a3b8';
            opt.style.background = '#f1f5f9';
        } else {
            opt.disabled = false;
            opt.textContent = baseAmPm;
            opt.style.color = '';
            opt.style.background = '';
        }
    });

    if (timeSelect.options[timeSelect.selectedIndex]?.disabled) {
        const firstAvailable = Array.from(timeSelect.options).find(o => !o.disabled);
        if (firstAvailable) timeSelect.value = firstAvailable.value;
    }
}

function renderEventList() {
    eventListContainer.innerHTML = '';
    
    const term = searchInput.value.toLowerCase();
    const filterTime = filterTimeDropdown.value;
    
    const today = new Date();
    today.setHours(0,0,0,0);
    
    let filteredEvents = events.filter(e => {
        const matchesSearch = e.title.toLowerCase().includes(term) || (e.description ?? '').toLowerCase().includes(term);
        if (!matchesSearch) return false;
        
        if (activeStatusFilter !== 'all' && e.status !== activeStatusFilter) {
            return false;
        }

        const evtDate = new Date(`${e.date}T00:00:00`);
        
        if (filterTime === 'selected-day' && selectedFilterDate) {
            return e.date === selectedFilterDate;
        } else if (filterTime === 'today') {
            return evtDate.getTime() === today.getTime();
        } else if (filterTime === 'this-week') {
            const firstDay = new Date(today);
            firstDay.setDate(today.getDate() - today.getDay());
            const lastDay = new Date(firstDay);
            lastDay.setDate(firstDay.getDate() + 6);
            return evtDate >= firstDay && evtDate <= lastDay;
        } else if (filterTime === 'this-month') {
            return evtDate.getMonth() === today.getMonth() && evtDate.getFullYear() === today.getFullYear();
        }
        
        return true; 
    });
    
    if(filterTime !== 'selected-day' && filterTime !== 'all' && activeStatusFilter === 'all') {
        // Only if time filter is specific maybe we don't need additional defaults, but for all we should show everything
    }
    
    if (filteredEvents.length === 0) {
        eventListContainer.innerHTML = '<div style="padding: 40px; text-align: center; color: var(--text-muted); font-size: var(--font-size-lg);" class="glass">No events found for this view.</div>';
        return;
    }
    
    filteredEvents.forEach(e => {
        const row = document.createElement('div');
        row.className = 'glass event-row';
        row.style.cssText = 'display: flex; justify-content: space-between; align-items: center; padding: 25px 30px; border-radius: var(--border-radius-lg); transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1); flex-shrink: 0;';
        
        const dObj = new Date(`${e.date}T00:00:00`);
        const mNames = ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];
        const dayOfMonth = dObj.getDate();
        const monthStr = mNames[dObj.getMonth()];
        
        let dotClass = 'status-upcoming';
        let titleClass = '';
        if (e.status === 'completed') dotClass = 'status-completed';
        if (e.status === 'cancelled') {
            dotClass = 'status-cancelled';
            titleClass = 'event-title-cancelled';
        }

        row.innerHTML = `
            <div style="display: flex; align-items: center; gap: 20px; flex: 1; pointer-events: none; min-width: 0;">
                <div class="status-dot ${dotClass}" style="width: 12px; height: 12px;"></div>
                <div style="background: var(--primary-blue); color: white; border-radius: 12px; padding: 12px; text-align: center; min-width: 70px; flex-shrink: 0; box-shadow: 0 4px 10px rgba(37,99,235,0.3);">
                    <div style="font-size: var(--font-size-lg); font-weight: 700; line-height: 1;">${dayOfMonth}</div>
                    <div style="font-size: var(--font-size-sm); text-transform: uppercase; font-weight: 600; opacity: 0.9;">${monthStr}</div>
                </div>
                <div style="flex: 1; min-width: 0;">
                    <h3 class="${titleClass}" style="font-size: 19px; font-weight: 700; color: var(--text-main); margin-bottom: 5px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 100%;">${e.title}</h3>
                    <div style="display: flex; gap: 15px; color: var(--text-muted); font-size: var(--font-size-base); font-weight: 500;">
                        <span style="display: flex; align-items: center; gap: 5px;">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                            ${formatTimeToAmer(e.time)}
                        </span>
                    </div>
                </div>
            </div>
            <div style="color: var(--primary-blue); opacity: 0.5;">
                <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"></polyline></svg>
            </div>
        `;
        
        row.onclick = () => openViewModal(e.id);
        
        eventListContainer.appendChild(row);
    });
}

function openModal(mode, eventId) {
    document.getElementById('event-form').reset();
    document.getElementById('event-id').value = '';
    
    if (mode === 'add') {
        document.getElementById('modal-title').textContent = 'Add New Event';
        document.getElementById('event-date').value = selectedFilterDate || new Date().toISOString().split('T')[0];
        document.getElementById('status-group').style.display = 'none';
    } else if (mode === 'edit') {
        document.getElementById('modal-title').textContent = 'Edit Event';
        document.getElementById('status-group').style.display = 'block';
        const e = events.find(ev => ev.id === eventId);
        if (e) {
            document.getElementById('event-id').value = e.id;
            document.getElementById('event-title').value = e.title;
            document.getElementById('event-date').value = e.date;
            document.getElementById('event-desc').value = e.description;
            document.getElementById('event-status').value = e.status || 'upcoming';
            updateTimeAvailability();
            timeSelect.value = e.time;
        }
    }
    
    updateTimeAvailability();
    eventModal.classList.add('active');
    document.getElementById('event-title').focus();
}

function openViewModal(eventId) {
    const e = events.find(ev => ev.id === eventId);
    if(!e) return;
    
    document.getElementById('view-title').textContent = e.title;
    document.getElementById('view-date').textContent = getFullDateString(e.date);
    document.getElementById('view-time').textContent = formatTimeToAmer(e.time);
    
    const badge = document.getElementById('view-status-badge');
    const cancelBtn = document.getElementById('btn-cancel-meeting');
    
    badge.className = 'status-badge';
    let statusText = 'Upcoming';
    cancelBtn.style.display = 'inline-flex'; // Reset display
    
    if (e.status === 'completed') {
        statusText = 'Completed';
        badge.classList.add('badge-completed');
        badge.innerHTML = 'ðŸŸ¢ ' + statusText;
    } else if (e.status === 'cancelled') {
        statusText = 'Cancelled';
        badge.classList.add('badge-cancelled');
        badge.innerHTML = 'ðŸ”´ ' + statusText;
        cancelBtn.style.display = 'none'; // Hide if already cancelled
    } else {
        badge.classList.add('badge-upcoming');
        badge.innerHTML = 'ðŸŸ¡ ' + statusText;
    }
    
    const descCon = document.getElementById('view-desc-container');
    const descEl = document.getElementById('view-desc');
    if(e.description && e.description.trim() !== '') {
        descEl.textContent = e.description;
        descCon.style.display = 'block';
    } else {
        descCon.style.display = 'none';
    }
    
    document.getElementById('btn-edit-view').onclick = () => {
        closeModal('view-modal');
        setTimeout(() => openModal('edit', e.id), 150);
    };
    
    document.getElementById('btn-cancel-meeting').onclick = () => {
        cancelMeeting(e.id);
        closeModal('view-modal');
    };

    document.getElementById('btn-delete-view').onclick = () => {
        closeModal('view-modal');
        setTimeout(() => deleteEvent(e.id), 150);
    };
    
    viewModal.classList.add('active');
}

function closeModal(modalId) {
    document.getElementById(modalId).classList.remove('active');
}

function handleCancelModal() {
    closeModal('event-modal');
    const id = document.getElementById('event-id').value;
    if (id) {
        setTimeout(() => openViewModal(id), 150);
    }
}

async function handleEventSubmit(e) {
    e.preventDefault();
    
    const id = document.getElementById('event-id').value;
    const title = document.getElementById('event-title').value;
    const date = document.getElementById('event-date').value;
    const time = document.getElementById('event-time').value;
    const description = document.getElementById('event-desc').value;
    const status = document.getElementById('event-status') ? document.getElementById('event-status').value : 'upcoming';
    
    closeModal('event-modal');
    
    setTimeout(async () => {
        try {
            const res = await fetch(API_BASE, {
                method: 'POST',
                headers: getFetchHeaders('POST'),
                body: JSON.stringify({ id, title, date, time, description, status })
            });
            const result = await res.json();
            
            if(result.status === 'success') {
                Swal.fire({
                    title: id ? 'Event updated successfully!' : 'Event created successfully!',
                    icon: 'success',
                    toast: true,
                    position: 'top',
                    showConfirmButton: false,
                    timer: 2000,
                    timerProgressBar: true,
                    showClass: {
                        popup: 'animate__animated animate__fadeInDown animate__faster'
                    },
                    hideClass: {
                        popup: 'animate__animated animate__fadeOutUp animate__faster'
                    }
                });
                fetchEvents();
            } else {
                Swal.fire('Error!', 'Failed to save event', 'error');
            }
        } catch(err) {
            console.error(err);
            Swal.fire('Error!', 'Server Error', 'error');
        }
    }, 150);
}

async function cancelMeeting(id) {
    const e = events.find(ev => ev.id === id);
    if(!e) return;

    try {
        const res = await fetch(API_BASE, {
            method: 'POST',
            headers: getFetchHeaders('POST'),
            body: JSON.stringify({
                id: e.id,
                title: e.title,
                date: e.date,
                time: e.time,
                description: e.description,
                status: 'cancelled'
            })
        });
        const result = await res.json();
        
        if(result.status === 'success') {
            Swal.fire({
                title: 'Meeting Cancelled',
                icon: 'success',
                toast: true,
                position: 'top',
                showConfirmButton: false,
                timer: 2000,
                timerProgressBar: true
            });
            fetchEvents();
        }
    } catch(err) {
        console.error(err);
    }
}

function deleteEvent(id) {
    Swal.fire({
        title: 'Delete Event',
        text: 'Are you sure you want to delete this event?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#94a3b8',
        confirmButtonText: 'Yes, delete it!'
    }).then(async (result) => {
        if (result.isConfirmed) {
            try {
                const res = await fetch(`${API_BASE}/${id}`, {
                    method: 'DELETE',
                    headers: getFetchHeaders('DELETE'),
                });
                const data = await res.json();
                
                if(data.status === 'success') {
                    Swal.fire({
                        title: 'Event deleted successfully!',
                        icon: 'success',
                        toast: true,
                        position: 'top',
                        showConfirmButton: false,
                        timer: 2000,
                        timerProgressBar: true,
                        showClass: {
                            popup: 'animate__animated animate__fadeInDown animate__faster'
                        },
                        hideClass: {
                            popup: 'animate__animated animate__fadeOutUp animate__faster'
                        }
                    });
                    fetchEvents();
                } else {
                    Swal.fire('Error!', 'Failed to delete event', 'error');
                }
            } catch(err) {
                console.error(err);
                Swal.fire('Error!', 'Server Error', 'error');
            }
        }
    });
}
