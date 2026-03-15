@extends('layouts.app')

@section('title', 'Profile - Schedule Management System')

@push('styles')
<style>
    .profile-section {
        padding: 20px;
        background: var(--card-bg);
        border-radius: var(--border-radius-lg);
        box-shadow: var(--box-shadow);
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: flex-start;
        overflow-y: auto;
        max-height: 100%;
    }

    .profile-form-wrapper {
        width: 100%;
        max-width: 550px;
        display: flex;
        flex-direction: column;
        align-items: center;
        padding: 20px 0;
    }

    .profile-picture-container {
        text-align: center;
        margin-bottom: 30px;
        position: relative;
    }

    .profile-pic-wrapper {
        position: relative;
        display: inline-block;
    }

    .profile-pic-wrapper img,
    .profile-pic-wrapper svg {
        width: 120px;
        height: 120px;
        border-radius: 50%;
        object-fit: cover;
        border: 3px solid #e2e8f0;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        padding: 8px;
    }

    .edit-pic-btn {
        position: absolute;
        bottom: 0;
        right: 0;
        background: transparent;
        border: none;
        border-radius: 50%;
        width: 32px;
        height: 32px;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        box-shadow: 0 2px 4px rgba(0,0,0,0.2);
        transition: transform 0.2s;
        padding: 4px;
    }

    .edit-pic-btn:hover {
        transform: scale(1.15);
        background: rgba(79, 70, 229, 0.1);
    }

    .edit-pic-btn svg {
        width: 100%;
        height: 100%;
        stroke: var(--primary-blue);
    }

    .modal-overlay {
        position: fixed;
        top: 0; left: 0; width: 100vw; height: 100vh;
        background: rgba(15, 23, 42, 0.5);
        backdrop-filter: blur(8px);
        -webkit-backdrop-filter: blur(8px);
        display: flex;
        align-items: center; justify-content: center;
        z-index: 3000;
        opacity: 0; pointer-events: none;
        transition: opacity 0.3s ease;
    }
    .modal-overlay.active { opacity: 1; pointer-events: auto; }
    .modal-content {
        background: var(--card-bg);
        padding: 40px;
        border-radius: var(--border-radius-lg);
        width: 100%; max-width: 450px;
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.3);
        transform: scale(0.95) translateY(20px);
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .modal-overlay.active .modal-content { transform: scale(1) translateY(0); }
    .modal-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 25px;
    }
    .modal-header h2 {
        font-size: var(--font-size-lg);
        color: var(--primary-blue);
        margin: 0;
    }
    .modal-close {
        background: transparent;
        border: none;
        font-size: 28px;
        cursor: pointer;
        color: var(--text-muted);
        line-height: 1;
    }
    .modal-close:hover { color: var(--text-main); }

    .form-group {
        margin-bottom: 20px;
    }

    .form-group label {
        display: block;
        margin-bottom: 8px;
        font-weight: 600;
        color: var(--text-main);
    }

    .form-group input {
        width: 100%;
        padding: 12px 15px;
        border: 2px solid #e2e8f0;
        border-radius: 10px;
        background: #fff;
        font-size: 15px;
        transition: border-color 0.2s;
    }

    .form-group input:focus {
        outline: none;
        border-color: var(--primary-blue);
    }

    .btn {
        padding: 12px 24px;
        border: none;
        border-radius: 10px;
        cursor: pointer;
        font-weight: 600;
        font-size: 15px;
        transition: all 0.2s;
    }

    .btn-primary {
        background: var(--primary-blue);
        color: white;
        box-shadow: 0 4px 14px rgba(37, 99, 235, 0.3);
    }

    .btn-primary:hover {
        background: #1d4ed8;
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(37, 99, 235, 0.4);
    }

    /* Color Picker - matches dashboard exactly */
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
    .color-picker-dropdown.open { display: block; }
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
    .color-picker-option:hover { background: #f1f5f9; }
    .color-picker-option .color-picker-swatch {
        width: 22px;
        height: 22px;
        border-radius: 6px;
        border: 1px solid rgba(0,0,0,0.1);
        flex-shrink: 0;
        margin-left: 12px;
    }

    /* Form row - two column layout */
    .form-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 15px;
    }
    @media (max-width: 600px) { .form-row { grid-template-columns: 1fr; } }

    /* Button variants */
    .btn-accent {
        background: var(--primary-blue);
        color: #fff;
        box-shadow: 0 4px 14px rgba(37,99,235,0.3);
    }
    .btn-accent:hover {
        background: #1d4ed8;
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(37,99,235,0.4);
    }
    .btn-outline {
        background: transparent;
        border: 2px solid var(--primary-blue);
        color: var(--primary-blue);
    }
    .btn-outline:hover { background: rgba(37,99,235,0.06); }
    .btn-danger {
        background: #ef4444;
        color: #fff;
        box-shadow: 0 4px 14px rgba(239,68,68,0.3);
    }
    .btn-danger:hover { background: #dc2626; }
    .btn-subdued {
        background: #f1f5f9;
        color: var(--text-main);
        border: 2px solid #e2e8f0;
    }

    /* Status badges */
    .status-badge { border-radius: 50px; padding: 4px 12px; font-size: 13px; font-weight: 700; }
    .badge-upcoming { background: #fef9c3; color: #854d0e; }
    .badge-completed { background: #dcfce7; color: #166534; }
    .badge-cancelled { background: #fee2e2; color: #991b1b; }

    /* View modal action area */
    .view-action-container { display: flex; flex-direction: column; gap: 12px; }
    .divider { height: 1px; background: #e2e8f0; }

    /* Form textarea */
    .form-group textarea {
        width: 100%;
        padding: 12px 15px;
        border: 2px solid #e2e8f0;
        border-radius: 10px;
        background: #fff;
        font-size: 15px;
        resize: vertical;
        transition: border-color 0.2s;
        font-family: inherit;
    }
    .form-group textarea:focus { outline: none; border-color: var(--primary-blue); }
    .form-group select {
        width: 100%;
        padding: 12px 15px;
        border: 2px solid #e2e8f0;
        border-radius: 10px;
        background: #fff;
        font-size: 15px;
    }

    /* Summary Dropdown */
    .summary-month-option {
        padding: 12px 16px;
        cursor: pointer;
        font-weight: 600;
        font-size: 14px;
        color: var(--text-main);
        transition: background 0.15s;
    }
    .summary-month-option:hover { background: #f1f5f9; }
</style>
@endpush

@section('content')
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
                <button class="hamburger-menu" id="hamburger-menu">
                    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="var(--primary-blue)" stroke-width="2.5"><line x1="3" y1="12" x2="21" y2="12"></line><line x1="3" y1="6" x2="21" y2="6"></line><line x1="3" y1="18" x2="21" y2="18"></line></svg>
                </button>
                <a href="{{ route('dashboard') }}" style="text-decoration: none; color: inherit; cursor: pointer;">
                    <h1 style="margin: 0;">Schedule Management System</h1>
                </a>
            </div>
            
            <div style="display: flex; align-items: center; gap: 20px;">
                <div class="profile-container">
                    <button class="profile-btn" id="profile-btn">
                        @if($user->profile_picture)
                            <img src="{{ asset('storage/profile_pictures/' . $user->profile_picture) }}" alt="Profile" style="width:32px; height:32px; border-radius:50%; object-fit:cover;">
                        @else
                            <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                        @endif
                    </button>
                    <div class="profile-dropdown" id="profile-dropdown">
                        <div style="padding: 10px; font-weight: bold; color: var(--text-main); border-bottom: 1px solid #e0e0e0;">{{ $user->display_name }}</div>
                        <a href="{{ route('profile') }}" style="display: block; padding: 10px; color: var(--text-main); text-decoration: none; background: #f0f0f0;">My Profile</a>
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
        
        <section class="profile-section">
            <div class="profile-form-wrapper">
                <h1 style="text-align: center; margin-bottom: 40px; color: var(--primary-blue);">Your Profile</h1>

                <div class="profile-picture-container">
                    <div class="profile-pic-wrapper">
                        @if(!empty($user->profile_picture))
                            <img src="{{ asset('storage/profile_pictures/' . $user->profile_picture) }}?v={{ time() }}" alt="Profile Picture" id="current-profile-pic" style="display: block;">
                        @else
                            <svg viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="1.5">
                                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                                <circle cx="12" cy="7" r="4"></circle>
                            </svg>
                        @endif
                        <button class="edit-pic-btn" id="edit-pic-btn" title="Edit Profile Picture">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 20h9"></path><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4 12.5-12.5z"></path></svg>
                        </button>
                    </div>
                </div>

                <!-- Hidden file input for profile picture upload -->
                <input type="file" id="profile-picture-input" accept="image/*" style="display: none;">

                @if(session('success'))
                    <div style="color: green; text-align: center; margin-bottom: 20px;">{{ session('success') }}</div>
                @endif

                @if($errors->any())
                    <div style="color: #dc2626; margin-bottom: 20px; padding: 12px; background: #fef2f2; border-radius: 10px;">
                        <ul style="margin: 0; padding-left: 20px;">
                            @foreach($errors->all() as $err)
                                <li>{{ $err }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('profile.update') }}" method="POST" id="profile-update-form">
                    @csrf
                    <div class="form-group">
                        <label for="full_name">Full Name</label>
                        <input type="text" id="full_name" name="full_name" value="{{ old('full_name', $user->full_name ?? $user->display_name) }}" required>
                    </div>
                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <input type="email" id="email" name="email" value="{{ old('email', $user->email) }}" required>
                    </div>
                    <div class="form-group">
                        <label for="password">New Password (leave blank to keep current)</label>
                        <input type="password" id="password" name="password" placeholder="e.g. admin123">
                    </div>
                    <div class="form-group">
                        <label for="password_confirmation">Confirm New Password</label>
                        <input type="password" id="password_confirmation" name="password_confirmation" placeholder="e.g. admin123">
                    </div>
                    <div style="display: flex; justify-content: flex-end; margin-top: 30px;">
                        <button type="submit" class="btn btn-primary">Update Profile</button>
                    </div>
                </form>
            </div>
        </section>
    </main>
</div>

<!-- Modals for Schedule Functionality -->
<!-- Add/Edit Event Modal -->
<div class="modal-overlay" id="event-modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2 id="modal-title">Add New Schedule</h2>
        </div>
        <form id="event-form">
            <input type="hidden" id="event-id">
            <div class="form-group">
                <label for="event-title">Title of Schedule</label>
                <input type="text" id="event-title" required placeholder="e.g. Training">
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label for="event-location">Location</label>
                    <input type="text" id="event-location" name="location" placeholder="e.g. Surigao City">
                </div>
                <div class="form-group">
                    <label for="event-classification">Classification of Clients</label>
                    <input type="text" id="event-classification" name="classification" placeholder="e.g. Women, Youth, IP, Farmers">
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label for="event-date">Date Started</label>
                    <input type="text" id="event-date" name="date" required placeholder="Select start date">
                </div>
                <div class="form-group" style="position: relative;">
                    <label for="event-end-date">Date Ended (optional)</label>
                    <div style="position: relative; display: flex; align-items: center;">
                        <input type="text" id="event-end-date" name="end_date" placeholder="Select end date" style="flex: 1;">
                        <button type="button" class="clear-date-btn" data-input="event-end-date" title="Clear date" style="position: absolute; right: 10px; background: transparent; border: none; cursor: pointer; color: var(--text-muted); font-size: 18px;">&times;</button>
                    </div>
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
                <button type="button" class="btn" style="background: transparent; color: var(--text-muted); box-shadow: none;" onclick="closeModal('event-modal')">Cancel</button>
                <button type="submit" class="btn btn-accent" id="save-event-btn">Save Schedule</button>
            </div>
        </form>
    </div>
</div>

<!-- View Event Details Modal -->
<div class="modal-overlay" id="view-modal" style="z-index: 100000;">
    <div class="modal-content" style="max-width: 600px; padding: 40px; position: relative;">
        <div id="view-top-date" style="text-align: center; font-size: 20px; font-weight: 800; color: var(--primary-blue); text-transform: uppercase; margin-bottom: 12px; width: 100%; letter-spacing: 0.5px;"></div>
        <div style="margin-bottom: 20px; display: flex; justify-content: space-between; align-items: flex-start; gap: 15px; flex-wrap: wrap;">
            <div style="display:flex; align-items:flex-start; gap:10px; min-width: 0; flex: 1;">
                <h2 id="view-title" style="word-break: break-word; font-size: 32px; color: var(--text-main); font-weight: 800; margin-bottom: 10px; min-width:0;">Schedule Title</h2>
                <button type="button" class="view-inline-edit-btn" data-field="title" aria-label="Edit title" style="display:none; margin-top: 6px; background:transparent; border:none; cursor:pointer; padding:6px; flex-shrink:0;">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="var(--primary-blue)" stroke-width="2"><path d="M12 20h9"></path><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4 12.5-12.5z"></path></svg>
                </button>
            </div>
            <span id="view-status-badge" class="status-badge badge-upcoming" style="font-size: 19px; font-weight: 700; padding: 6px 12px; border-radius: 50px; white-space: nowrap; align-self: flex-start;">🟡 Upcoming</span>
        </div>
        <div style="margin-bottom: 25px; display: flex; flex-direction: column; gap: 16px;">
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
                        <input type="text" id="view-date-start-input" style="width:100%; padding: 10px 12px; border: 2px solid #e2e8f0; border-radius: 10px; background:#fff;" placeholder="Select start date">
                    </div>
                    <div style="flex:1 1 180px; position: relative;">
                        <div style="font-size: 12px; font-weight: 800; color: var(--text-muted); margin-bottom: 6px;">Date Ended (optional)</div>
                        <div style="position: relative; display: flex; align-items: center;">
                            <input type="text" id="view-date-end-input" style="width:100%; padding: 10px 12px; border: 2px solid #e2e8f0; border-radius: 10px; background:#fff;" placeholder="Select end date">
                            <button type="button" class="clear-date-btn" data-input="view-date-end-input" title="Clear date" style="position: absolute; right: 10px; background: transparent; border: none; cursor: pointer; color: var(--text-muted); font-size: 18px;">&times;</button>
                        </div>
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
                    @foreach ([['#93c5fd','Soft Blue'],['#fdba74','Peach'],['#86efac','Sage'],['#fcd34d','Amber'],['#c4b5fd','Warm Violet'],['#fb923c','Orange'],['#fef3c7','Cream'],['#f9a8d4','Rose'],['#5eead4','Mint'],['#d1d5db','Warm Gray']] as $c)
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
            <button type="button" class="view-inline-edit-btn" data-field="details" aria-label="Edit details" style="position:absolute; right: 14px; top: 14px; background:transparent; border:none; cursor:pointer; padding:6px;">
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
                <button type="button" class="btn" id="btn-delete-view" style="background: #a9b3c0ff; color: white;">
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

<!-- Custom Alert Modal -->
<div class="modal-overlay" id="custom-alert-modal" style="z-index: 99999;">
    <div class="modal-content" style="max-width: 400px; padding: 40px 30px; text-align: center; border-radius: 24px;">
        <h2 style="font-size: 24px; font-weight: 800; color: #1e293b; margin-bottom: 20px;">Upcoming Reminders</h2>
        <div id="custom-alert-list" style="margin-bottom: 30px; display: flex; flex-direction: column; gap: 10px; max-height: 40vh; overflow-y: auto; text-align: left;"></div>
        <button class="btn btn-accent" id="custom-alert-ok" style="width: 100%; padding: 14px; font-size: 16px; border-radius: 12px;">Okay</button>
    </div>
</div>

<!-- Edit single day modal -->
<div class="modal-overlay" id="edit-day-modal">
    <div class="modal-content" style="max-width: 480px;">
        <div class="modal-header"><h2 id="edit-day-modal-title">Edit day details</h2></div>
        <input type="hidden" id="edit-day-event-id"><input type="hidden" id="edit-day-date">
        <div class="form-group"><label for="edit-day-title">Schedule Title</label><input type="text" id="edit-day-title"></div>
        <div class="form-group"><label for="edit-day-location">Location</label><input type="text" id="edit-day-location"></div>
        <div class="form-group"><label for="edit-day-classification">Classification</label><input type="text" id="edit-day-classification"></div>
        <div class="form-group"><label for="edit-day-desc">Details</label><textarea id="edit-day-desc" rows="3"></textarea></div>
        <div style="display: flex; justify-content: flex-end; gap: 15px; margin-top: 20px;">
            <button type="button" class="btn" style="background: transparent; color: var(--text-muted);" onclick="closeModal('edit-day-modal')">Cancel</button>
            <button type="button" class="btn btn-accent" id="edit-day-save">Save</button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Hamburger Menu Logic
    const hamburgerMenu = document.getElementById('hamburger-menu');
    const sidebar = document.getElementById('sidebar-main');

    if (hamburgerMenu && sidebar) {
        hamburgerMenu.addEventListener('click', (e) => {
            e.stopPropagation();
            sidebar.classList.toggle('active');
        });

        document.addEventListener('click', (e) => {
            if (sidebar.classList.contains('active') && !sidebar.contains(e.target) && e.target !== hamburgerMenu) {
                sidebar.classList.remove('active');
            }
        });
    }

    // Profile Dropdown Logic
    const profileBtn = document.getElementById('profile-btn');
    const profileDropdown = document.getElementById('profile-dropdown');

    if (profileBtn && profileDropdown) {
        profileBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            profileDropdown.classList.toggle('active');
        });

        document.addEventListener('click', (e) => {
            if (!profileBtn.contains(e.target) && !profileDropdown.contains(e.target)) {
                profileDropdown.classList.remove('active');
            }
        });
    }

    // Profile Picture Upload Logic with Debug Console Logs
    const editBtn = document.getElementById('edit-pic-btn');
    const fileInput = document.getElementById('profile-picture-input');

    if (editBtn && fileInput) {
        editBtn.addEventListener('click', () => {
            fileInput.click();
        });

        fileInput.addEventListener('change', function() {
            if (fileInput.files && fileInput.files[0]) {
                const file = fileInput.files[0];
                const formData = new FormData();
                formData.append('profile_picture', file);
                formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));
                
                Swal.fire({
                    title: 'Uploading...',
                    allowOutsideClick: false,
                    didOpen: () => { Swal.showLoading(); }
                });

                fetch('{{ route("profile.upload-picture") }}', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const imageUrl = '{{ asset('storage/profile_pictures/') }}/' + data.filename;
                        
                        // Update profile page image
                        const currentPic = document.getElementById('current-profile-pic');
                        if (currentPic) {
                            currentPic.src = imageUrl + '?v=' + Date.now();
                        } else {
                            const wrapper = document.querySelector('.profile-pic-wrapper');
                            const svg = wrapper.querySelector('svg');
                            if (svg) {
                                const img = document.createElement('img');
                                img.src = imageUrl + '?v=' + Date.now();
                                img.alt = 'Profile Picture';
                                img.id = 'current-profile-pic';
                                svg.replaceWith(img);
                            }
                        }
                        
                        // Update header dropdown image
                        const headerPic = document.querySelector('#profile-btn img');
                        if (headerPic) {
                            headerPic.src = imageUrl + '?v=' + Date.now();
                        } else {
                            const profileBtn = document.getElementById('profile-btn');
                            const svg = profileBtn.querySelector('svg');
                            if (svg) {
                                const img = document.createElement('img');
                                img.src = imageUrl + '?v=' + Date.now();
                                img.alt = 'Profile';
                                img.style.cssText = 'width:32px; height:32px; border-radius:50%; object-fit:cover;';
                                svg.replaceWith(img);
                            }
                        }
                        
                        Swal.fire({
                            icon: 'success',
                            title: 'Success!',
                            text: 'Profile picture updated successfully!',
                            toast: true,
                            position: 'top-end',
                            showConfirmButton: false,
                            timer: 3000,
                            timerProgressBar: true
                        });
                        
                        fileInput.value = '';
                    } else {
                        throw new Error('Upload failed');
                    }
                })
                .catch(error => {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: 'Failed to upload profile picture.',
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: 3000
                    });
                });
            }
        });
    }

    // Show success message using SweetAlert
    @if(session('success'))
        Swal.fire({
            icon: 'success',
            title: 'Success!',
            text: '{{ session('success') }}',
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true
        });
    @endif
});
</script>
<script src="{{ asset('script.js') }}?v={{ time() }}"></script>
@endpush
