import { API_BASE, getFetchHeaders } from "./api.js";

let currentDate = new Date();
let events = [];
let selectedFilterDate = null;
let currentDayOverrides = {};
let mainLocation = "";
function getFullDateString(dateStr) {
    const dObj = new Date(`${dateStr}T00:00:00`);
    const mNames = [
        "January",
        "February",
        "March",
        "April",
        "May",
        "June",
        "July",
        "August",
        "September",
        "October",
        "November",
        "December",
    ];
    return `${mNames[dObj.getMonth()]} ${dObj.getDate()}, ${dObj.getFullYear()}`;
}

// DOM Elements
const calendarGrid = document.getElementById("calendar-grid");
const monthYearText =
    document.getElementById("calendar-month-year") ||
    document.getElementById("month-year");
const prevMonthBtn = document.getElementById("prev-month");
const nextMonthBtn = document.getElementById("next-month");
const eventModal = document.getElementById("event-modal");
const viewModal = document.getElementById("view-modal");
const eventForm = document.getElementById("event-form");
const eventListContainer = document.getElementById("event-list-container");
const searchInput = document.getElementById("search-input");
const filterTimeDropdown = document.getElementById("filter-time-dropdown");
const statusChips = document.querySelectorAll("#status-filters .chip");
const selectedDayOption = document.getElementById("selected-day-option");
const monthFilterDropdown = document.getElementById("month-filter");
let activeStatusFilter = "all";
let countdownInterval = null;
let currentViewContext = { eventId: null, date: null };
let currentViewOriginal = {};
let viewEditMode = false;
let currentEditContext = { eventId: null, date: null };

function toLocalDateString(date) {
    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, "0");
    const day = String(date.getDate()).padStart(2, "0");
    return `${year}-${month}-${day}`;
}

// Initialization
document.addEventListener("DOMContentLoaded", () => {
    fetchEvents();

    // Large tooltip for per-day schedule locations (accessibility)
    const ensureCalendarTooltip = () => {
        let el = document.getElementById("calendar-location-tooltip");
        if (el) return el;
        el = document.createElement("div");
        el.id = "calendar-location-tooltip";
        el.className = "calendar-location-tooltip";
        document.body.appendChild(el);
        return el;
    };

    window.showCalendarLocationTooltip = (dateStr, locationText) => {
        const tooltip = ensureCalendarTooltip();
        const dayCell = dateStr
            ? document.querySelector(`.calendar-day[data-date="${dateStr}"]`)
            : null;
        if (!dayCell) return;

        const rect = dayCell.getBoundingClientRect();
        const cx = rect.left + rect.width / 2;
        const cy = rect.top + rect.height / 2;

        const loc = (locationText || "").trim();
        tooltip.innerHTML = `Location: ${loc ? loc : "—"}<span class="muted">${getFullDateString(dateStr)}</span>`;
        tooltip.style.left = `${cx}px`;
        tooltip.style.top = `${cy}px`;
        tooltip.classList.add("show");
    };

    window.hideCalendarLocationTooltip = () => {
        const tooltip = document.getElementById("calendar-location-tooltip");
        if (tooltip) tooltip.classList.remove("show");
    };

    // Close modal on click outside or ESC
    document.querySelectorAll(".modal-overlay").forEach((overlay) => {
        overlay.addEventListener("mousedown", (e) => {
            if (e.target === overlay) {
                overlay.classList.remove("active");
            }
        });
    });

    window.addEventListener("keydown", (e) => {
        if (e.key === "Escape") {
            document.querySelectorAll(".modal-overlay").forEach((overlay) => {
                overlay.classList.remove("active");
            });
        }
    });

    if (prevMonthBtn) {
        prevMonthBtn.addEventListener("click", () => {
            currentDate.setMonth(currentDate.getMonth() - 1);
            renderCalendar();
        });
    }

    if (nextMonthBtn) {
        nextMonthBtn.addEventListener("click", () => {
            currentDate.setMonth(currentDate.getMonth() + 1);
            renderCalendar();
        });
    }

    if (eventForm) {
        eventForm.addEventListener("submit", handleEventSubmit);
    }

    // Per-day location listeners
    const dateStartInput = document.getElementById("event-date");
    const dateEndInput = document.getElementById("event-end-date");
    const locationSelector = document.getElementById("day-location-selector");
    const locationInput = document.getElementById("event-location");
    const locationDayLabel = document.getElementById("location-day-label");

    const updateDaySelector = () => {
        const startDate = dateStartInput ? dateStartInput.value : "";
        const endDate = dateEndInput ? dateEndInput.value : "";
        const container = document.getElementById("day-location-container");
        
        // Only show for multi-day schedules (end date exists and is different from start)
        if (!startDate || !endDate || startDate === endDate) {
            if (container) container.style.display = "none";
            if (locationDayLabel) locationDayLabel.style.display = "none";
            if (locationSelector) locationSelector.value = "general";
            return;
        }

        const start = new Date(startDate + "T00:00:00");
        const end = new Date(endDate + "T00:00:00");
        
        if (end <= start) {
            if (container) container.style.display = "none";
            if (locationDayLabel) locationDayLabel.style.display = "none";
            if (locationSelector) locationSelector.value = "general";
            return;
        }

        // It is a multi-day schedule
        if (container) container.style.display = "block";
        const currentVal = locationSelector ? locationSelector.value : "general";
        if (locationSelector) {
            locationSelector.innerHTML = '<option value="general">Apply to all days</option>';
            
            let curr = new Date(start);
            while (curr <= end) {
                const dStr = toLocalDateString(curr);
                const option = document.createElement("option");
                option.value = dStr;
                option.textContent = getFullDateString(dStr);
                locationSelector.appendChild(option);
                curr.setDate(curr.getDate() + 1);
            }
            
            locationSelector.value = currentVal;
            if (locationSelector.selectedIndex === -1) locationSelector.value = "general";
        }
    };

    if (dateStartInput) dateStartInput.addEventListener("change", updateDaySelector);
    if (dateEndInput) dateEndInput.addEventListener("change", updateDaySelector);
    
    // Initialize Flatpickr if available
    if (typeof flatpickr !== 'undefined') {
        if (dateStartInput) {
            flatpickr(dateStartInput, { altInput: true, altFormat: "m/d/Y", dateFormat: "Y-m-d" });
        }
        if (dateEndInput) {
            flatpickr(dateEndInput, { altInput: true, altFormat: "m/d/Y", dateFormat: "Y-m-d" });
        }
    }

    // Expose updateDaySelector globally for the openModal function to call
    window.updateDaySelector = updateDaySelector;

    if (locationSelector) {
        locationSelector.addEventListener("change", () => {
            const val = locationSelector.value;
            if (val === "general") {
                locationInput.value = mainLocation;
            } else {
                locationInput.value = (currentDayOverrides[val] && currentDayOverrides[val].location) || "";
            }
        });
    }

    if (locationInput) {
        locationInput.addEventListener("input", () => {
            const val = locationSelector.value;
            if (val === "general") {
                mainLocation = locationInput.value;
            } else {
                if (!currentDayOverrides[val]) currentDayOverrides[val] = {};
                currentDayOverrides[val].location = locationInput.value;
            }
        });
    }

    // Cancel button - use delegation on the modal so it works reliably
    if (eventModal) {
        eventModal.addEventListener("click", (e) => {
            if (e.target.closest("#btn-cancel-event-modal")) {
                e.preventDefault();
                e.stopPropagation();
                handleCancelModal();
            }
        });
    }

    if (searchInput) {
        searchInput.addEventListener("input", () => {
            renderEventList();
        });
    }

    if (filterTimeDropdown) {
        filterTimeDropdown.addEventListener("change", () => {
            if (filterTimeDropdown.value !== "selected-day") {
                selectedFilterDate = null;
            }
            renderEventList();
        });
    }

    statusChips.forEach((chip) => {
        chip.addEventListener("click", () => {
            statusChips.forEach((c) => c.classList.remove("active"));
            chip.classList.add("active");
            activeStatusFilter = chip.getAttribute("data-status");
            renderEventList();
        });
    });

    if (monthFilterDropdown) {
        monthFilterDropdown.addEventListener("change", () => {
            renderEventList();
        });
    }

    // Sidebar Push Logic
    const hamburgerMenu = document.getElementById("hamburger-menu");
    const sidebar = document.getElementById("sidebar-main");
    const mainContent = document.getElementById("main-content");

    const toggleSidebar = () => {
        sidebar.classList.toggle("active");
    };

    if (hamburgerMenu) {
        hamburgerMenu.addEventListener("click", (e) => {
            e.stopPropagation();
            toggleSidebar();
        });
    }

    const btnAddSchedule = document.getElementById("btn-add-schedule");
    if (btnAddSchedule) {
        btnAddSchedule.addEventListener("click", () => openModal("add"));
    }

    // Create Summary Dropdown Logic
    const btnCreateSummary = document.getElementById("btn-create-summary");
    const summaryDropdownMenu = document.getElementById(
        "summary-dropdown-menu",
    );

    if (btnCreateSummary && summaryDropdownMenu) {
        btnCreateSummary.addEventListener("click", (e) => {
            e.stopPropagation();
            console.log("Create Summary button clicked");
            summaryDropdownMenu.style.display =
                summaryDropdownMenu.style.display === "none" ? "block" : "none";
        });

        // Handle month selection
        summaryDropdownMenu
            .querySelectorAll(".summary-month-option")
            .forEach((option) => {
                option.addEventListener("click", (e) => {
                    e.stopPropagation();
                    const month = option.getAttribute("data-month");
                    const monthNames = [
                        "January",
                        "February",
                        "March",
                        "April",
                        "May",
                        "June",
                        "July",
                        "August",
                        "September",
                        "October",
                        "November",
                        "December",
                    ];
                    const selectedMonthName =
                        month === "all"
                            ? "All Year"
                            : monthNames[parseInt(month)];

                    console.log(
                        "Exporting schedule for:",
                        selectedMonthName,
                        "Month value:",
                        month,
                    );

                    // Show loading indicator
                    Swal.fire({
                        title: "Generating Excel...",
                        text: `Preparing ${selectedMonthName} schedule summary`,
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        },
                    });

                    // Submit form to download Excel
                    const form = document.createElement("form");
                    form.method = "POST";
                    form.action = "/export-summary";

                    const tokenInput = document.createElement("input");
                    tokenInput.type = "hidden";
                    tokenInput.name = "_token";
                    tokenInput.value =
                        document
                            .querySelector('meta[name="csrf-token"]')
                            ?.getAttribute("content") || "";

                    const monthInput = document.createElement("input");
                    monthInput.type = "hidden";
                    monthInput.name = "month";
                    monthInput.value = month;

                    const yearInput = document.createElement("input");
                    yearInput.type = "hidden";
                    yearInput.name = "year";
                    yearInput.value = new Date().getFullYear();

                    form.appendChild(tokenInput);
                    form.appendChild(monthInput);
                    form.appendChild(yearInput);
                    document.body.appendChild(form);

                    console.log(
                        "Submitting form to:",
                        form.action,
                        "with month:",
                        month,
                        "year:",
                        yearInput.value,
                    );

                    // Close dropdown
                    summaryDropdownMenu.style.display = "none";

                    // Submit form
                    form.submit();
                    document.body.removeChild(form);

                    // Close loading alert after download starts
                    setTimeout(() => {
                        Swal.close();
                        Swal.fire({
                            icon: "success",
                            title: "Download Started!",
                            text: `Your ${selectedMonthName} schedule summary is being downloaded.`,
                            toast: true,
                            position: "top-end",
                            showConfirmButton: false,
                            timer: 3000,
                            timerProgressBar: true,
                        });
                    }, 1000);
                });
            });

        // Close dropdown when clicking outside
        document.addEventListener("click", () => {
            if (summaryDropdownMenu) {
                summaryDropdownMenu.style.display = "none";
            }
        });
    }

    // Profile Dropdown Logic
    const profileBtn = document.getElementById("profile-btn");
    const profileDropdown = document.getElementById("profile-dropdown");

    if (profileBtn && profileDropdown) {
        profileBtn.addEventListener("click", (e) => {
            e.stopPropagation();
            profileDropdown.classList.toggle("active");
        });

        document.addEventListener("click", (e) => {
            if (
                !profileBtn.contains(e.target) &&
                !profileDropdown.contains(e.target)
            ) {
                profileDropdown.classList.remove("active");
            }
        });
    }

    // Close sidebar on outside click
    document.addEventListener("click", (e) => {
        if (
            sidebar &&
            sidebar.classList.contains("active") &&
            !sidebar.contains(e.target) &&
            e.target !== hamburgerMenu
        ) {
            sidebar.classList.remove("active");
        }
    });

    // Notification icon click
    const btnNotification = document.getElementById("btn-notification");
    if (btnNotification) {
        btnNotification.addEventListener("click", (e) => {
            e.stopPropagation();
            openNotificationModal(events, { showEvenIfEmpty: true });
        });
    }

    // Edit day (per-day details) save
    const editDaySave = document.getElementById("edit-day-save");
    if (editDaySave) {
        editDaySave.addEventListener("click", saveEditDay);
    }

    // Schedule color picker dropdown
    const colorTrigger = document.getElementById("event-color-trigger");
    const colorDropdown = document.getElementById("event-color-dropdown");
    const colorValueInput = document.getElementById("event-color-value");
    const colorTriggerName = document.getElementById(
        "event-color-trigger-name",
    );
    const colorTriggerSwatch = document.getElementById(
        "event-color-trigger-swatch",
    );
    if (colorTrigger && colorDropdown) {
        colorTrigger.addEventListener("click", (e) => {
            e.stopPropagation();
            colorDropdown.classList.toggle("open");
            colorTrigger.setAttribute(
                "aria-expanded",
                colorDropdown.classList.contains("open"),
            );
        });
        document
            .querySelectorAll("#event-color-dropdown .color-picker-option")
            .forEach((opt) => {
                opt.addEventListener("click", (e) => {
                    e.stopPropagation();
                    const hex = opt.getAttribute("data-hex");
                    const name = opt.getAttribute("data-name");
                    if (colorValueInput) colorValueInput.value = hex;
                    if (colorTriggerName) colorTriggerName.textContent = name;
                    if (colorTriggerSwatch)
                        colorTriggerSwatch.style.background = hex;
                    colorDropdown.classList.remove("open");
                    colorTrigger.setAttribute("aria-expanded", "false");
                });
            });
        document.addEventListener("click", () => {
            colorDropdown.classList.remove("open");
            colorTrigger.setAttribute("aria-expanded", "false");
        });
    }

    setTimeout(checkUpcomingReminders, 1000);
});

