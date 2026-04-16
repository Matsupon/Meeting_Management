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
        max-width: 1600px;
        margin: 0 auto;
        overflow: hidden;
    }

    /* Larger screens typography enhancement */
    @media (min-width: 1401px) {
        :root {
            --font-size-base: 18px;
            --font-size-lg: 22px;
            --font-size-xl: 32px;
        }
        .bento-number { font-size: 80px !important; }
        .modal-content { max-width: 750px !important; }
        .calendar-day { font-size: 20px !important; }
        .event-row h3 { font-size: 22px !important; }
        .event-row .status-pill { font-size: 16px !important; }
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
        top: 6px;
        right: 10px;
        font-size: 32px;
        line-height: 1;
        color: var(--text-muted);
        cursor: pointer;
        transition: color 0.2s;
        background: transparent;
        border: none;
        padding: 4px;
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
        padding-top: 6vh;
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
    .modal-overlay.active .modal-content { transform: scale(1) translateY(0); pointer-events: auto; }
    .modal-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; }
    .modal-header h2 { font-size: var(--font-size-xl); color: var(--primary-blue); font-weight: 800; }
    .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; }
    .badge-ongoing { background: #dbeafe; color: #1d4ed8; }

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
        position: fixed; top: -120px; left: 50%; transform: translateX(-50%);
        background: var(--card-bg);
        padding: 20px 30px; border-radius: var(--border-radius-md);
        box-shadow: 0 10px 25px rgba(0,0,0,0.1); z-index: 60000;
        display: flex; align-items: center; gap: 15px;
        transition: top 0.5s cubic-bezier(0.4, 0, 0.2, 1);
        cursor: pointer;
        position: fixed;
        min-width: 320px;
        max-width: 520px;
    }
    .reminder-alert.show { top: 30px; }
    .reminder-icon { color: var(--accent); }
    .reminder-text strong { display: block; font-size: var(--font-size-lg); color: var(--text-main); }
    .reminder-text span { color: var(--text-muted); font-size: var(--font-size-base); }

    .reminder-item:hover { background: #f8fafc; }

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

    .color-picker-wrap {
        position: relative;
        width: 100%;
    }
    .color-picker-trigger {
        width: 100%;
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 12px 18px;
        font-size: var(--font-size-base);
        font-weight: 600;
        border: 2px solid #e2e8f0;
        border-radius: var(--border-radius-md);
        background: #f8fafc;
        color: var(--text-main);
        cursor: pointer;
        text-align: left;
    }
    .color-picker-trigger:hover {
        border-color: var(--primary-blue);
        background: #fff;
    }
    .color-picker-trigger .color-picker-swatch {
        width: 24px;
        height: 24px;
        border-radius: 8px;
        border: 1px solid rgba(0,0,0,0.1);
        flex-shrink: 0;
        margin-left: 12px;
    }
    .color-picker-dropdown {
        display: none;
        position: absolute;
        top: 100%;
        left: 0;
        right: 0;
        margin-top: 6px;
        background: #fff;
        border: 2px solid #e2e8f0;
        border-radius: var(--border-radius-md);
        box-shadow: 0 10px 25px rgba(0,0,0,0.1);
        z-index: 100;
        max-height: 280px;
        overflow-y: auto;
    }
    .color-picker-dropdown.open {
        display: block;
    }
    .color-picker-option {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 12px 18px;
        cursor: pointer;
        font-weight: 600;
        font-size: var(--font-size-base);
        transition: background 0.15s;
    }
    .color-picker-option:hover {
        background: #f1f5f9;
    }
    .color-picker-option .color-picker-swatch {
        width: 22px;
        height: 22px;
        border-radius: 6px;
        border: 1px solid rgba(0,0,0,0.1);
        flex-shrink: 0;
        margin-left: 12px;
    }

    /* Summary Dropdown Menu */
    .summary-month-option {
        padding: 12px 16px;
        cursor: pointer;
        font-weight: 600;
        font-size: 14px;
        color: var(--text-main);
        transition: background 0.15s;
    }
    .summary-month-option:hover {
        background: #f1f5f9;
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
        <button type="button" id="reminder-close" class="reminder-close" aria-label="Dismiss reminder">&times;</button>
    </div>

    <div class="dashboard-viewport">
        <!-- Sidebar -->
        <aside class="sidebar-main glass" id="sidebar-main">
            <div class="sidebar-content">
                <div class="legend-section">
                    <h4>LEGEND:</h4>
                    <div class="legend-item"><span class="legend-color amber"></span> 1-3 days before the schedule</div>
                    <div class="legend-item"><span class="legend-color yellow"></span> 4-6 days before the schedule</div>
                    <div class="legend-item"><span class="legend-color green"></span> more than 6 days before the schedule</div>
                    <div class="legend-item"><span class="legend-color blue"></span> Ongoing schedule</div>
                    <div class="legend-item"><span class="legend-color gray"></span> Finished schedule</div>
                </div>

                <div class="sidebar-search-filters">
                    <div class="form-group">
                        <input type="text" id="search-input" placeholder="Search for a schedule..." class="sidebar-input">
                    </div>

                    <div class="filter-chips sidebar-chips" id="status-filters">
                        <div class="chip active" data-status="upcoming">Upcoming</div>
                        <div class="chip" data-status="completed">Finished</div>
                        <div class="chip" data-status="cancelled">Cancelled</div>
                        
                        <select id="month-filter" class="sidebar-month-filter">
                            <option value="all">All Months</option>
                            <option value="0">January</option>
                            <option value="1">February</option>
                            <option value="2">March</option>
                            <option value="3">April</option>
                            <option value="4">May</option>
                            <option value="5">June</option>
                            <option value="6">July</option>
                            <option value="7">August</option>
                            <option value="8">September</option>
                            <option value="9">October</option>
                            <option value="10">November</option>
                            <option value="11">December</option>
                        </select>
                    </div>
                </div>

                <div class="sidebar-divider"></div>
                
                <h4 style="margin: 10px 0 10px 10px; color: var(--text-muted); font-size: 12px; text-transform: uppercase;">Schedules</h4>
                <div class="agenda-scroll-container sidebar-list" id="event-list-container">
                    <!-- Event rows injected via JS -->
                </div>
            </div>
        </aside>

        <main class="main-content" id="main-content">
            <header class="dashboard-header">
                <div style="display: flex; align-items: center; gap: 20px;">
                    <button class="hamburger-menu" id="hamburger-menu" aria-label="Toggle sidebar menu">
                        <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="var(--primary-blue)" stroke-width="2.5"><line x1="3" y1="12" x2="21" y2="12"></line><line x1="3" y1="6" x2="21" y2="6"></line><line x1="3" y1="18" x2="21" y2="18"></line></svg>
                    </button>
                    <h1>Schedule Management System</h1>
                </div>
                
                <div style="display: flex; align-items: center; gap: 20px;">
                    <!-- Create Summary Dropdown -->
                    <div class="dropdown" style="position: relative;">
                        <button class="btn btn-primary" id="btn-create-summary" style="display: flex; align-items: center; gap: 8px;">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>
                            Create Summary
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"></polyline></svg>
                        </button>
                        <div id="summary-dropdown-menu" style="display: none; position: absolute; top: 100%; left: 0; margin-top: 8px; background: white; border-radius: 12px; box-shadow: 0 10px 40px rgba(0,0,0,0.15); min-width: 200px; z-index: 1000; overflow: hidden;">
                            <div style="padding: 12px 16px; font-size: 13px; font-weight: 700; color: var(--text-muted); text-transform: uppercase; border-bottom: 1px solid #e2e8f0;">Select Period</div>
                            <div class="summary-month-option" data-month="0">January</div>
                            <div class="summary-month-option" data-month="1">February</div>
                            <div class="summary-month-option" data-month="2">March</div>
                            <div class="summary-month-option" data-month="3">April</div>
                            <div class="summary-month-option" data-month="4">May</div>
                            <div class="summary-month-option" data-month="5">June</div>
                            <div class="summary-month-option" data-month="6">July</div>
                            <div class="summary-month-option" data-month="7">August</div>
                            <div class="summary-month-option" data-month="8">September</div>
                            <div class="summary-month-option" data-month="9">October</div>
                            <div class="summary-month-option" data-month="10">November</div>
                            <div class="summary-month-option" data-month="11">December</div>
                            <div style="border-top: 2px solid #e2e8f0;"></div>
                            <div class="summary-month-option" data-month="all" style="font-weight: 700; color: var(--primary-blue);">All Year</div>
                        </div>
                    </div>
                    
                    <button class="btn btn-primary" id="btn-add-schedule">+ Add New Schedule</button>
                    
                    <button class="notification-btn" id="btn-notification" title="Notifications" aria-label="View notifications">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="var(--primary-blue)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path>
                            <path d="M13.73 21a2 2 0 0 1-3.46 0"></path>
                        </svg>
                        <span class="notification-badge" id="notification-badge" style="display: none;"></span>
                    </button>
                    
                    <div class="profile-container">
                        <button class="profile-btn" id="profile-btn">
                            @if($user->profile_picture)
                                <img src="/storage/{{ $user->profile_picture }}" alt="Profile" style="width:32px; height:32px; border-radius:50%; object-fit:cover;">
                            @else
                                <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                            @endif
                        </button>
                        <div class="profile-dropdown" id="profile-dropdown">
                            <div style="padding: 10px; font-weight: bold; color: var(--text-main); border-bottom: 1px solid #e0e0e0;">{{ $user->display_name }}</div>
                            <a href="{{ route('profile') }}" style="display: block; padding: 10px; color: var(--text-main); text-decoration: none;" onmouseover="this.style.background='#f0f0f0'" onmouseout="this.style.background='transparent'">My Profile</a>
                            <div style="height: 1px; background: #e0e0e0; margin: 5px 0;"></div>
                            <form action="{{ route('logout') }}" method="POST">
                                @csrf
                                <button type="submit" class="dropdown-logout-btn" style="width: 100%; border: none; background: transparent; padding: 10px; cursor: pointer; text-align: left;">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path><polyline points="16 17 21 12 16 7"></polyline><line x1="21" y1="12" x2="9" y2="12"></line></svg>
                                    <span>Logout</span>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
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
    <div class="modal-overlay" id="event-modal" role="dialog" aria-modal="true" aria-labelledby="modal-title">
        <div class="modal-content">
            <div class="modal-header">
                <h2 id="modal-title">Add New Schedule</h2>
                <div id="edit-context-date-label" style="font-size: 16px; font-weight: 800; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.5px; display: none;"></div>
            </div>
            
            <form id="event-form">
                <input type="hidden" id="event-id">
                <input type="hidden" id="event-edit-context-date">
                
                <div class="form-group">
                    <label for="event-title">
                        Title of Schedule
                    </label>
                    <input type="text" id="event-title" required placeholder="e.g. Training">
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 8px;">
                            <label for="event-location" style="margin-bottom: 0;">
                                Location
                            </label>
                            <div id="day-location-container" style="display: none;">
                                <select id="day-location-selector" class="sidebar-input" style="font-size: 1.05rem; padding: 8px 12px; width: auto; min-width: 220px; border-radius: 12px; font-weight: 700; border: 2px solid var(--primary-blue);">
                                    <option value="general">Apply to all days</option>
                                </select>
                            </div>
                        </div>
                        <input type="text" id="event-location" name="location" placeholder="e.g. Surigao City" style="font-size: 1.25rem; padding: 15px; font-weight: 600;">
                    </div>
                    <div class="form-group">
                        <label for="event-classification">
                            Classification of Clients
                        </label>
                        <input type="text" id="event-classification" name="classification" placeholder="e.g. Women, Youth, IP, Farmers">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="event-date">Date Started</label>
                        <input type="text" id="event-date" name="date" required placeholder="MM/DD/YYYY">
                    </div>
                    <div class="form-group">
                        <label for="event-end-date">Date Ended (optional)</label>
                        <input type="text" id="event-end-date" name="end_date" placeholder="MM/DD/YYYY">
                    </div>
                </div>


                
                <div class="form-group" id="status-group" style="display: none;">
                    <label for="event-status">Update Status</label>
                    <select id="event-status">
                        <option value="upcoming">Upcoming</option>
                        <option value="completed">Finished</option>
                        <option value="cancelled">Cancelled</option>
                    </select>
                </div>



                <div class="form-group">
                    <label for="event-desc">Description (Optional)</label>
                    <textarea id="event-desc" rows="3" placeholder="Add some details..."></textarea>
                </div>
                
                <div style="display: flex; justify-content: flex-end; gap: 15px; margin-top: 30px;">
                    <button type="button" class="btn" id="btn-cancel-event-modal" style="background: transparent; color: var(--text-muted); box-shadow: none;">Cancel</button>
                    <button type="submit" class="btn btn-accent" id="save-event-btn">Save Schedule</button>
                </div>
            </form>
        </div>
    </div>

    <!-- View Event Details Modal -->
    <div class="modal-overlay" id="view-modal" style="z-index: 100000;" role="dialog" aria-modal="true" aria-labelledby="view-title">
        <div class="modal-content" style="max-width: 600px; padding: 40px; position: relative;">
            <div id="view-top-date" style="text-align: center; font-size: 20px; font-weight: 800; color: var(--primary-blue); text-transform: uppercase; margin-bottom: 12px; width: 100%; letter-spacing: 0.5px;"></div>
            <div style="margin-bottom: 20px; display: flex; justify-content: space-between; align-items: flex-start; gap: 15px; flex-wrap: wrap;">
                <div style="display:flex; align-items:flex-start; gap:10px; min-width: 0; flex: 1;">
                    <h2 id="view-title" style="word-break: break-word; font-size: 32px; color: var(--text-main); font-weight: 800; margin-bottom: 10px; min-width:0;">Schedule Title</h2>
                    <input type="text" id="view-title-input" style="display:none; font-size: 26px; font-weight: 800; color: var(--text-main); border: 2px solid var(--primary-blue); border-radius: 10px; padding: 6px 12px; width: 100%; background: #fff;">
                </div>
                <span id="view-status-badge" class="status-badge badge-upcoming" style="font-size: 19px; font-weight: 700; padding: 6px 12px; border-radius: 50px; white-space: nowrap; align-self: flex-start;">🟡 Upcoming</span>
            </div>
            
            <div style="margin-bottom: 25px; display: flex; flex-direction: column; gap: 16px;">
                <!-- Title edit (inline) lives in header -->
                <div id="view-title-actions" style="display:none; justify-content:flex-end; gap:10px; margin-top:-8px;">
                    <button type="button" class="btn btn-subdued" data-action="cancel" data-field="title">Cancel</button>
                    <button type="button" class="btn btn-accent" data-action="save" data-field="title">Save</button>
                </div>

                <div style="display: flex; flex-wrap: wrap; gap: 20px;">
                    <div style="flex: 1 1 220px; display: flex; flex-direction: column; gap: 4px; position: relative;" data-view-field="location">
                        <span style="font-size: 13px; font-weight: 700; text-transform: uppercase; color: var(--text-muted);">Location</span>
                        <div style="display: flex; align-items: center; gap: 8px; font-size: 16px; color: var(--text-main); min-height: 24px;">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="var(--primary-blue)" stroke-width="2"><path d="M21 10c0 7-9 11-9 11S3 17 3 10a9 9 0 1 1 18 0z"></path><circle cx="12" cy="10" r="3"></circle></svg>
                            <span id="view-location">Location</span>
                            <input type="text" id="view-location-input" style="display:none; flex: 1; padding: 8px 10px; border: 2px solid #e2e8f0; border-radius: 10px; background:#fff;">
                        </div>
                        <div id="view-location-actions" style="display:none; justify-content:flex-end; gap:10px; margin-top:10px;">
                            <button type="button" class="btn btn-subdued" data-action="cancel" data-field="location">Cancel</button>
                            <button type="button" class="btn btn-accent" data-action="save" data-field="location">Save</button>
                        </div>
                    </div>
                    <div style="flex: 1 1 220px; display: flex; flex-direction: column; gap: 4px; position: relative;" data-view-field="classification">
                        <span style="font-size: 13px; font-weight: 700; text-transform: uppercase; color: var(--text-muted);">Classification of Clients</span>
                        <div style="display: flex; align-items: center; gap: 8px; font-size: 16px; color: var(--text-main); min-height: 24px;">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="var(--primary-blue)" stroke-width="2"><circle cx="12" cy="7" r="4"></circle><path d="M5.5 21a7 7 0 0 1 13 0"></path></svg>
                            <span id="view-classification">Classification of Clients</span>
                            <input type="text" id="view-classification-input" style="display:none; flex: 1; padding: 8px 10px; border: 2px solid #e2e8f0; border-radius: 10px; background:#fff;">
                        </div>
                        <div id="view-classification-actions" style="display:none; justify-content:flex-end; gap:10px; margin-top:10px;">
                            <button type="button" class="btn btn-subdued" data-action="cancel" data-field="classification">Cancel</button>
                            <button type="button" class="btn btn-accent" data-action="save" data-field="classification">Save</button>
                        </div>
                    </div>
                </div>

                <div style="display: flex; flex-direction: column; gap: 4px; position: relative;" data-view-field="date">
                    <span style="font-size: 13px; font-weight: 700; text-transform: uppercase; color: var(--text-muted);">Date</span>
                    <div style="display: flex; align-items: center; gap: 8px; font-size: 16px; color: var(--text-main); min-height: 24px;">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="var(--primary-blue)" stroke-width="2.5"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
                        <span id="view-date">Date Started – Date Ended</span>
                    </div>
                    <div id="view-date-edit" style="display:none; gap:12px; margin-top:10px; flex-wrap:wrap;">
                        <div style="flex:1 1 180px;">
                            <div style="font-size: 12px; font-weight: 800; color: var(--text-muted); margin-bottom: 6px;">Date Started</div>
                            <input type="date" id="view-date-start-input" style="width:100%; padding: 10px 12px; border: 2px solid #e2e8f0; border-radius: 10px; background:#fff;">
                        </div>
                        <div style="flex:1 1 180px;">
                            <div style="font-size: 12px; font-weight: 800; color: var(--text-muted); margin-bottom: 6px;">Date Ended (optional)</div>
                            <input type="date" id="view-date-end-input" style="width:100%; padding: 10px 12px; border: 2px solid #e2e8f0; border-radius: 10px; background:#fff;">
                        </div>
                    </div>
                    <div id="view-date-actions" style="display:none; justify-content:flex-end; gap:10px; margin-top:10px;">
                        <button type="button" class="btn btn-subdued" data-action="cancel" data-field="date">Cancel</button>
                        <button type="button" class="btn btn-accent" data-action="save" data-field="date">Save</button>
                    </div>
                </div>

                <div style="display: flex; flex-direction: column; gap: 6px; position: relative;" data-view-field="color">
                    <span style="font-size: 13px; font-weight: 700; text-transform: uppercase; color: var(--text-muted);">Schedule Color</span>
                    <div style="display:flex; align-items:center; gap:10px; min-height: 24px;">
                        <span id="view-color-name" style="font-weight:700; color: var(--text-main);">Color</span>
                        <span id="view-color-swatch" style="width:28px; height:18px; border-radius:6px; border:1px solid rgba(0,0,0,0.12); background:#93c5fd;"></span>
                    </div>
                    <div id="view-color-palette" style="display:none; margin-top:10px; gap:10px; flex-wrap:wrap;">
                        @foreach ([
                            ['#93c5fd','Soft Blue'],
                            ['#fdba74','Peach'],
                            ['#86efac','Sage'],
                            ['#fcd34d','Amber'],
                            ['#c4b5fd','Warm Violet'],
                            ['#fb923c','Orange'],
                            ['#fef3c7','Cream'],
                            ['#f9a8d4','Rose'],
                            ['#5eead4','Mint'],
                            ['#d1d5db','Warm Gray']
                        ] as $c)
                            <button type="button" class="view-color-rect" data-hex="{{ $c[0] }}" title="{{ $c[1] }}" style="width:44px; height:26px; border-radius:8px; border:2px solid rgba(15,23,42,0.12); background: {{ $c[0] }}; cursor:pointer;"></button>
                        @endforeach
                    </div>
                    <div id="view-color-actions" style="display:none; justify-content:flex-end; gap:10px; margin-top:10px;">
                        <button type="button" class="btn btn-subdued" data-action="cancel" data-field="color">Cancel</button>
                        <button type="button" class="btn btn-accent" data-action="save" data-field="color">Save</button>
                    </div>
                </div>
            </div>
            
            <div style="margin-bottom: 25px; position: relative; background: #f8fafc; padding: 20px; border-radius: var(--border-radius-md); border: 2px solid #e2e8f0;" id="view-desc-container">
                <div style="font-size: 14px; color: var(--text-muted); text-transform: uppercase; font-weight: 800; margin-bottom: 8px;">Details</div>
                <p id="view-desc" style="color: var(--text-main); font-size: 18px; white-space: pre-wrap; line-height: 1.6;"></p>
                <textarea id="view-details-input" rows="4" style="display:none; width:100%; padding: 12px 14px; border: 2px solid #e2e8f0; border-radius: 12px; background:#fff; font-size: 16px; line-height: 1.6;"></textarea>
                <div id="view-details-actions" style="display:none; justify-content:flex-end; gap:10px; margin-top:10px;">
                    <button type="button" class="btn btn-subdued" data-action="cancel" data-field="details">Cancel</button>
                    <button type="button" class="btn btn-accent" data-action="save" data-field="details">Save</button>
                </div>
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
                    <button type="button" class="btn" id="btn-delete-view" style="background: #a9b3c0ff; color: white; box-shadow: 0 4px 14px 0 #a9b3c0ff;" onmouseover="this.style.background='#334155'" onmouseout="this.style.background='#475569'">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
                        Delete Permanently
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Day Events Modal -->
    <div class="modal-overlay" id="day-events-modal" role="dialog" aria-modal="true" aria-labelledby="day-events-title">
        <div class="modal-content" style="max-height: 80vh; overflow-y: auto;">
            <div class="modal-header" style="display: flex; justify-content: space-between; align-items: center;">
                <h2 id="day-events-title" style="font-size: 29px; margin: 0;">Schedules</h2>
                <button class="btn btn-accent btn-sm" id="btn-add-from-day" style="padding: 8px 16px; font-size: 17px; border-radius: 50px;">+ Add Schedule</button>
            </div>
            <div id="day-events-list"></div>
        </div>
    </div>

    <!-- Custom Alert Modal -->
    <div class="modal-overlay" id="custom-alert-modal" style="z-index: 99999;" role="alertdialog" aria-modal="true" aria-labelledby="custom-alert-title">
        <div class="modal-content" style="max-width: 400px; padding: 40px 30px; text-align: center; border-radius: 24px; background: linear-gradient(180deg, #ffffff 0%, #f8fafc 100%);">
            <div style="width: 80px; height: 80px; background: #e0e7ff; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 20px; box-shadow: 0 10px 25px rgba(79, 70, 229, 0.2);">
                <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="#4f46e5" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path><path d="M13.73 21a2 2 0 0 1-3.46 0"></path></svg>
            </div>
            <h2 id="custom-alert-title" style="font-size: 24px; font-weight: 800; color: #1e293b; margin-bottom: 20px; letter-spacing: -0.5px;">Upcoming Reminders</h2>
            <div id="custom-alert-list" style="margin-bottom: 30px; display: flex; flex-direction: column; gap: 10px; max-height: 40vh; overflow-y: auto; text-align: left; padding: 0 5px;"></div>
            <button class="btn btn-accent" id="custom-alert-ok" style="width: 100%; padding: 14px; font-size: 16px; border-radius: 12px; box-shadow: 0 8px 20px rgba(79, 70, 229, 0.3);">Okay</button>
        </div>
    </div>

    <!-- Edit single day (full details) within a schedule -->
    <div class="modal-overlay" id="edit-day-modal">
        <div class="modal-content" style="max-width: 480px;">
            <div class="modal-header">
                <h2 id="edit-day-modal-title">Edit day details</h2>
            </div>
            <input type="hidden" id="edit-day-event-id">
            <input type="hidden" id="edit-day-date">
            <div class="form-group">
                <label for="edit-day-title">Schedule Title</label>
                <input type="text" id="edit-day-title" placeholder="Title for this day">
            </div>
            <div class="form-group">
                <label for="edit-day-location">Location</label>
                <input type="text" id="edit-day-location" placeholder="Location for this day">
            </div>
            <div class="form-group">
                <label for="edit-day-classification">Classification of Clients</label>
                <input type="text" id="edit-day-classification" placeholder="Classification for this day">
            </div>
            <div class="form-group">
                <label for="edit-day-desc">Details (optional)</label>
                <textarea id="edit-day-desc" rows="3" placeholder="Details for this day"></textarea>
            </div>
            <div style="display: flex; justify-content: flex-end; gap: 15px; margin-top: 20px;">
                <button type="button" class="btn" style="background: transparent; color: var(--text-muted);" onclick="closeModal('edit-day-modal')">Cancel</button>
                <button type="button" class="btn btn-accent" id="edit-day-save">Save</button>
            </div>
        </div>
    </div>

        </main>
    </div>
@endsection

@push('scripts')
@vite(['resources/js/schedule.js'])
@endpush
