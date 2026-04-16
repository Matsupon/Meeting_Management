@extends('layouts.app')

@section('title', 'Schedule List - MMS')

@section('content')
<div class="main-container">
    <!-- Sidebar -->
    <aside class="sidebar glass">
        <div class="sidebar-header">
            <div class="logo">
                <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="var(--primary-blue)" stroke-width="2.5"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
                <span>MMS</span>
            </div>
        </div>
        <nav class="sidebar-nav">
            <a href="{{ route('dashboard') }}" class="nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path><polyline points="9 22 9 12 15 12 15 22"></polyline></svg>
                <span>Dashboard</span>
            </a>
            <a href="{{ route('schedule.list') }}" class="nav-item {{ request()->routeIs('schedule.list') ? 'active' : '' }}">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="8" y1="6" x2="21" y2="6"></line><line x1="8" y1="12" x2="21" y2="12"></line><line x1="8" y1="18" x2="21" y2="18"></line><line x1="3" y1="6" x2="3.01" y2="6"></line><line x1="3" y1="12" x2="3.01" y2="12"></line><line x1="3" y1="18" x2="3.01" y2="18"></line></svg>
                <span>Schedule List</span>
            </a>
        </nav>
        <div class="sidebar-footer">
            <a href="{{ route('logout') }}" class="nav-item logout">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path><polyline points="16 17 21 12 16 7"></polyline><line x1="21" y1="12" x2="9" y2="12"></line></svg>
                <span>Logout</span>
            </a>
        </div>
    </aside>

    <main class="dashboard-wrapper" style="display: block; padding: 40px; overflow-y: auto;">
        <header style="margin-bottom: 30px; display: flex; justify-content: space-between; align-items: center;">
            <div>
                <h1 style="font-size: 32px; font-weight: 800; color: var(--text-main);">Schedule List</h1>
                <p style="color: var(--text-muted); font-weight: 500;">View and manage all your upcoming and past schedules.</p>
            </div>
            <div style="display: flex; gap: 15px; align-items: center;">
                <button class="notification-btn" id="btn-notification" title="Notifications" aria-label="View notifications" style="background: transparent; border: none; cursor: pointer; padding: 8px; border-radius: 50%; color: var(--primary-blue);">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path>
                        <path d="M13.73 21a2 2 0 0 1-3.46 0"></path>
                    </svg>
                    <span class="notification-badge" id="notification-badge" style="display: none;"></span>
                </button>
                <select id="month-filter" class="glass" style="padding: 12px 20px; border-radius: var(--border-radius-md); border: 1px solid rgba(226, 232, 240, 0.8); font-weight: 600; color: var(--primary-blue);">
                    <option value="all">All Months</option>
                    <option value="1">January</option>
                    <option value="2">February</option>
                    <option value="3">March</option>
                    <option value="4">April</option>
                    <option value="5">May</option>
                    <option value="6">June</option>
                    <option value="7">July</option>
                    <option value="8">August</option>
                    <option value="9">September</option>
                    <option value="10">October</option>
                    <option value="11">November</option>
                    <option value="12">December</option>
                </select>
            </div>
        </header>

        <div class="glass" style="border-radius: var(--border-radius-lg); overflow: hidden;">
            <table style="width: 100%; border-collapse: collapse; text-align: left;">
                <thead style="background: rgba(37, 99, 235, 0.05); color: var(--primary-blue); font-weight: 700;">
                    <tr>
                        <th style="padding: 20px;">Title</th>
                        <th style="padding: 20px;">Duration</th>
                        <th style="padding: 20px;">Time</th>
                        <th style="padding: 20px;">Status</th>
                        <th style="padding: 20px;">Details</th>
                    </tr>
                </thead>
                <tbody id="schedule-table-body">
                    <!-- Data injected via JS -->
                </tbody>
            </table>
        </div>
    </main>
</div>

<!-- Notification modal -->
<div class="modal-overlay" id="custom-alert-modal" style="z-index: 99999;">
    <div class="modal-content" style="max-width: 450px; padding: 40px;">
        <h2 style="font-size: 24px; font-weight: 800; color: #1e293b; margin-bottom: 20px;">Upcoming Reminders</h2>
        <div id="custom-alert-list" style="margin-bottom: 30px; display: flex; flex-direction: column; gap: 10px; max-height: 40vh; overflow-y: auto; text-align: left;"></div>
        <button class="btn btn-accent" id="custom-alert-ok" style="width: 100%; padding: 14px; font-size: 16px; border-radius: 12px;">Okay</button>
    </div>
</div>