// Schedule color palette (system-assigned, no manual picker)
const SCHEDULE_COLORS = [
    { hex: "#93c5fd", name: "Soft Blue" },
    { hex: "#fdba74", name: "Peach" },
    { hex: "#86efac", name: "Sage" },
    { hex: "#fcd34d", name: "Amber" },
    { hex: "#c4b5fd", name: "Warm Violet" },
    { hex: "#fb923c", name: "Orange" },
    { hex: "#fef3c7", name: "Cream" },
    { hex: "#f9a8d4", name: "Rose" },
    { hex: "#5eead4", name: "Mint" },
    { hex: "#d1d5db", name: "Warm Gray" },
];

// Auto-assign color: cycle through palette based on current count of events
function getNextAutoColor() {
    const idx = events.length % SCHEDULE_COLORS.length;
    return SCHEDULE_COLORS[idx].hex;
}
const LEGACY_COLOR_MAP = {
    "#bfdbfe": "#93c5fd",
    "#fecaca": "#fdba74",
    "#bbf7d0": "#86efac",
    "#fde68a": "#fcd34d",
    "#e9d5ff": "#c4b5fd",
    "#fed7aa": "#fb923c",
    "#cffafe": "#5eead4",
    "#99f6e4": "#5eead4",
    "#e5e7eb": "#d1d5db",
    "#f9a8d4": "#f9a8d4",
};
function getEventColorName(hex) {
    if (!hex) return "Soft Blue";
    const normalized = LEGACY_COLOR_MAP[hex] || hex;
    const found = SCHEDULE_COLORS.find((c) => c.hex === normalized);
    return found ? found.name : SCHEDULE_COLORS[0].name;
}
function setEventColorPicker(hex, name) {
    const norm = LEGACY_COLOR_MAP[hex] || hex;
    const val = document.getElementById("event-color-value");
    const triggerName = document.getElementById("event-color-trigger-name");
    const triggerSwatch = document.getElementById("event-color-trigger-swatch");
    if (val) val.value = norm;
    if (triggerName) triggerName.textContent = name || getEventColorName(hex);
    if (triggerSwatch) triggerSwatch.style.background = norm;
}

function normalizeScheduleColor(hex) {
    return LEGACY_COLOR_MAP[hex] || hex || SCHEDULE_COLORS[0].hex;
}

function setViewInlineMode(field, isEditing) {
    const actions = document.getElementById(`view-${field}-actions`);
    const editBtn = document.querySelector(
        `.view-inline-edit-btn[data-field="${field}"]`,
    );
    if (actions) actions.style.display = isEditing ? "flex" : "none";
    if (editBtn) editBtn.style.display = isEditing ? "none" : "inline-flex";
}

async function saveViewField(field) {
    const e = events.find((ev) => ev.id === currentViewContext.eventId);
    if (!e) return;
    const ctxDate = currentViewContext.date || e.date;
    const isMultiDay = e.end_date && e.end_date !== e.date;
    const applyToDay =
        isMultiDay &&
        ctxDate &&
        ctxDate !== e.date &&
        field !== "date" &&
        field !== "color";

    const next = { ...e };
    const day_overrides = { ...(e.day_overrides || {}) };
    const day = { ...(day_overrides[ctxDate] || {}) };

    if (field === "title") {
        const val = (
            document.getElementById("view-title-input")?.value || ""
        ).trim();
        if (applyToDay) day.title = val || null;
        else next.title = val || next.title;
    }
    if (field === "location") {
        const val = (
            document.getElementById("view-location-input")?.value || ""
        ).trim();
        if (applyToDay) day.location = val || null;
        else next.location = val || null;
    }
    if (field === "classification") {
        const val = (
            document.getElementById("view-classification-input")?.value || ""
        ).trim();
        if (applyToDay) day.classification = val || null;
        else next.classification = val || null;
    }
    if (field === "date") {
        const startVal =
            document.getElementById("view-date-start-input")?.value || "";
        const endVal =
            document.getElementById("view-date-end-input")?.value || "";
        next.date = startVal || next.date;
        next.end_date = endVal || null;
    }
    if (field === "color") {
        const selected = currentViewOriginal.color || next.color;
        next.color = normalizeScheduleColor(selected);
    }
    if (field === "details") {
        const val = (
            document.getElementById("view-details-input")?.value || ""
        ).trim();
        if (applyToDay) day.description = val || null;
        else next.description = val || "";
    }

    if (applyToDay) {
        day_overrides[ctxDate] = day;
        next.day_overrides = day_overrides;
    } else if (isMultiDay && ctxDate && field !== "date" && field !== "color") {
        // If editing the start day, treat it as schedule-level update (default details)
        next.day_overrides = day_overrides;
    }

    try {
        const res = await fetch(API_BASE, {
            method: "POST",
            credentials: "include",
            headers: getFetchHeaders("POST"),
            body: JSON.stringify({
                id: next.id,
                title: next.title,
                date: next.date,
                end_date: next.end_date,
                location: next.location,
                classification: next.classification,
                description: next.description,
                status: next.status,
                color: next.color,
                day_overrides: next.day_overrides,
            }),
        });
        const result = await res.json();
        if (result.status === "success") {
            Swal.fire({
                title: "Updated successfully!",
                icon: "success",
                toast: true,
                position: "top",
                showConfirmButton: false,
                timer: 1500,
                timerProgressBar: true,
            });
            // Exit edit mode after save
            viewEditMode = false;
            const editDetailsBtn = document.getElementById("btn-edit-view");
            if (editDetailsBtn) {
                editDetailsBtn.innerHTML = `<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg> Edit Details`;
            }
            updateViewEditModeUI();
            await fetchEvents();
            openViewModal(next.id, ctxDate);
        } else {
            Swal.fire("Error!", "Failed to update schedule", "error");
        }
    } catch (err) {
        console.error(err);
        Swal.fire("Error!", "Server Error", "error");
    }
}

