@extends('layouts.app')

@section('title', 'Dashboard - Schedule Management System')

@push('styles')
<style>
    .dashboard-wrapper {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 15px;
        height: 100vh;
        max-height: 100vh;
        padding: 10px;
        box-sizing: border-box;
        max-width: 1400px;
        margin: 0 auto;
        overflow: hidden;
    }

    /* Responsiveness for small screens */
    @media (max-width: 1024px) {
        .dashboard-wrapper {
            grid-template-columns: 1fr;
            height: 100vh;
            overflow-y: auto; /* Allow scroll on very small mobile if grid collapses */
        }
        .left-panel, .right-panel {
            height: auto;
        }
        .agenda-scroll-container {
            max-height: 40vh;
        }
    }



    /* LEFT PANEL */
    .left-panel {
        display: flex;
        flex-direction: column;
        gap: 15px;
        height: 100%;
        overflow: hidden;
    }

    .top-header {
        padding: 20px 30px;
        border-radius: var(--border-radius-lg);
        box-shadow: var(--box-shadow);
        flex-shrink: 0;
    }

    .welcome-area h1 {
        font-size: var(--font-size-xl);
        color: var(--primary-blue);
        font-weight: 700;
        margin-bottom: 5px;
    }
    
    .welcome-area p {
        color: var(--text-muted);
        font-size: var(--font-size-base);
        font-weight: 500;
    }

    .stats-bento {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 20px;
        flex-shrink: 0;
    }

    .bento-card {
        padding: 20px;
        border-radius: var(--border-radius-lg);
        text-align: center;
    }

    .bento-card h3 {
        font-size: var(--font-size-base);
        color: var(--text-main);
        margin-bottom: 10px;
        font-weight: 600;
    }

    .bento-number {
        font-family: 'Outfit', sans-serif;
        font-size: 60px;
        font-weight: 700;
        color: var(--primary-blue);
        line-height: 1;
    }

    .bento-number.highlight-week {
        color: #8b5cf6;
    }

    .calendar-section {
        padding: 25px;
        border-radius: var(--border-radius-lg);
        flex: 1;
        display: flex;
        flex-direction: column;
    }


    /* RIGHT PANEL */
    .right-panel {
        display: flex;
        flex-direction: column;
        gap: 15px;
        height: 100%;
        overflow: hidden;
    }

    .top-actions {
        display: flex;
        justify-content: flex-end;
        flex-shrink: 0;
    }

    .control-center {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 15px 20px;
        border-radius: var(--border-radius-lg);
        gap: 15px;
        flex-shrink: 0;
    }


    .agenda-scroll-container {
        flex: 1;
        overflow-y: auto;
        overflow-x: hidden;
        padding-right: 5px;
        display: flex;
        flex-direction: column;
        gap: 10px;
        scrollbar-width: thin;
    }

    .reminder-close {
        position: absolute;
        top: 8px;
        right: 12px;
        font-size: 20px;
        line-height: 1;
        color: var(--text-muted);
        cursor: pointer;
        transition: color 0.2s;
    }
    .reminder-close:hover { color: var(--text-main); }


    /* Modals */
    .modal-overlay {
        position: fixed;
        top: 0; left: 0; width: 100vw; height: 100vh;
        background: rgba(15, 23, 42, 0.4);
        backdrop-filter: blur(8px);
        -webkit-backdrop-filter: blur(8px);
        display: flex;
        align-items: flex-start; justify-content: center;
        padding-top: 10vh;
        z-index: 2000;
        opacity: 0; pointer-events: none;
        transition: opacity 0.3s ease;
    }
    .modal-overlay.active { opacity: 1; pointer-events: auto; }
    .modal-content {
        background: var(--card-bg);
        padding: 40px;
        border-radius: var(--border-radius-lg);
        width: 100%; max-width: 600px;
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
        transform: scale(0.95) translateY(20px);
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .modal-overlay.active .modal-content { transform: scale(1) translateY(0); }
    .modal-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; }
    .modal-header h2 { font-size: var(--font-size-lg); color: var(--primary-blue); }
    .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; }

    /* Modal responsiveness */
    @media (max-height: 700px) {
        .modal-content {
            padding: 20px;
            max-height: 90vh;
            overflow-y: auto;
        }
        .modal-header { margin-bottom: 15px; }
    }


    /* Reminder Alert */
    .reminder-alert {
        position: fixed; top: -100px; left: 50%; transform: translateX(-50%);
        background: var(--card-bg); border-left: 6px solid var(--accent);
        padding: 20px 30px; border-radius: var(--border-radius-md);
        box-shadow: 0 10px 25px rgba(0,0,0,0.1); z-index: 1100;
        display: flex; align-items: center; gap: 15px;
        transition: top 0.5s cubic-bezier(0.4, 0, 0.2, 1);
        cursor: pointer;
    }
    .reminder-alert.show { top: 30px; }
    .reminder-icon { color: var(--accent); }
    .reminder-text strong { display: block; font-size: var(--font-size-lg); color: var(--text-main); }
    .reminder-text span { color: var(--text-muted); font-size: var(--font-size-base); }

    /* Event Row Hover */
    .event-row {
        cursor: pointer;
        width: 100%;
        max-width: 100%;
        box-sizing: border-box;
    }
    .event-row:hover {
        transform: scale(1.02);
        box-shadow: 0 15px 30px -5px rgba(37,99,235,0.15);
    }