<!-- Simple detail modal for list view -->
<div class="modal-overlay" id="view-modal">
    <div class="modal-content" style="max-width: 600px; padding: 40px;">
        <div style="margin-bottom: 20px;">
            <h2 id="view-title" style="word-break: break-word; font-size: 32px; color: var(--text-main); font-weight: 800; margin-bottom: 10px;">Schedule Title</h2>
            <div id="view-status-badge" class="status-badge badge-upcoming" style="font-size: 16px; padding: 6px 14px;">🟡 Upcoming</div>
        </div>
        
        <div style="margin-bottom: 25px; display: flex; gap: 15px; flex-direction: column;">
            <div style="display: flex; align-items: center; gap: 12px; font-size: 22px; color: var(--text-main);">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="var(--primary-blue)" stroke-width="2.5"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
                <span id="view-date" style="font-weight: 700;">Date</span>
            </div>
            <div style="display: flex; align-items: center; gap: 12px; font-size: 22px; color: var(--text-main);">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="var(--primary-blue)" stroke-width="2.5"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                <span id="view-time" style="font-weight: 700;">Time</span>
            </div>
        </div>
        
        <div style="margin-bottom: 25px; background: #f8fafc; padding: 20px; border-radius: var(--border-radius-md); border: 2px solid #e2e8f0; display: none;" id="view-desc-container">
            <div style="font-size: 14px; color: var(--text-muted); text-transform: uppercase; font-weight: 800; margin-bottom: 8px;">Details</div>
            <p id="view-desc" style="color: var(--text-main); font-size: 18px; white-space: pre-wrap; line-height: 1.6;"></p>
        </div>

        <div style="display: flex; justify-content: flex-end; margin-top: 20px;">
            <button class="btn btn-subdued" onclick="closeModal('view-modal')">Close</button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
@vite(['resources/js/schedule.js'])
<script>
    // Specific logic for the schedule list page
    document.addEventListener('DOMContentLoaded', () => {
        const monthFilter = document.getElementById('month-filter');
        if (monthFilter) {
            monthFilter.addEventListener('change', renderScheduleTable);
            // Default to current month if you want
            monthFilter.value = new Date().getMonth() + 1;
            renderScheduleTable();
        }
        
        // Modal overlay handling for the list page specifically if needed
        document.querySelectorAll('.modal-overlay').forEach(overlay => {
            overlay.addEventListener('mousedown', (e) => {
                if (e.target === overlay) overlay.classList.remove('active');
            });
        });
    });

    function renderScheduleTable() {
        const tableBody = document.getElementById('schedule-table-body');
        if (!tableBody) return;
        
        tableBody.innerHTML = '';
        const filterVal = document.getElementById('month-filter').value;
        
        const filtered = events.filter(e => {
            if (filterVal === 'all') return true;
            const month = new Date(e.date + 'T00:00:00').getMonth() + 1;
            return month === parseInt(filterVal);
        });

        if (filtered.length === 0) {
            tableBody.innerHTML = '<tr><td colspan="5" style="padding: 40px; text-align: center; color: var(--text-muted);">No schedules found for this month.</td></tr>';
            return;
        }

        filtered.forEach(e => {
            const row = document.createElement('tr');
            row.style.cssText = 'border-bottom: 1px solid rgba(226, 232, 240, 0.5); cursor: pointer; transition: background 0.2s;';
            row.onmouseenter = () => row.style.background = 'rgba(37, 99, 235, 0.02)';
            row.onmouseleave = () => row.style.background = 'transparent';
            row.onclick = () => openViewModal(e.id);

            const duration = e.end_date ? `${e.date} to ${e.end_date}` : e.date;
            
            let statusBadge = '';
            if (e.status === 'completed') statusBadge = '<span class="status-badge badge-completed">🟢 Finished</span>';
            else if (e.status === 'cancelled') statusBadge = '<span class="status-badge badge-cancelled">🔴 Cancelled</span>';
            else statusBadge = '<span class="status-badge badge-upcoming">🟡 Upcoming</span>';

            row.innerHTML = `
                <td style="padding: 20px; font-weight: 700; color: var(--text-main);">${e.title}</td>
                <td style="padding: 20px; color: var(--text-muted); font-weight: 500;">${duration}</td>
                <td style="padding: 20px; color: var(--text-muted); font-weight: 500;">${formatTimeToAmer(e.time)}</td>
                <td style="padding: 20px;">${statusBadge}</td>
                <td style="padding: 20px; color: var(--primary-blue); font-weight: 700;">View Details</td>
            `;
            tableBody.appendChild(row);
        });
    }

    // Override the fetchEvents to also re-render the table if we are on this page
    const originalFetchEvents = fetchEvents;
    fetchEvents = async function() {
        await originalFetchEvents();
        if (document.getElementById('schedule-table-body')) {
            renderScheduleTable();
        }
    };
</script>
@endpush