function cancelViewField(field) {
    // restore original values in inputs and hide edit UI
    if (field === "title") {
        const input = document.getElementById("view-title-input");
        const h2 = document.getElementById("view-title");
        if (input) input.value = currentViewOriginal.title || "";
        if (h2) h2.style.display = "";
        if (input) input.style.display = "none";
    }
    if (field === "location") {
        const input = document.getElementById("view-location-input");
        const span = document.getElementById("view-location");
        if (input) input.value = currentViewOriginal.location || "";
        if (span) span.style.display = "";
        if (input) input.style.display = "none";
    }
    if (field === "classification") {
        const input = document.getElementById("view-classification-input");
        const span = document.getElementById("view-classification");
        if (input) input.value = currentViewOriginal.classification || "";
        if (span) span.style.display = "";
        if (input) input.style.display = "none";
    }
    if (field === "date") {
        const edit = document.getElementById("view-date-edit");
        if (edit) edit.style.display = "none";
    }
    if (field === "color") {
        const palette = document.getElementById("view-color-palette");
        if (palette) palette.style.display = "none";
    }
    if (field === "details") {
        const input = document.getElementById("view-details-input");
        const span = document.getElementById("view-desc");
        if (input) input.value = currentViewOriginal.details || "";
        if (span) span.style.display = "";
        if (input) input.style.display = "none";
    }
    setViewInlineMode(field, false);
}

function startViewField(field) {
    // only one field at a time
    ["title", "location", "classification", "date", "color", "details"].forEach(
        (f) => {
            if (f !== field) cancelViewField(f);
        },
    );

    if (field === "title") {
        const input = document.getElementById("view-title-input");
        const h2 = document.getElementById("view-title");
        if (h2) h2.style.display = "none";
        if (input) {
            input.style.display = "block";
            input.value = currentViewOriginal.title || "";
            input.focus();
        }
    }
    if (field === "location") {
        const input = document.getElementById("view-location-input");
        const span = document.getElementById("view-location");
        if (span) span.style.display = "none";
        if (input) {
            input.style.display = "inline-flex";
            input.value = currentViewOriginal.location || "";
            input.focus();
        }
    }
    if (field === "classification") {
        const input = document.getElementById("view-classification-input");
        const span = document.getElementById("view-classification");
        if (span) span.style.display = "none";
        if (input) {
            input.style.display = "inline-flex";
            input.value = currentViewOriginal.classification || "";
            input.focus();
        }
    }
    if (field === "date") {
        const edit = document.getElementById("view-date-edit");
        const start = document.getElementById("view-date-start-input");
        const end = document.getElementById("view-date-end-input");
        if (edit) edit.style.display = "flex";
        if (start) start.value = currentViewOriginal.date || "";
        if (end) end.value = currentViewOriginal.end_date || "";
    }
    if (field === "color") {
        const palette = document.getElementById("view-color-palette");
        if (palette) palette.style.display = "flex";
    }
    if (field === "details") {
        const input = document.getElementById("view-details-input");
        const span = document.getElementById("view-desc");
        if (span) span.style.display = "none";
        if (input) {
            input.style.display = "block";
            input.value = currentViewOriginal.details || "";
            input.focus();
        }
    }
    setViewInlineMode(field, true);
}


function getDaysUntil(fromDate, toDate) {
    const start = new Date(fromDate);
    start.setHours(0, 0, 0, 0);
    const target = new Date(toDate);
    target.setHours(0, 0, 0, 0);

    if (target <= start) return 0;

    let count = 0;
    // Count workdays starting TODAY up to the day BEFORE the schedule.
    // Excludes Saturday/Sunday, and excludes the schedule day itself.
    let current = new Date(start);
    while (current < target) {
        const day = current.getDay();
        if (day !== 0 && day !== 6) {
            // 0 = Sunday, 6 = Saturday
            count++;
        }
        current.setDate(current.getDate() + 1);
    }
    return count;
}

async function fetchEvents() {
    try {
        const response = await fetch(API_BASE, {
            credentials: "include",
            headers: { Accept: "application/json" },
        });
        if (response.status === 401) {
            window.location.href = "/";
            return;
        }
        if (response.status === 419) {
            window.location.reload();
            return;
        }
        const contentType = response.headers.get("content-type");
        if (!contentType || !contentType.includes("application/json")) {
            throw new Error("Server returned non-JSON response");
        }
        const result = await response.json();
        if (result.status === "success") {
            const now = new Date();
            now.setHours(0, 0, 0, 0);

            events = result.data.map((e) => {
                const start = new Date(e.date + "T00:00:00");
                const end = e.end_date
                    ? new Date(e.end_date + "T00:00:00")
                    : start;
                let status = e.status || "upcoming";
                if (typeof status === "string") {
                    status = status.toString().trim().toLowerCase();
                }
                // Automatic status update: only if end date has passed
                if (end < now && status === "upcoming") {
                    status = "completed";
                }
                return { ...e, status };
            });

            events.sort((a, b) => new Date(a.date) - new Date(b.date));
            renderCalendar();
            renderEventList();
            updateBentoStats();
            updateNextMeeting();
            updateNotificationBadge();
            // Reminders must be computed after events are loaded
            checkUpcomingReminders();
            // Open specific event if ?view=id in URL
            const viewId = new URLSearchParams(window.location.search).get(
                "view",
            );
            if (viewId && events.some((e) => e.id === viewId)) {
                setTimeout(() => openViewModal(viewId), 300);
                window.history.replaceState({}, "", window.location.pathname);
            }
        }
    } catch (e) {
        console.error("Failed to fetch events", e);
    }
}

function getColorByDate(dateStr, status, endDateStr) {
    const now = new Date();
    now.setHours(0, 0, 0, 0);
    const start = new Date(dateStr + "T00:00:00");
    const end = endDateStr ? new Date(endDateStr + "T00:00:00") : start;
    const daysUntil = getDaysUntil(now, start);

    if (status === "completed") return "#64748b"; // Gray for finished
    if (now >= start && now <= end && status === "upcoming") return "#3b82f6"; // Blue for ongoing

    if (daysUntil > 6) return "#22c55e"; // Green (More than 6 work days)
    if (daysUntil >= 4) return "#eab308"; // Amber (4, 5, or 6 days)
    if (daysUntil <= 3) return "#ea580c"; // Warm orange (soon), no red
    return "#22c55e";
}