</style>
@endpush

@section('content')

    <!-- Reminder Alert -->
    <div class="reminder-alert" id="reminder-alert">
        <div class="reminder-icon">
            <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path><path d="M13.73 21a2 2 0 0 1-3.46 0"></path></svg>
        </div>
        <div class="reminder-text" id="reminder-text"></div>
    </div>

    <div class="dashboard-viewport">
        <!-- Sidebar -->
        <aside class="sidebar-main glass" id="sidebar-main">
            <div class="sidebar-content">
                <div class="legend-section">
                    <h4>LEGEND:</h4>
                    <div class="legend-item"><span class="legend-color red"></span> 3 days before the schedule</div>
                    <div class="legend-item"><span class="legend-color yellow"></span> 5 days before the schedule</div>
                    <div class="legend-item"><span class="legend-color green"></span> 1 week before the schedule</div>
                    <div class="legend-item"><span class="legend-color blue"></span> Finished schedule</div>
                </div>

                <div class="sidebar-search-filters">
                    <div class="form-group">
                        <input type="text" id="search-input" placeholder="Search for a schedule..." class="sidebar-input">
                    </div>

                    <div class="filter-chips sidebar-chips" id="status-filters">
                        <div class="chip active" data-status="upcoming">Upcoming</div>
                        <div class="chip" data-status="completed">Finished</div>
                        <div class="chip" data-status="cancelled">Cancelled</div>
                    </div>
                </div>

                <div class="sidebar-divider"></div>
                
                <h4 style="margin: 20px 0 10px; color: var(--text-muted); font-size: 12px; text-transform: uppercase;">Schedules</h4>
                <div class="agenda-scroll-container sidebar-list" id="event-list-container">
                    <!-- Event rows injected via JS -->
                </div>
            </div>

            <div class="sidebar-footer">
                <form action="{{ route('logout') }}" method="POST" style="display: flex; justify-content: center; width: 100%;">
                    @csrf
                    <button type="submit" class="logout-btn">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path><polyline points="16 17 21 12 16 7"></polyline><line x1="21" y1="12" x2="9" y2="12"></line></svg>
                        <span>Logout</span>
                    </button>
                </form>
            </div>
        </aside>

        <main class="main-content" id="main-content">
            <header class="dashboard-header">
                <div style="display: flex; align-items: center; gap: 20px;">
                    <button class="hamburger-menu" id="hamburger-menu">
                        <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="var(--primary-blue)" stroke-width="2.5"><line x1="3" y1="12" x2="21" y2="12"></line><line x1="3" y1="6" x2="21" y2="6"></line><line x1="3" y1="18" x2="21" y2="18"></line></svg>
                    </button>
                    <h1>Schedule Management System</h1>
                </div>
                <button class="btn btn-primary" id="btn-add-schedule">+ Add New Schedule</button>
            </header>

            <section class="calendar-center-wrapper">
                <div class="calendar-header-actions glass">
                    <button class="calendar-nav-btn" id="prev-month">&lt;</button>
                    <h2 id="calendar-month-year" style="font-size: 18px; font-weight: 800; color: var(--text-main); min-width: 180px; text-align: center;"></h2>
                    <button class="calendar-nav-btn" id="next-month">&gt;</button>
                </div>

                <div class="calendar-grid-container glass">
                    <div class="calendar-weekdays">
                        <div>Sun</div><div>Mon</div><div>Tue</div><div>Wed</div><div>Thu</div><div>Fri</div><div>Sat</div>
                    </div>
                    <div class="calendar-grid" id="calendar-grid"></div>
                </div>
            </section>
        </main>
    </div>

    <!-- Add/Edit Event Modal -->
    <div class="modal-overlay" id="event-modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 id="modal-title">Add New Schedule</h2>
            </div>
            
            <form id="event-form">
                <input type="hidden" id="event-id">
                
                <div class="form-group">
                    <label for="event-title">Schedule Title</label>
                    <input type="text" id="event-title" required placeholder="e.g. Design Sync">
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="event-date">Start Date</label>
                        <input type="date" id="event-date" name="date" required>
                    </div>
                    <div class="form-group">
                        <label for="event-end-date">End Date (Optional)</label>
                        <input type="date" id="event-end-date" name="end_date">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="event-time">Time</label>
                        <select id="event-time" name="time" required></select>
                    </div>
                </div>
                
                <div class="form-group" id="status-group" style="display: none;">
                    <label for="event-status">Update Status</label>
                    <select id="event-status">
                        <option value="upcoming">🟡 Upcoming</option>
                        <option value="completed">🟢 Completed</option>
                        <option value="cancelled">🔴 Cancelled</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="event-desc">Description (Optional)</label>
                    <textarea id="event-desc" rows="3" placeholder="Add some details..."></textarea>
                </div>
                
                <div style="display: flex; justify-content: flex-end; gap: 15px; margin-top: 30px;">
                    <button type="button" class="btn" style="background: transparent; color: var(--text-muted); box-shadow: none;" onclick="handleCancelModal()">Cancel</button>
                    <button type="submit" class="btn btn-accent" id="save-event-btn">Save Schedule</button>
                </div>
            </form>
        </div>
    </div>

    <!-- View Event Details Modal -->
    <div class="modal-overlay" id="view-modal">
        <div class="modal-content" style="max-width: 600px; padding: 40px;">
            <div style="margin-bottom: 20px;">
                <h2 id="view-title" style="word-break: break-word; font-size: 32px; color: var(--text-main); font-weight: 800; margin-bottom: 10px;">Schedule Title</h2>
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


            
            <div class="view-action-container">
                <button type="button" class="btn btn-outline" id="btn-edit-view" style="width: 100%;">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
                    Edit Details
                </button>
                
                <div class="divider"></div>
                
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <button type="button" class="btn btn-danger" id="btn-cancel-meeting" style="font-size: 14px;">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="4.93" y1="4.93" x2="19.07" y2="19.07"></line></svg>
                        Cancel Schedule
                    </button>
                    <button type="button" class="btn btn-subdued" id="btn-delete-view">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
                        Delete Permanently
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Day Events Modal -->
    <div class="modal-overlay" id="day-events-modal">
        <div class="modal-content" style="max-height: 80vh; overflow-y: auto;">
            <div class="modal-header" style="display: flex; justify-content: space-between; align-items: center;">
                <h2 id="day-events-title">Schedules</h2>
                <button class="btn btn-accent btn-sm" id="btn-add-from-day" style="padding: 8px 16px; font-size: 12px; border-radius: 50px;">+ Add Schedule</button>
            </div>
            <div id="day-events-list"></div>
        </div>
    </div>

        </main>
    </div>
@endsection

@push('scripts')
<script src="{{ asset('script.js') }}"></script>
@endpush
