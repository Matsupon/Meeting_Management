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
        position: fixed; top: -120px; left: 50%; transform: translateX(-50%);
        background: var(--card-bg);
        padding: 20px 30px; border-radius: var(--border-radius-md);
        box-shadow: 0 10px 25px rgba(0,0,0,0.1); z-index: 1100;
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
                
                <h4 style="margin: 20px 0 10px; color: var(--text-muted); font-size: 12px; text-transform: uppercase;">Schedules</h4>
                <div class="agenda-scroll-container sidebar-list" id="event-list-container">
                    <!-- Event rows injected via JS -->
                </div>
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
                
                <div style="display: flex; align-items: center; gap: 20px;">
                    <button class="btn btn-primary" id="btn-add-schedule">+ Add New Schedule</button>
                    
                    <div class="profile-container">
                        <button class="profile-btn" id="profile-btn">
                            <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                        </button>
                        <div class="profile-dropdown" id="profile-dropdown">
                            <form action="{{ route('logout') }}" method="POST">
                                @csrf
                                <button type="submit" class="dropdown-logout-btn">
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
    <div class="modal-overlay" id="event-modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 id="modal-title">Add New Schedule</h2>
            </div>
            
            <form id="event-form">
                <input type="hidden" id="event-id">
                
                <div class="form-group">
                    <label for="event-title">
                        Title of Schedule
                    </label>
                    <input type="text" id="event-title" required placeholder="e.g. Quarterly Planning Meeting">
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="event-location">
                            Location
                        </label>
                        <input type="text" id="event-location" name="location" placeholder="e.g. Manila Office, Zoom, Client Site">
                    </div>
                    <div class="form-group">
                        <label for="event-classification">
                            Classification of Clients
                        </label>
                        <input type="text" id="event-classification" name="classification" placeholder="e.g. New Client, Existing Client">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="event-date">Date Started</label>
                        <input type="date" id="event-date" name="date" required>
                    </div>
                    <div class="form-group">
                        <label for="event-end-date">Date Ended</label>
                        <input type="date" id="event-end-date" name="end_date">
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
                    <label>Schedule Color</label>
                    <input type="hidden" id="event-color-value" name="event-color" value="#93c5fd">
                    <div class="color-picker-wrap" id="event-color-picker-wrap">
                        <button type="button" class="color-picker-trigger" id="event-color-trigger" aria-haspopup="listbox" aria-expanded="false">
                            <span class="color-picker-trigger-name" id="event-color-trigger-name">Soft Blue</span>
                            <span class="color-picker-swatch" id="event-color-trigger-swatch" style="background:#93c5fd;"></span>
                        </button>
                        <div class="color-picker-dropdown" id="event-color-dropdown" role="listbox" aria-hidden="true">
                            <div class="color-picker-option" role="option" tabindex="0" data-hex="#93c5fd" data-name="Soft Blue"><span class="color-picker-option-name" style="color:#1e3a8a">Soft Blue</span><span class="color-picker-swatch" style="background:#93c5fd;"></span></div>
                            <div class="color-picker-option" role="option" tabindex="0" data-hex="#fdba74" data-name="Peach"><span class="color-picker-option-name" style="color:#9a3412">Peach</span><span class="color-picker-swatch" style="background:#fdba74;"></span></div>
                            <div class="color-picker-option" role="option" tabindex="0" data-hex="#86efac" data-name="Sage"><span class="color-picker-option-name" style="color:#14532d">Sage</span><span class="color-picker-swatch" style="background:#86efac;"></span></div>
                            <div class="color-picker-option" role="option" tabindex="0" data-hex="#fcd34d" data-name="Amber"><span class="color-picker-option-name" style="color:#78350f">Amber</span><span class="color-picker-swatch" style="background:#fcd34d;"></span></div>
                            <div class="color-picker-option" role="option" tabindex="0" data-hex="#c4b5fd" data-name="Warm Violet"><span class="color-picker-option-name" style="color:#4c1d95">Warm Violet</span><span class="color-picker-swatch" style="background:#c4b5fd;"></span></div>
                            <div class="color-picker-option" role="option" tabindex="0" data-hex="#fb923c" data-name="Orange"><span class="color-picker-option-name" style="color:#9a3412">Orange</span><span class="color-picker-swatch" style="background:#fb923c;"></span></div>
                            <div class="color-picker-option" role="option" tabindex="0" data-hex="#fef3c7" data-name="Cream"><span class="color-picker-option-name" style="color:#78350f">Cream</span><span class="color-picker-swatch" style="background:#fef3c7;"></span></div>
                            <div class="color-picker-option" role="option" tabindex="0" data-hex="#f9a8d4" data-name="Rose"><span class="color-picker-option-name" style="color:#831843">Rose</span><span class="color-picker-swatch" style="background:#f9a8d4;"></span></div>
                            <div class="color-picker-option" role="option" tabindex="0" data-hex="#5eead4" data-name="Mint"><span class="color-picker-option-name" style="color:#134e4a">Mint</span><span class="color-picker-swatch" style="background:#5eead4;"></span></div>
                            <div class="color-picker-option" role="option" tabindex="0" data-hex="#d1d5db" data-name="Warm Gray"><span class="color-picker-option-name" style="color:#1f2937">Warm Gray</span><span class="color-picker-swatch" style="background:#d1d5db;"></span></div>
                        </div>
                    </div>
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
            <div style="margin-bottom: 20px; display: flex; justify-content: space-between; align-items: flex-start; gap: 15px; flex-wrap: wrap;">
                <div style="display:flex; align-items:flex-start; gap:10px; min-width: 0; flex: 1;">
                    <h2 id="view-title" style="word-break: break-word; font-size: 32px; color: var(--text-main); font-weight: 800; margin-bottom: 10px; min-width:0;">Schedule Title</h2>
                    <button type="button" class="view-inline-edit-btn" data-field="title" aria-label="Edit title" style="display:none; margin-top: 6px; background:transparent; border:none; cursor:pointer; padding:6px; flex-shrink:0;">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="var(--primary-blue)" stroke-width="2"><path d="M12 20h9"></path><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4 12.5-12.5z"></path></svg>
                    </button>
                </div>
                <span id="view-status-badge" class="status-badge badge-upcoming" style="font-size: 14px; font-weight: 700; padding: 6px 12px; border-radius: 50px; white-space: nowrap; align-self: flex-start;">🟡 Upcoming</span>
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
                        <button type="button" class="view-inline-edit-btn" data-field="location" aria-label="Edit location" style="position:absolute; right:0; top:0; background:transparent; border:none; cursor:pointer; padding:6px;">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="var(--primary-blue)" stroke-width="2"><path d="M12 20h9"></path><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4 12.5-12.5z"></path></svg>
                        </button>
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
                        <button type="button" class="view-inline-edit-btn" data-field="classification" aria-label="Edit classification" style="position:absolute; right:0; top:0; background:transparent; border:none; cursor:pointer; padding:6px;">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="var(--primary-blue)" stroke-width="2"><path d="M12 20h9"></path><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4 12.5-12.5z"></path></svg>
                        </button>
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
                    <button type="button" class="view-inline-edit-btn" data-field="date" aria-label="Edit date" style="position:absolute; right:0; top:0; background:transparent; border:none; cursor:pointer; padding:6px;">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="var(--primary-blue)" stroke-width="2"><path d="M12 20h9"></path><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4 12.5-12.5z"></path></svg>
                    </button>
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
                    <button type="button" class="view-inline-edit-btn" data-field="color" aria-label="Edit color" style="position:absolute; right:0; top:0; background:transparent; border:none; cursor:pointer; padding:6px;">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="var(--primary-blue)" stroke-width="2"><path d="M12 20h9"></path><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4 12.5-12.5z"></path></svg>
                    </button>
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
            
            <div style="margin-bottom: 25px; position: relative; background: #f8fafc; padding: 20px; border-radius: var(--border-radius-md); border: 2px solid #e2e8f0; display: none;" id="view-desc-container">
                <div style="font-size: 14px; color: var(--text-muted); text-transform: uppercase; font-weight: 800; margin-bottom: 8px;">Details</div>
                <p id="view-desc" style="color: var(--text-main); font-size: 18px; white-space: pre-wrap; line-height: 1.6;"></p>
                <textarea id="view-details-input" rows="4" style="display:none; width:100%; padding: 12px 14px; border: 2px solid #e2e8f0; border-radius: 12px; background:#fff; font-size: 16px; line-height: 1.6;"></textarea>
                <button type="button" class="view-inline-edit-btn" data-field="details" aria-label="Edit details" style="display:none; position:absolute; right: 14px; margin-top: -42px; background:transparent; border:none; cursor:pointer; padding:6px;">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="var(--primary-blue)" stroke-width="2"><path d="M12 20h9"></path><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4 12.5-12.5z"></path></svg>
                </button>
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
<script src="{{ asset('script.js') }}"></script>
@endpush