function renderCalendar() {
    if (!calendarGrid) return; // Not on a page with a calendar
    calendarGrid.innerHTML = "";

    const year = currentDate.getFullYear();
    const month = currentDate.getMonth();

    const monthNames = [
        "January",
        "February",
        "March",
        "April",
        "May",
        "June",
        "July",
        "August",
        "September",
        "October",
        "November",
        "December",
    ];
    monthYearText.textContent = `${monthNames[month]} ${year}`;

    const firstDay = new Date(year, month, 1).getDay();
    const daysInMonth = new Date(year, month + 1, 0).getDate();
    const totalCells = firstDay + daysInMonth;
    const numRows = Math.ceil(totalCells / 7);

    const now = new Date();
    now.setHours(0, 0, 0, 0);
    const nowDateTime = new Date();

    // Helper: cell index (0-based) -> grid row and col (1-based)
    function cellToGrid(cellIndex) {
        const r = Math.floor(cellIndex / 7);
        const c = cellIndex % 7;
        return { row: r + 1, col: c + 1 };
    }

    // Empty cells for offset
    for (let i = 0; i < firstDay; i++) {
        const cell = document.createElement("div");
        const { row, col } = cellToGrid(i);
        cell.style.gridRow = row;
        cell.style.gridColumn = col;
        calendarGrid.appendChild(cell);
    }

    // Day cells only (no event blocks inside) – table style, no rounded corners, no hover animation
    for (let i = 1; i <= daysInMonth; i++) {
        const cell = document.createElement("div");
        cell.className = "calendar-day glass";

        const cellDate = `${year}-${String(month + 1).padStart(2, "0")}-${String(i).padStart(2, "0")}`;
        const cellDateObj = new Date(cellDate + "T00:00:00");
        cell.setAttribute("data-date", cellDate);
        const cellIndex = firstDay + (i - 1);
        const { row, col } = cellToGrid(cellIndex);

        cell.style.gridRow = row;
        cell.style.gridColumn = col;
        // Keep inline styles minimal; layout handled by CSS for table look
        cell.style.cssText +=
            "text-align: center; cursor: pointer; position: relative;";

        const dayEvents = events.filter((e) => {
            if (e.status === "cancelled") return false;
            const start = new Date(e.date + "T00:00:00");
            const end = e.end_date ? new Date(e.end_date + "T00:00:00") : start;
            return cellDateObj >= start && cellDateObj <= end;
        });

        const isPast = cellDateObj < now;
        const hasFinishedEvents = dayEvents.some(
            (e) => e.status === "completed",
        );
        // Gray only when date is strictly in the past AND every schedule on that day has passed or is finished (never gray today)
        const todayStr = new Date().toISOString().split("T")[0];
        const allSchedulesDone =
            dayEvents.length === 0 ||
            dayEvents.every((e) => {
                const evtEnd = e.end_date
                    ? new Date(e.end_date + "T00:00:00")
                    : new Date(e.date + "T00:00:00");
                return e.status === "completed" || evtEnd < now;
            });
        if (hasFinishedEvents) cell.classList.add("finished-day");
        if (cellDate !== todayStr && isPast && allSchedulesDone)
            cell.classList.add("past-no-finish");

        const dayText = document.createElement("div");
        dayText.textContent = i;
        dayText.style.fontWeight = "700";
        dayText.style.fontSize = "var(--font-size-lg)";
        cell.appendChild(dayText);

        if (cellDate === new Date().toISOString().split("T")[0]) {
            cell.style.border = "2px solid var(--primary-blue)";
            cell.style.backgroundColor = "rgba(255, 255, 255, 0.95)";
            dayText.style.color = "var(--primary-blue)";
        }

        const hasUpcomingOrFuture = dayEvents.some((e) => {
            const evtEnd = e.end_date
                ? new Date(e.end_date + "T00:00:00")
                : new Date(e.date + "T00:00:00");
            return e.status === "upcoming" && evtEnd >= now;
        });
        if (!isPast || hasFinishedEvents || hasUpcomingOrFuture) {
            cell.addEventListener("click", () => {
                if (dayEvents.length > 0) {
                    const modal = document.getElementById("day-events-modal");
                    document.getElementById("day-events-title").textContent =
                        `Events for ${i} ${monthNames[month]}`;
                    const addBtn = document.getElementById("btn-add-from-day");
                    addBtn.onclick = () => {
                        closeModal("day-events-modal");
                        selectedFilterDate = cellDate;
                        openModal("add");
                        document.getElementById("event-date").value = cellDate;
                    };
                    const list = document.getElementById("day-events-list");
                    list.innerHTML = "";
                    list.style.cssText =
                        "display: flex; flex-direction: column; gap: 15px; margin-top: 20px;";
                    dayEvents.forEach((e) => {
                        const card = document.createElement("div");
                        card.className = "glass";
                        card.style.cssText =
                            "padding: 16px 18px; border-radius: var(--border-radius-md); box-shadow: var(--box-shadow); cursor: pointer; text-align: left;";

                        const dotClass =
                            e.status === "completed"
                                ? "status-completed"
                                : e.status === "cancelled"
                                  ? "status-cancelled"
                                  : "status-upcoming";

                        // Multi-day date display
                        const startObj = new Date(e.date + "T00:00:00");
                        const endObj = e.end_date
                            ? new Date(e.end_date + "T00:00:00")
                            : startObj;
                        const isMultiDay = e.end_date && e.end_date !== e.date;
                        const mNamesShort = [
                            "Jan",
                            "Feb",
                            "Mar",
                            "Apr",
                            "May",
                            "Jun",
                            "Jul",
                            "Aug",
                            "Sep",
                            "Oct",
                            "Nov",
                            "Dec",
                        ];
                        const startStr = `${mNamesShort[startObj.getMonth()]} ${startObj.getDate()}`;
                        const dateDisplay = isMultiDay
                            ? `${startStr} – ${mNamesShort[endObj.getMonth()]} ${endObj.getDate()}`
                            : startStr;

                        const effectiveLocation =
                            (e.day_overrides &&
                            cellDate &&
                            e.day_overrides[cellDate] &&
                            e.day_overrides[cellDate].location)
                                ? e.day_overrides[cellDate].location
                                : e.location || "—";

                        card.innerHTML = `
                            <div style="min-width: 0;">
                                <h3 style="font-size: 24px; font-weight: 700; color: var(--primary-blue); margin-bottom: 8px; max-width: 100%; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                    ${e.title}
                                </h3>
                                <div style="color: var(--text-main); font-size: var(--font-size-base); font-weight: 800; margin-bottom: 6px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                    <span style="font-weight: 900;">Location:</span> ${effectiveLocation}
                                </div>
                                <div style="color: var(--text-muted); font-size: var(--font-size-base); display: flex; align-items: center; gap: 6px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
                                    <span style="min-width:0; overflow:hidden; text-overflow:ellipsis;">${dateDisplay}</span>
                                </div>
                            </div>
                        `;
                        card.onclick = () => {
                            closeModal("day-events-modal");
                            // Always show full details first; allow editing the clicked day from the view modal
                            openViewModal(e.id, cellDate);
                        };
                        list.appendChild(card);
                    });
                    modal.classList.add("active");
                } else {
                    selectedFilterDate = cellDate;
                    openModal("add");
                    document.getElementById("event-date").value = cellDate;
                }
            });
        }

        calendarGrid.appendChild(cell);
    }

    // Events that fall in this month (non-cancelled)
    const monthStart = new Date(year, month, 1);
    const monthEnd = new Date(year, month + 1, 0);
    const eventsInMonth = events.filter((e) => {
        if (e.status === "cancelled") return false;
        const start = new Date(e.date + "T00:00:00");
        const end = e.end_date ? new Date(e.end_date + "T00:00:00") : start;
        return start <= monthEnd && end >= monthStart;
    });

    // Build per-week-row segments and lane-assign them so different schedules never overlap.
    // Also enforce the requirement: max 2 bars per day cell.
    const BAR_GAP = 2;
    const BAR_HEIGHT = 20;
    const BOTTOM_PADDING = 8;

    // dayCounts[row][col] counts how many bars already occupy this cell
    const dayCounts = {};
    const getCount = (r, c) => dayCounts[r]?.[c] ?? 0;
    const incCount = (r, c) => {
        if (!dayCounts[r]) dayCounts[r] = {};
        dayCounts[r][c] = (dayCounts[r][c] || 0) + 1;
    };
    
    const overflowCounts = {};
    const incOverflow = (r, c) => {
        if (!overflowCounts[r]) overflowCounts[r] = {};
        overflowCounts[r][c] = (overflowCounts[r][c] || 0) + 1;
    };

    // Build segments per row (split events across week rows)
    const segmentsByRow = {};
    eventsInMonth.forEach((evt) => {
        const start = new Date(evt.date + "T00:00:00");
        const end = evt.end_date ? new Date(evt.end_date + "T00:00:00") : start;
        const startMonth = start.getMonth();
        const endMonth = end.getMonth();

        const clipStartDay = startMonth === month ? start.getDate() : 1;
        const clipEndDay = endMonth === month ? end.getDate() : daysInMonth;

        const startCellIndex = firstDay + (clipStartDay - 1);
        const endCellIndex = firstDay + (clipEndDay - 1);
        const startRow = Math.floor(startCellIndex / 7);
        const endRow = Math.floor(endCellIndex / 7);
        const startCol = startCellIndex % 7;
        const endCol = endCellIndex % 7;

        for (let r = startRow; r <= endRow; r++) {
            const segStartCol = r === startRow ? startCol : 0;
            const segEndCol = r === endRow ? endCol : 6;
            if (!segmentsByRow[r]) segmentsByRow[r] = [];
            segmentsByRow[r].push({
                evt,
                row: r,
                startCol: segStartCol,
                endCol: segEndCol,
            });
        }
    });

    // Lane assignment per row
    Object.keys(segmentsByRow).sort((a,b)=>a-b).forEach((rowKey) => {
        const r = parseInt(rowKey, 10);
        const segs = segmentsByRow[r];
        segs.sort((a, b) => a.startCol - b.startCol || (b.endCol - b.startCol) - (a.endCol - a.startCol));

        const laneEnds = []; 
        segs.forEach((seg) => {
            let lane = 0;
            while (lane < laneEnds.length && laneEnds[lane] >= seg.startCol) lane++;
            if (lane === laneEnds.length) laneEnds.push(-1);
            laneEnds[lane] = seg.endCol;
            seg.lane = lane;
        });

        segs.forEach((seg) => {
            // Check max 2 bars per day
            let canFit = true;
            for (let c = seg.startCol; c <= seg.endCol; c++) {
                if (getCount(r, c) >= 2) { canFit = false; break; }
            }
            if (canFit) {
                addEventBar(seg.evt, r + 1, seg.startCol + 1, seg.endCol + 2, seg.lane);
                for (let c = seg.startCol; c <= seg.endCol; c++) incCount(r, c);
            } else {
                for (let c = seg.startCol; c <= seg.endCol; c++) incOverflow(r, c);
            }
        });
    });

    // Render overflow badges (+N)
    Object.keys(overflowCounts).forEach((rKey) => {
        const r = parseInt(rKey, 10);
        Object.keys(overflowCounts[r]).forEach((cKey) => {
            const c = parseInt(cKey, 10);
            const count = overflowCounts[r][c];
            if (count > 0) {
                const badge = document.createElement("div");
                badge.style.gridRow = r + 1;
                badge.style.gridColumn = c + 1;
                badge.style.alignSelf = "end";
                badge.style.justifySelf = "end";
                badge.style.background = "#e2e8f0";
                badge.style.color = "#000000";
                badge.style.borderRadius = "4px";
                badge.style.padding = "2px 6px";
                badge.style.fontSize = "12px";
                badge.style.fontWeight = "700";
                badge.style.margin = "4px 8px 8px 4px";
                badge.style.zIndex = "10";
                badge.style.pointerEvents = "none";
                badge.textContent = `+${count}`;
                calendarGrid.appendChild(badge);
            }
        });
    });

    function addEventBar(evt, gridRow, colStart, colEnd, laneIndex) {
        const barColor = evt.color || getColorByDate(evt.date, evt.status, evt.end_date);
        const bar = document.createElement("div");
        bar.className = "calendar-event-bar";
        
        bar.style.setProperty("--bar-row", gridRow);
        bar.style.setProperty("--bar-start", colStart);
        bar.style.setProperty("--bar-end", colEnd);
        
        const SEGMENT_HEIGHT = 28; 
        const SEGMENT_GAP = 4;
        bar.style.setProperty(
            "--bar-bottom",
            BOTTOM_PADDING + laneIndex * (SEGMENT_HEIGHT + SEGMENT_GAP) + "px",
        );
        bar.style.height = SEGMENT_HEIGHT + "px";
        
        // Convert flex display to grid to align sub-capsules directly underneath calendar columns
        const spanDays = colEnd - colStart;
        bar.style.display = "grid";
        bar.style.gridTemplateColumns = `repeat(${spanDays}, 1fr)`;
        bar.style.position = "relative";
        bar.style.padding = "0";
        bar.style.borderRadius = "6px";
        bar.style.overflow = "hidden";

        if (evt.status === "completed") {
            bar.style.backgroundColor = "#e2e8f0";
            bar.style.color = "#64748b";
        } else {
            bar.style.backgroundColor = barColor;
        }

        const titleColor = evt.status === "completed" ? "#64748b" : "inherit";
        let innerHtmlContent = `
            <div style="position: absolute; top: 0; left: 10px; height: 100%; display: flex; align-items: center; font-size: 16px; font-weight: 800; white-space: nowrap; pointer-events: none; color: ${titleColor}; z-index: 2;">
                ${evt.title}
            </div>
        `;
        
        for (let i = 0; i < spanDays; i++) {
            const currentDayOfMonth = ((gridRow - 1) * 7 + (colStart - 1) + i) - firstDay + 1;
            let segDateStr = "";
            let segLoc = "";
            if (currentDayOfMonth >= 1 && currentDayOfMonth <= daysInMonth) {
                segDateStr = `${year}-${String(month + 1).padStart(2, "0")}-${String(currentDayOfMonth).padStart(2, "0")}`;
                segLoc =
                    (evt.day_overrides &&
                    evt.day_overrides[segDateStr] &&
                    evt.day_overrides[segDateStr].location)
                        ? evt.day_overrides[segDateStr].location
                        : evt.location || "";
            }
            const titleAttr = segLoc ? `Location: ${String(segLoc).replace(/"/g, "&quot;")}` : "Location: —";
            const clickHandler = segDateStr
                ? `openViewModal('${evt.id}', '${segDateStr}')`
                : `openViewModal('${evt.id}')`;
            innerHtmlContent += `
                <div
                    role="button"
                    tabindex="0"
                    onmouseenter="window.showCalendarLocationTooltip && window.showCalendarLocationTooltip('${segDateStr}', '${String(segLoc || "").replace(/\\/g, "\\\\").replace(/'/g, "\\'")}')"
                    onmouseleave="window.hideCalendarLocationTooltip && window.hideCalendarLocationTooltip()"
                    onfocus="window.showCalendarLocationTooltip && window.showCalendarLocationTooltip('${segDateStr}', '${String(segLoc || "").replace(/\\/g, "\\\\").replace(/'/g, "\\'")}')"
                    onblur="window.hideCalendarLocationTooltip && window.hideCalendarLocationTooltip()"
                    onclick="${clickHandler}"
                    style="height: 100%; width: 100%; cursor: pointer; z-index: 1; background: transparent;"
                ></div>
            `;
        }
        
        bar.innerHTML = innerHtmlContent;
        calendarGrid.appendChild(bar);
    }
}

function updateBentoStats() {
    const today = new Date();
    const currentMonth = today.getMonth();
    const currentYear = today.getFullYear();

    const firstDayOfWeek = new Date(today);
    firstDayOfWeek.setDate(today.getDate() - today.getDay());
    firstDayOfWeek.setHours(0, 0, 0, 0);

    const lastDayOfWeek = new Date(firstDayOfWeek);
    lastDayOfWeek.setDate(firstDayOfWeek.getDate() + 6);
    lastDayOfWeek.setHours(23, 59, 59, 999);

    let monthCount = 0;
    let weekCount = 0;

    events.forEach((e) => {
        const [y, m, d] = e.date.split("-");
        const evtDate = new Date(y, m - 1, d);

        if (
            evtDate.getMonth() === currentMonth &&
            evtDate.getFullYear() === currentYear
        ) {
            monthCount++;
        }
        if (evtDate >= firstDayOfWeek && evtDate <= lastDayOfWeek) {
            weekCount++;
        }
    });

    const statsEl = document.getElementById("stats-month");
    if (statsEl) statsEl.textContent = monthCount;
}

function updateNextMeeting() {
    if (countdownInterval) clearInterval(countdownInterval);
    const bento = document.getElementById("next-meeting-bento");
    if (!bento) return;

    const now = new Date();
    now.setHours(0, 0, 0, 0);
    const upcomingEvents = events
        .filter((e) => {
            if (e.status !== "upcoming") return false;
            const evtEnd = e.end_date
                ? new Date(e.end_date + "T00:00:00")
                : new Date(e.date + "T00:00:00");
            return evtEnd >= now;
        })
        .sort((a, b) => new Date(a.date) - new Date(b.date));

    if (upcomingEvents.length === 0) {
        bento.innerHTML = `
            <h3 style="color: var(--primary-blue); font-size: 14px; text-transform: uppercase; letter-spacing: 1px; font-weight: 700; margin-bottom: 5px;">NEXT SCHEDULE</h3>
            <div style="font-size: 20px; font-weight: 700; color: var(--text-muted); line-height: 1.2; text-align: center;">No upcoming schedules</div>
        `;
        return;
    }

    const nextEvent = upcomingEvents[0];
    const startDate = new Date(nextEvent.date + "T00:00:00");
    const daysLeft = Math.ceil((startDate - now) / (1000 * 60 * 60 * 24));
    const timeStr =
        daysLeft <= 0 ? "Today" : daysLeft === 1 ? "1 day" : `${daysLeft} days`;

    bento.innerHTML = `
        <h3 style="color: var(--primary-blue); font-size: 14px; text-transform: uppercase; letter-spacing: 1px; font-weight: 700; margin-bottom: 5px;">NEXT SCHEDULE</h3>
        <div style="color: #ef4444; font-weight: 600; font-size: 16px; margin-bottom: 5px;">Starts in ${timeStr}</div>
        <div style="font-size: 24px; font-weight: 700; color: var(--text-main); line-height: 1.2; text-align: center; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 100%; cursor: pointer;" onclick="openViewModal('${nextEvent.id}')">${nextEvent.title}</div>
    `;
}

function renderEventList() {
    eventListContainer.innerHTML = "";

    const term = searchInput.value.toLowerCase();
    const filterTime = filterTimeDropdown ? filterTimeDropdown.value : "all";
    const selectedMonth = monthFilterDropdown
        ? monthFilterDropdown.value
        : "all";

    const today = new Date();
    today.setHours(0, 0, 0, 0);

    let filteredEvents = events.filter((e) => {
        const matchesSearch =
            e.title.toLowerCase().includes(term) ||
            (e.description && e.description.toLowerCase().includes(term));

        // If there is a search term, ignore other filters
        if (term) return matchesSearch;

        // Month filter (Always applies if no search term)
        if (selectedMonth !== "all") {
            const start = new Date(e.date + "T00:00:00");
            const end = e.end_date ? new Date(e.end_date + "T00:00:00") : start;
            const targetMonthInt = parseInt(selectedMonth, 10);

            const eventMonths = [];
            let current = new Date(start);
            while (current <= end) {
                eventMonths.push(current.getMonth());
                current.setMonth(current.getMonth() + 1);
                current.setDate(1); // Jump to first of next month to avoid skipping
            }
            if (!eventMonths.includes(targetMonthInt)) return false;
        }

        if (!matchesSearch) return false;

        const start = new Date(e.date + "T00:00:00");
        const end = e.end_date ? new Date(e.end_date + "T00:00:00") : start;

        // Handling filters correctly
        if (activeStatusFilter === "completed") {
            return e.status === "completed";
        } else if (activeStatusFilter === "cancelled") {
            return e.status === "cancelled";
        } else {
            // Default Upcoming filter: Hide finished or cancelled
            if (e.status === "completed" || e.status === "cancelled")
                return false;
            // Include if not yet passed (ongoing or future)
            if (end < today) return false;
        }

        if (filterTime === "selected-day" && selectedFilterDate) {
            return e.date === selectedFilterDate;
        } else if (filterTime === "today") {
            return today >= start && today <= end;
        } else if (filterTime === "this-week") {
            const firstDay = new Date(today);
            firstDay.setDate(today.getDate() - today.getDay());
            const lastDay = new Date(firstDay);
            lastDay.setDate(firstDay.getDate() + 6);
            return start <= lastDay && end >= firstDay;
        } else if (filterTime === "this-month") {
            return (
                (start.getMonth() === today.getMonth() &&
                    start.getFullYear() === today.getFullYear()) ||
                (end.getMonth() === today.getMonth() &&
                    end.getFullYear() === today.getFullYear())
            );
        }

        return true;
    });

    if (filteredEvents.length === 0) {
        eventListContainer.innerHTML =
            '<div style="padding: 40px; text-align: center; color: var(--text-muted); font-size: var(--font-size-lg);" class="glass">No events found for this view.</div>';
        return;
    }

    filteredEvents.forEach((e) => {
        const row = document.createElement("div");
        row.className = "glass event-row";
        row.style.cssText =
            "display: flex; justify-content: space-between; align-items: center; padding: 25px 30px 40px; border-radius: var(--border-radius-lg); transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1); flex-shrink: 0; position: relative;";

        const dObj = new Date(`${e.date}T00:00:00`);
        const mNames = [
            "Jan",
            "Feb",
            "Mar",
            "Apr",
            "May",
            "Jun",
            "Jul",
            "Aug",
            "Sep",
            "Oct",
            "Nov",
            "Dec",
        ];
        const dayOfMonth = dObj.getDate();
        const monthStr = mNames[dObj.getMonth()];
        const endObj = e.end_date ? new Date(`${e.end_date}T00:00:00`) : null;
        const endDayOfMonth = endObj ? endObj.getDate() : null;
        const endMonthStr = endObj ? mNames[endObj.getMonth()] : null;

        const dateBadgeHtml = (() => {
            if (!e.end_date || e.end_date === e.date || !endObj) {
                return `
                    <div style="font-size: 16px; font-weight: 800; line-height: 1;">${dayOfMonth}</div>
                    <div style="font-size: 11px; text-transform: uppercase; font-weight: 800; opacity: 0.95; margin-top: 2px;">${monthStr}</div>
                `;
            }
            if (
                endObj.getMonth() === dObj.getMonth() &&
                endObj.getFullYear() === dObj.getFullYear()
            ) {
                // Same month: "13–19" then "Mar"
                return `
                    <div style="font-size: 16px; font-weight: 900; line-height: 1;">${dayOfMonth}–${endDayOfMonth}</div>
                    <div style="font-size: 9px; text-transform: uppercase; font-weight: 900; opacity: 0.95; margin-top: 2px;">${monthStr}</div>
                `;
            }
            // Different months: two lines "30 - Mar" then "1 - Apr"
            return `
                <div style="font-size: 12px; font-weight: 900; line-height: 1.1; white-space: nowrap;">${dayOfMonth} - ${monthStr}</div>
                <div style="font-size: 12px; font-weight: 900; line-height: 1.1; white-space: nowrap; margin-top: 2px;">${endDayOfMonth} - ${endMonthStr}</div>
            `;
        })();

        const logicalColor = getColorByDate(e.date, e.status, e.end_date);
        const badgeColor = e.color || logicalColor;

        let statusColorClass = "status-green";
        if (logicalColor === "#eab308") statusColorClass = "status-yellow";
        if (logicalColor === "#ea580c") statusColorClass = "status-amber"; // Soon (warm orange)
        if (logicalColor === "#3b82f6") {
            statusColorClass = "status-blue"; // Ongoing
        } else if (logicalColor === "#64748b") {
            statusColorClass = "status-gray"; // Finished
        } else if (e.status === "cancelled") {
            statusColorClass = ""; // No indicator for cancelled
        }

        let pillClass = "pill-upcoming";
        let statusLabel = "Upcoming";
        let titleClass = "";

        const todayDate = new Date();
        todayDate.setHours(0, 0, 0, 0);
        const evtStart = new Date(`${e.date}T00:00:00`);
        const evtEnd = e.end_date
            ? new Date(`${e.end_date}T00:00:00`)
            : evtStart;
        const isOngoing =
            todayDate >= evtStart &&
            todayDate <= evtEnd &&
            e.status === "upcoming";
        const isActuallyToday = todayDate.getTime() === evtStart.getTime();

        if (e.status === "completed") {
            pillClass = "pill-completed";
            statusLabel = "Finished";
        } else if (e.status === "cancelled") {
            pillClass = "pill-cancelled";
            statusLabel = "Cancelled";
            titleClass = "event-title-cancelled";
        } else if (isOngoing) {
            pillClass = "pill-ongoing";
            statusLabel = "Ongoing";
        }

        // Days left counter
        const daysUntil = getDaysUntil(todayDate, evtStart);
        let daysLeftLabel =
            isOngoing || isActuallyToday
                ? "Today"
                : `${daysUntil} day${daysUntil === 1 ? "" : "s"} left`;

        // Hide days left for finished or cancelled
        if (e.status === "completed" || e.status === "cancelled") {
            daysLeftLabel = "";
        }

        const dateRangeLabel =
            !e.end_date || e.end_date === e.date
                ? `${monthStr} ${dayOfMonth}`
                : endObj && endObj.getMonth() === dObj.getMonth()
                  ? `${monthStr} ${dayOfMonth}–${endDayOfMonth}`
                  : `${monthStr} ${dayOfMonth} – ${endMonthStr} ${endDayOfMonth}`;

        // Calculate contrast color for the date badge background
        const hex = badgeColor.replace("#", "");
        const r = parseInt(hex.substr(0, 2), 16);
        const g = parseInt(hex.substr(2, 2), 16);
        const b = parseInt(hex.substr(4, 2), 16);
        const luminance = (0.299 * r + 0.587 * g + 0.114 * b) / 255;
        const contrastTextColor = luminance > 0.6 ? "#0f172a" : "#ffffff";

        row.innerHTML = `
            <div class="status-line ${statusColorClass}"></div>
            <div style="position: absolute; right: 16px; top: 10px; font-size: 15px; font-weight: 700; color: ${logicalColor};">${daysLeftLabel}</div>
            <div style="display: flex; align-items: center; gap: 15px; flex: 1; pointer-events: none; min-width: 0;">
                <div style="background: ${badgeColor}; color: ${contrastTextColor}; border-radius: 8px; padding: 10px 10px; text-align: center; min-width: 78px; flex-shrink: 0;">
                    ${dateBadgeHtml}
                </div>
                <div style="flex: 1; min-width: 0;">
                    <h3 class="${titleClass}" style="font-size: 17px; font-weight: 700; color: var(--text-main); margin-bottom: 4px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 100%;">${e.title}</h3>
                    <div style="display: flex; gap: 10px; color: var(--text-muted); font-size: 16px; font-weight: 500;">
                        <span style="display: flex; align-items: center; gap: 3px;">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
                            ${dateRangeLabel}
                        </span>
                    </div>
                </div>
            </div>
            <div class="status-pill ${pillClass}" style="position: absolute; right: 16px; bottom: 10px;">${statusLabel}</div>
        `;

        row.onclick = () => openViewModal(e.id);

        eventListContainer.appendChild(row);
    });
}

function openModal(mode, eventId) {
    document.getElementById("event-form").reset();
    document.getElementById("event-id").value = "";
    const contextDateInput = document.getElementById("event-edit-context-date");
    if (contextDateInput) contextDateInput.value = "";
    const editContextLabel = document.getElementById("edit-context-date-label");
    if (editContextLabel) {
        editContextLabel.textContent = "";
        editContextLabel.style.display = "none";
    }

    const locEl = document.getElementById("event-location");
    const classEl = document.getElementById("event-classification");

    if (mode === "add") {
        document.getElementById("modal-title").textContent = "Add New Schedule";
        const newDate = selectedFilterDate || new Date().toISOString().split("T")[0];
        const dateInput = document.getElementById("event-date");
        if (dateInput._flatpickr) dateInput._flatpickr.setDate(newDate);
        else dateInput.value = newDate;
        
        const endDateInput = document.getElementById("event-end-date");
        if (endDateInput._flatpickr) endDateInput._flatpickr.clear();
        else endDateInput.value = "";
        
        document.getElementById("status-group").style.display = "none";
        
        currentDayOverrides = {};
        mainLocation = "";
        const sel = document.getElementById("day-location-selector");
        if (sel) sel.value = "general";
        const lab = document.getElementById("location-day-label");
        if (lab) lab.style.display = "none";
    } else if (mode === "edit") {
        document.getElementById("modal-title").textContent = "Edit Schedule";
        document.getElementById("status-group").style.display = "block";
        // Never show the per-day location dropdown in edit mode
        const container = document.getElementById("day-location-container");
        if (container) container.style.display = "none";
        const e = events.find((ev) => ev.id === eventId);
        if (e) {
            const ctxDate =
                (currentEditContext.eventId === eventId &&
                currentEditContext.date
                    ? currentEditContext.date
                    : null) || e.date;

            document.getElementById("event-id").value = e.id;
            document.getElementById("event-title").value = e.title;
            
            const dateInput = document.getElementById("event-date");
            if (dateInput._flatpickr) dateInput._flatpickr.setDate(e.date || "");
            else dateInput.value = e.date || "";
            
            const endDateInput = document.getElementById("event-end-date");
            if (endDateInput._flatpickr) endDateInput._flatpickr.setDate(e.end_date || "");
            else endDateInput.value = e.end_date || "";

            // In edit mode, we always edit the clicked day's location (no dropdown).
            // Use effective location for the context date (day override if present).
            const effLoc =
                (e.day_overrides &&
                ctxDate &&
                e.day_overrides[ctxDate] &&
                e.day_overrides[ctxDate].location !== undefined &&
                e.day_overrides[ctxDate].location !== null)
                    ? e.day_overrides[ctxDate].location
                    : e.location || "";
            if (locEl) locEl.value = effLoc || "";
            if (classEl) classEl.value = e.classification || "";
            document.getElementById("event-desc").value = e.description || "";
            document.getElementById("event-status").value =
                e.status || "upcoming";
            
            // Initialize per-day overrides
            currentDayOverrides = JSON.parse(JSON.stringify(e.day_overrides || {}));
            mainLocation = e.location || "";

            const lab = document.getElementById("location-day-label");
            if (lab) lab.style.display = "none";

            if (contextDateInput) contextDateInput.value = ctxDate || "";
            if (editContextLabel && ctxDate) {
                editContextLabel.textContent = getFullDateString(ctxDate);
                editContextLabel.style.display = "block";
            }
        }
    }
    
    // Trigger date range update
    const startInp = document.getElementById("event-date");
    if (startInp) startInp.dispatchEvent(new Event('change'));
    if (typeof window.updateDaySelector === 'function') window.updateDaySelector();

    if (eventModal) eventModal.classList.add("active");
    const focusEl = document.getElementById("event-title");
    if (focusEl) focusEl.focus();
}

function openViewModal(eventId, dateContext) {
    const e = events.find((ev) => ev.id === eventId);
    if (!e) return;
    if (!viewModal) return;

    const ctxDate = dateContext || e.date;
    const overrides =
        e.day_overrides && e.day_overrides[ctxDate]
            ? e.day_overrides[ctxDate]
            : {};
    const effTitle = overrides.title || e.title;
    const effLocation = overrides.location || e.location;
    const effClassification = overrides.classification || e.classification;
    const effDescription =
        overrides.description !== undefined && overrides.description !== null
            ? overrides.description
            : ctxDate === e.date
              ? e.description
              : "";

    currentViewContext = { eventId: e.id, date: ctxDate };
    currentViewOriginal = {
        title: effTitle || "",
        location: overrides.location || e.location || "",
        classification: overrides.classification || e.classification || "",
        date: e.date || "",
        end_date: e.end_date || "",
        color: normalizeScheduleColor(e.color || "#93c5fd"),
        details: effDescription || "",
    };

    const viewTitle = document.getElementById("view-title");
    const viewTitleText = document.getElementById("view-title-text");
    if (viewTitle) viewTitle.textContent = effTitle;
    if (viewTitleText) viewTitleText.textContent = effTitle;
    const titleInput = document.getElementById("view-title-input");
    if (titleInput) titleInput.value = effTitle || "";

    const dateText =
        e.end_date && e.end_date !== e.date
            ? `${getFullDateString(e.date)} – ${getFullDateString(e.end_date)}`
            : getFullDateString(e.date);
    const viewTopDate = document.getElementById("view-top-date");
    if (viewTopDate) viewTopDate.textContent = getFullDateString(ctxDate);
    const viewDate = document.getElementById("view-date");
    if (viewDate) viewDate.textContent = dateText;

    const viewLocation = document.getElementById("view-location");
    const viewClassification = document.getElementById("view-classification");
    if (viewLocation) viewLocation.textContent = effLocation || "—";
    if (viewClassification)
        viewClassification.textContent = effClassification || "—";

    const viewColorName = document.getElementById("view-color-name");
    const viewColorSwatch = document.getElementById("view-color-swatch");
    const cHex = normalizeScheduleColor(e.color || "#93c5fd");
    if (viewColorName) viewColorName.textContent = getEventColorName(cHex);
    if (viewColorSwatch) viewColorSwatch.style.background = cHex;

    // Status badge: detect ongoing (today is between start and end, status is upcoming)
    const badge = document.getElementById("view-status-badge");
    if (badge) {
        const todayForBadge = new Date();
        todayForBadge.setHours(0, 0, 0, 0);
        const evtStartForBadge = new Date(e.date + "T00:00:00");
        const evtEndForBadge = e.end_date
            ? new Date(e.end_date + "T00:00:00")
            : evtStartForBadge;
        const isOngoingForBadge =
            todayForBadge >= evtStartForBadge &&
            todayForBadge <= evtEndForBadge &&
            e.status === "upcoming";

        badge.classList.remove(
            "badge-upcoming",
            "badge-completed",
            "badge-cancelled",
            "badge-ongoing",
        );
        if (e.status === "completed") {
            badge.classList.add("badge-completed");
            badge.textContent = "✅ Finished";
        } else if (e.status === "cancelled") {
            badge.classList.add("badge-cancelled");
            badge.textContent = "❌ Cancelled";
        } else if (isOngoingForBadge) {
            badge.classList.add("badge-ongoing");
            badge.textContent = "🔵 Ongoing";
        } else {
            badge.classList.add("badge-upcoming");
            badge.textContent = "🟡 Upcoming";
        }
    }

    // reset any open editors
    ["title", "location", "classification", "date", "color", "details"].forEach(
        cancelViewField,
    );
    viewEditMode = false;
    updateViewEditModeUI();

    const cancelBtn = document.getElementById("btn-cancel-meeting");
    const editBtn = document.getElementById("btn-edit-view");
    const deleteBtn = document.getElementById("btn-delete-view");
    if (cancelBtn)
        cancelBtn.style.display =
            e.status === "upcoming" ? "inline-flex" : "none";
    if (editBtn)
        editBtn.style.display =
            e.status === "completed" ? "none" : "inline-flex";
    if (deleteBtn) deleteBtn.style.display = "inline-flex";

    const descCon = document.getElementById("view-desc-container");
    const descEl = document.getElementById("view-desc");
    if (descCon && descEl) {
        if (effDescription && String(effDescription).trim() !== "") {
            descEl.textContent = effDescription;
            descEl.style.display = "block";
        } else {
            descEl.style.display = "none";
        }
        descCon.style.display = "block"; // Always show container
    }
    const detailsInput = document.getElementById("view-details-input");
    if (detailsInput) detailsInput.value = currentViewOriginal.details || "";


    // wire inline edit buttons/actions (idempotent via onclick assignment)
    document.querySelectorAll(".view-inline-edit-btn").forEach((btn) => {
        btn.onclick = (ev) => {
            ev.stopPropagation();
            if (!viewEditMode) return;
            startViewField(btn.getAttribute("data-field"));
        };
    });
    document
        .querySelectorAll('[data-action="save"][data-field]')
        .forEach((btn) => {
            btn.onclick = (ev) => {
                ev.stopPropagation();
                saveViewField(btn.getAttribute("data-field"));
            };
        });
    document
        .querySelectorAll('[data-action="cancel"][data-field]')
        .forEach((btn) => {
            btn.onclick = (ev) => {
                ev.stopPropagation();
                cancelViewField(btn.getAttribute("data-field"));
            };
        });
    document.querySelectorAll(".view-color-rect").forEach((btn) => {
        btn.onclick = (ev) => {
            ev.stopPropagation();
            currentViewOriginal.color = normalizeScheduleColor(
                btn.getAttribute("data-hex"),
            );
            const sw = document.getElementById("view-color-swatch");
            const nm = document.getElementById("view-color-name");
            if (sw) sw.style.background = currentViewOriginal.color;
            if (nm)
                nm.textContent = getEventColorName(currentViewOriginal.color);
        };
    });

    const editDetailsBtn = document.getElementById("btn-edit-view");
    if (editDetailsBtn) {
        editDetailsBtn.onclick = (ev) => {
            ev.stopPropagation();
            closeModal("view-modal");
            currentEditContext = { eventId: e.id, date: ctxDate };
            setTimeout(() => {
                openModal("edit", e.id);
            }, 100);
        };
    }
    if (cancelBtn)
        cancelBtn.onclick = () => {
            Swal.fire({
                title: "Cancel Schedule?",
                text: "This will mark the schedule as cancelled.",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#ef4444",
                cancelButtonColor: "#94a3b8",
                confirmButtonText: "Yes, cancel it!",
                customClass: { container: "swal-on-top" },
            }).then((result) => {
                if (result.isConfirmed) {
                    cancelMeeting(e.id);
                    closeModal("view-modal");
                }
            });
        };
    const deleteViewBtn = document.getElementById("btn-delete-view");
    if (deleteViewBtn)
        deleteViewBtn.onclick = () => {
            // Use the existing red delete confirmation (deleteEvent)
            closeModal("view-modal");
            setTimeout(() => deleteEvent(e.id), 150);
        };

    viewModal.classList.add("active");
}

function updateViewEditModeUI() {
    document.querySelectorAll(".view-inline-edit-btn").forEach((btn) => {
        btn.style.display = viewEditMode ? "inline-flex" : "none";
    });
}

function openEditDayModal(eventId, dateStr) {
    const e = events.find((ev) => ev.id === eventId);
    if (!e) return;
    const dayOverrides = e.day_overrides || {};
    const override = dayOverrides[dateStr] || {};
    document.getElementById("edit-day-event-id").value = eventId;
    document.getElementById("edit-day-date").value = dateStr;
    document.getElementById("edit-day-modal-title").textContent =
        `Edit details for ${getFullDateString(dateStr)}`;
    const titleEl = document.getElementById("edit-day-title");
    const classEl = document.getElementById("edit-day-classification");
    if (titleEl) titleEl.value = override.title || e.title || "";
    document.getElementById("edit-day-location").value =
        override.location || e.location || "";
    if (classEl)
        classEl.value = override.classification || e.classification || "";
    document.getElementById("edit-day-desc").value =
        override.description !== undefined && override.description !== null
            ? override.description
            : dateStr === e.date
              ? e.description || ""
              : "";
    document.getElementById("edit-day-modal").classList.add("active");
}

async function saveEditDay() {
    const eventId = document.getElementById("edit-day-event-id").value;
    const dateStr = document.getElementById("edit-day-date").value;
    const titleEl = document.getElementById("edit-day-title");
    const location = document.getElementById("edit-day-location").value.trim();
    const classEl = document.getElementById("edit-day-classification");
    const description = document.getElementById("edit-day-desc").value.trim();
    const e = events.find((ev) => ev.id === eventId);
    if (!e) return;

    const dayOverrides = { ...(e.day_overrides || {}) };
    dayOverrides[dateStr] = {
        title: titleEl ? titleEl.value.trim() || null : null,
        location: location || null,
        classification: classEl ? classEl.value.trim() || null : null,
        description: description || null,
    };

    try {
        const res = await fetch(API_BASE, {
            method: "POST",
            credentials: "include",
            headers: getFetchHeaders("POST"),
            body: JSON.stringify({
                id: e.id,
                title: e.title,
                date: e.date,
                end_date: e.end_date,
                location: e.location,
                classification: e.classification,
                description: e.description,
                status: e.status,
                color: e.color,
                day_overrides: dayOverrides,
            }),
        });
        const result = await res.json();
        if (result.status === "success") {
            closeModal("edit-day-modal");
            fetchEvents();
            openViewModal(eventId, dateStr);
        }
    } catch (err) {
        console.error(err);
    }
}

function closeModal(modalId) {
    document.getElementById(modalId).classList.remove("active");
}

function handleCancelModal() {
    closeModal("event-modal");
    const id = document.getElementById("event-id").value;
    if (id) {
        setTimeout(() => openViewModal(id), 150);
    }
}

async function handleEventSubmit(ev) {
    ev.preventDefault();

    const id = document.getElementById("event-id").value;
    const title = document.getElementById("event-title").value;
    const date = document.getElementById("event-date").value;
    const end_date = document.getElementById("event-end-date").value || null;
    const classification = document.getElementById("event-classification")
        ? document.getElementById("event-classification").value
        : null;
    const description = document.getElementById("event-desc").value;
    const status = document.getElementById("event-status")
        ? document.getElementById("event-status").value
        : "upcoming";
    const editCtxDate =
        document.getElementById("event-edit-context-date")?.value || null;

    // Date validation: end date must not be before start date
    if (end_date && end_date < date) {
        Swal.fire({
            icon: "error",
            title: "Invalid Date Range",
            text: "The end date cannot be before the start date. Please fix the dates and try again.",
            confirmButtonColor: "#2563eb",
            customClass: { container: "swal-on-top" },
        });
        return; // do NOT close the modal, keep it open
    }

    // Auto-assign color: keep existing color for edits, pick next for new
    let color;
    if (id) {
        // editing: preserve the existing schedule color
        const existing = events.find((e) => e.id === id);
        color = existing ? normalizeScheduleColor(existing.color || "#93c5fd") : getNextAutoColor();
    } else {
        color = getNextAutoColor();
    }

    const saveBtn = document.getElementById("save-event-btn");
    if (saveBtn) saveBtn.disabled = true;

    try {
        // If editing, apply the location change ONLY to the clicked day via day_overrides.
        // This prevents accidentally changing the location across the whole schedule.
        if (id && editCtxDate) {
            const existing = events.find((e) => e.id === id);
            const isMultiDay =
                existing && existing.end_date && existing.end_date !== existing.date;
            if (isMultiDay) {
                const locationInput = document.getElementById("event-location");
                const nextLoc = (locationInput?.value || "").trim();
                const nextOverrides = { ...(currentDayOverrides || {}) };
                const day = { ...(nextOverrides[editCtxDate] || {}) };
                day.location = nextLoc ? nextLoc : null;
                nextOverrides[editCtxDate] = day;
                currentDayOverrides = nextOverrides;
            } else {
                // Single day schedule: treat as schedule-level location
                mainLocation =
                    (document.getElementById("event-location")?.value || "").trim();
            }
        } else {
            // Add mode: keep existing behavior (schedule-level location)
            mainLocation =
                (document.getElementById("event-location")?.value || "").trim();
        }

        const payload = {
            id,
            title,
            date,
            end_date,
            location: mainLocation,
            classification,
            color,
            description,
            status,
            day_overrides: currentDayOverrides,
        };

        const res = await fetch(API_BASE, {
            method: "POST",
            credentials: "include",
            headers: getFetchHeaders("POST"),
            body: JSON.stringify(payload),
        });

        if (res.status === 401) {
            window.location.href = "/";
            return;
        }
        if (res.status === 419) {
            window.location.reload();
            return;
        }

        let result = null;
        try {
            result = await res.json();
        } catch {
            // ignore JSON parse error; handled below
        }

        if (res.ok && result?.status === "success") {
            closeModal("event-modal");
            Swal.fire({
                title: id
                    ? "Schedule updated successfully!"
                    : "Schedule created successfully!",
                icon: "success",
                toast: true,
                position: "top",
                showConfirmButton: false,
                timer: 2000,
                timerProgressBar: true,
                showClass: {
                    popup: "animate__animated animate__fadeInDown animate__faster",
                },
                hideClass: {
                    popup: "animate__animated animate__fadeOutUp animate__faster",
                },
            });

            await fetchEvents();
            // For edits, immediately show the updated schedule details
            if (id) {
                setTimeout(() => openViewModal(id, editCtxDate || undefined), 50);
            }
            return;
        }

        const errorMsg =
            result?.message ||
            (res.status === 422
                ? Object.values(result?.errors || {})?.flat()?.[0] ||
                  "Please check your inputs and try again."
                : "Failed to save Schedule");
        Swal.fire("Error!", errorMsg, "error");
    } catch (err) {
        console.error(err);
        Swal.fire("Error!", "Server Error", "error");
    } finally {
        if (saveBtn) saveBtn.disabled = false;
    }
}

window.reminderShown = window.reminderShown || false;

const REMINDER_TARGET_DAYS = new Set([3, 5, 7]); // workdays before event

function getUpcomingReminders(eventsList) {
    const evs = eventsList || events;
    const now = new Date();
    now.setHours(0, 0, 0, 0);
    return evs
        .filter((e) => {
            if (e.status !== "upcoming") return false;
            const evtStart = new Date(e.date + "T00:00:00");
            if (evtStart < now) return false;
            const workDaysLeft = getDaysUntil(now, evtStart);
            return REMINDER_TARGET_DAYS.has(workDaysLeft);
        })
        .map((e) => {
            const evtStart = new Date(e.date + "T00:00:00");
            const workDaysLeft = getDaysUntil(now, evtStart);
            return { ...e, _workDaysLeft: workDaysLeft };
        })
        .sort(
            (a, b) =>
                a._workDaysLeft - b._workDaysLeft ||
                new Date(a.date) - new Date(b.date),
        );
}

function openNotificationModal(eventsList, options = {}) {
    const { showEvenIfEmpty = false, onItemClick } = options;
    const alertModal = document.getElementById("custom-alert-modal");
    const alertOk = document.getElementById("custom-alert-ok");
    const alertList = document.getElementById("custom-alert-list");
    if (!alertModal || !alertOk || !alertList) return;

    const upcoming = getUpcomingReminders(eventsList);

    if (upcoming.length === 0 && !showEvenIfEmpty) {
        alertModal.classList.remove("active");
        return;
    }

    let listHtml;
    if (upcoming.length === 0) {
        listHtml =
            '<div style="padding: 24px; text-align: center; color: var(--text-muted); font-weight: 600;">No upcoming reminders. Schedules 3, 5, or 7 work days away will appear here.</div>';
    } else {
        listHtml = upcoming
            .map((e) => {
                const displayTitle =
                    (e.title || "").length > 40
                        ? (e.title || "").slice(0, 40) + "…"
                        : e.title || "";
                const dateLabel =
                    e.end_date && e.end_date !== e.date
                        ? `${getFullDateString(e.date)} – ${getFullDateString(e.end_date)}`
                        : getFullDateString(e.date);
                const itemColor = getColorByDate(e.date, e.status, e.end_date);
                const daysLabel =
                    e._workDaysLeft === 0
                        ? "Today"
                        : `${e._workDaysLeft} work day${e._workDaysLeft === 1 ? "" : "s"} left`;
                return `
                <div class="reminder-item-real" data-event-id="${e.id}" style="display:flex; gap:12px; align-items:stretch; cursor:pointer; padding: 12px; border-radius: 12px; background: #ffffff; border: 1px solid #e2e8f0; transition: transform 0.2s, box-shadow 0.2s;">
                    <div style="width: 6px; border-radius: 6px; background:${itemColor}; flex-shrink:0;"></div>
                    <div style="min-width:0; flex: 1;">
                        <div style="font-weight:800; color: ${itemColor}; font-size: 13px; text-transform: uppercase;">${daysLabel}</div>
                        <div style="font-weight:700; color: var(--text-main); font-size: 15px; margin: 4px 0; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 100%;">${displayTitle}</div>
                        <div style="color: #94a3b8; font-weight:600; font-size: 13px;">${dateLabel}</div>
                    </div>
                </div>
            `;
            })
            .join("");
    }

    alertList.innerHTML = listHtml;

    if (upcoming.length > 0) {
        const openView =
            onItemClick ||
            ((id) => {
                if (typeof openViewModal === "function") openViewModal(id);
            });
        alertList.querySelectorAll(".reminder-item-real").forEach((item) => {
            item.addEventListener("mouseenter", () => {
                item.style.transform = "translateY(-2px)";
                item.style.boxShadow = "0 6px 15px rgba(0,0,0,0.06)";
            });
            item.addEventListener("mouseleave", () => {
                item.style.transform = "none";
                item.style.boxShadow = "none";
            });
            item.addEventListener("click", (ev) => {
                ev.stopPropagation();
                const id = item.getAttribute("data-event-id");
                if (id) openView(id);
            });
        });
    }

    alertModal.classList.add("active");

    const newOk = alertOk.cloneNode(true);
    alertOk.parentNode.replaceChild(newOk, alertOk);
    newOk.addEventListener("click", () =>
        alertModal.classList.remove("active"),
    );
}

// Expose for profile page notification button
window.openNotificationModal = openNotificationModal;
window.getUpcomingReminders = getUpcomingReminders;

function updateNotificationBadge() {
    const badge = document.getElementById("notification-badge");
    if (!badge) return;
    const upcoming = getUpcomingReminders();
    if (upcoming.length > 0) {
        badge.textContent = upcoming.length > 99 ? "99+" : upcoming.length;
        badge.style.display = "flex";
    } else {
        badge.style.display = "none";
    }
}

function checkUpcomingReminders() {
    if (window.reminderShown) return;
    const upcoming = getUpcomingReminders();
    if (upcoming.length === 0) return;
    openNotificationModal(events, { showEvenIfEmpty: false });
    window.reminderShown = true;
}

async function cancelMeeting(id) {
    const e = events.find((ev) => ev.id === id);
    if (!e) return;

    try {
        const res = await fetch(API_BASE, {
            method: "POST",
            credentials: "include",
            headers: getFetchHeaders("POST"),
            body: JSON.stringify({
                id: e.id,
                title: e.title,
                date: e.date,
                end_date: e.end_date,
                location: e.location,
                classification: e.classification,
                description: e.description,
                status: "cancelled",
                color: e.color,
                day_overrides: e.day_overrides,
            }),
        });
        const result = await res.json();

        if (result.status === "success") {
            Swal.fire({
                title: "Meeting Cancelled",
                icon: "success",
                toast: true,
                position: "top",
                showConfirmButton: false,
                timer: 2000,
                timerProgressBar: true,
            });
            fetchEvents();
        }
    } catch (err) {
        console.error(err);
    }
}


function deleteEvent(id) {
    Swal.fire({
        title: "Delete Event",
        text: "Are you sure you want to delete this event?",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#ef4444",
        cancelButtonColor: "#94a3b8",
        confirmButtonText: "Yes, delete it!",
        customClass: { container: "swal-on-top" },
    }).then(async (result) => {
        if (result.isConfirmed) {
            try {
                const res = await fetch(`${API_BASE}/${id}`, {
                    method: "DELETE",
                    credentials: "include",
                    headers: getFetchHeaders("DELETE"),
                });
                const data = await res.json();

                if (data.status === "success") {
                    Swal.fire({
                        title: "Event deleted successfully!",
                        icon: "success",
                        toast: true,
                        position: "top",
                        showConfirmButton: false,
                        timer: 2000,
                        timerProgressBar: true,
                        showClass: {
                            popup: "animate__animated animate__fadeInDown animate__faster",
                        },
                        hideClass: {
                            popup: "animate__animated animate__fadeOutUp animate__faster",
                        },
                    });
                    fetchEvents();
                } else {
                    Swal.fire("Error!", "Failed to delete event", "error");
                }
            } catch (err) {
                console.error(err);
                Swal.fire("Error!", "Server Error", "error");
            }
        }
    });
}
