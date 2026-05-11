const eventConfigEl = document.getElementById('event-config');
const eventId = eventConfigEl?.dataset.eventId || '';
const requireActionPrompts = eventConfigEl?.dataset.requireActionPrompts !== '0';
const initialStats = {
    checkedIn: Number(eventConfigEl?.dataset.checkedIn || '0'),
    absent: Number(eventConfigEl?.dataset.absent || '0'),
    totalStudents: Number(eventConfigEl?.dataset.totalStudents || '0'),
};

let sessionsData;
let attendanceData = [];
let dailyCurrentPage = 1;
const dailyRowsPerPage = 30;

function formatTime(dateString) {
    if (!dateString) return '-';
    const timeInDate = new Date(dateString);
    if (Number.isNaN(timeInDate.getTime())) return '-';

    const displayHours = timeInDate.getHours() % 12 || 12;
    const minutes = String(timeInDate.getMinutes()).padStart(2, '0');
    const ampm = timeInDate.getHours() >= 12 ? 'PM' : 'AM';
    return `${displayHours}:${minutes} ${ampm}`;
}

function getStatusBadge(status) {
    if (status === 'present') {
        return '<span class="px-3 py-1 bg-secondary-fixed text-on-secondary-fixed rounded-full text-xs font-semibold">Present</span>';
    }
    if (status === 'left_session') {
        return '<span class="px-3 py-1 bg-tertiary-container text-on-tertiary-container rounded-full text-xs font-semibold">On Break</span>';
    }
    return '<span class="px-3 py-1 bg-error text-on-error rounded-full text-xs font-semibold">Absent</span>';
}

function updateDailyPaginationControls(totalMatching, startIndex, endIndex) {
    const pageInfo = document.getElementById('daily-pagination-info');
    const pageIndicator = document.getElementById('daily-page-indicator');
    const prevButton = document.getElementById('daily-page-prev');
    const nextButton = document.getElementById('daily-page-next');

    const totalPages = Math.max(1, Math.ceil(totalMatching / dailyRowsPerPage));
    const normalizedStart = totalMatching === 0 ? 0 : startIndex;
    const normalizedEnd = totalMatching === 0 ? 0 : endIndex;

    if (pageInfo) {
        pageInfo.innerHTML = `Showing <strong>${normalizedStart}</strong> to <strong>${normalizedEnd}</strong> of <strong>${totalMatching}</strong> results`;
    }

    if (pageIndicator) {
        pageIndicator.textContent = `Page ${dailyCurrentPage} of ${totalPages}`;
    }

    if (prevButton) {
        prevButton.disabled = dailyCurrentPage <= 1;
    }

    if (nextButton) {
        nextButton.disabled = dailyCurrentPage >= totalPages;
    }
}

function attachDailyPaginationListeners() {
    const prevButton = document.getElementById('daily-page-prev');
    const nextButton = document.getElementById('daily-page-next');

    if (prevButton) {
        prevButton.addEventListener('click', function() {
            if (dailyCurrentPage > 1) {
                dailyCurrentPage -= 1;
                filterDailyAttendance(false);
            }
        });
    }

    if (nextButton) {
        nextButton.addEventListener('click', function() {
            dailyCurrentPage += 1;
            filterDailyAttendance(false);
        });
    }
}

function createDailyAttendanceRow(record) {
    const tr = document.createElement('tr');
    tr.className = 'hover:bg-surface-container-low/50 transition-colors daily-attendance-row';
    if (String(record.day_num) !== String(currentDay)) {
        tr.classList.add('hidden');
        tr.style.display = 'none';
    }
    tr.setAttribute('data-day', record.day_num);
    tr.setAttribute('data-session-id', record.session_id || '');
    tr.setAttribute('data-student-id', record.student_id);
    tr.setAttribute('data-name', escapeHtml((record.student_name || '').toLowerCase()));
    tr.setAttribute('data-status', record.status || 'absent');

    const timeIn = formatTime(record.time_in);
    const program = escapeHtml(record.student_program || '-');
    const section = escapeHtml(record.section || '-');
    const name = escapeHtml(record.student_name || 'Unknown');
    const studentId = escapeHtml(record.student_id || '');
    const duration = escapeHtml(record.formatted_duration || '-');
    const cycles = record.cycles_count > 0 ? escapeHtml(String(record.cycles_count)) : '-';

    let rowHtml = `
        <td class="py-5 px-6">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-full bg-primary-fixed text-on-primary-fixed flex items-center justify-center font-bold text-sm">${escapeHtml(name.slice(0, 2).toUpperCase())}</div>
                <span class="font-semibold text-on-surface text-base">${name}</span>
            </div>
        </td>
        <td class="py-5 px-6 text-on-surface-variant text-base font-medium">${studentId}</td>
        <td class="py-5 px-6 text-on-surface-variant text-base font-medium">${section}</td>
        <td class="py-5 px-6 text-on-surface-variant text-base font-medium">${program}</td>
        <td class="py-5 px-6 text-on-surface-variant text-center font-medium text-base">${timeIn}</td>
    `;

    if (requireActionPrompts) {
        rowHtml += `
            <td class="py-5 px-6 text-on-surface-variant text-center font-medium text-base">${duration}</td>
            <td class="py-5 px-6 text-on-surface-variant text-center font-medium text-base">${cycles}</td>
        `;
    }

    rowHtml += `
        <td class="py-5 px-6 text-center">${getStatusBadge(record.status)}</td>
    `;

    tr.innerHTML = rowHtml;
    return tr;
}

    document.addEventListener('DOMContentLoaded', function() {
        const sessionsDataEl = document.getElementById('sessions-data');
        sessionsData = JSON.parse(sessionsDataEl?.getAttribute('data-sessions') || '{}');
        // Initialize Lucide Icons
        if (typeof lucide !== 'undefined') {
            lucide.createIcons();
        }
        
        const progressBar = document.querySelector('[data-width]');
        if (progressBar) {
            const width = progressBar.getAttribute('data-width');
            progressBar.style.width = width + '%';
        }

        // Export Dropdown Toggle
        const exportDropdownBtn = document.getElementById('export-dropdown-btn');
        const exportDropdownMenu = document.getElementById('export-dropdown-menu');

        if (exportDropdownBtn && exportDropdownMenu) {
            exportDropdownBtn.addEventListener('click', function(e) {
                e.stopPropagation();
                exportDropdownMenu.classList.toggle('hidden');
            });

            document.addEventListener('click', function(e) {
                if (!exportDropdownBtn.contains(e.target) && !exportDropdownMenu.contains(e.target)) {
                    exportDropdownMenu.classList.add('hidden');
                }
            });
        }

        // Attendee List Export Dropdown Toggle
        const attendeeExportBtn = document.getElementById('attendee-export-dropdown-btn');
        const attendeeExportMenu = document.getElementById('attendee-export-dropdown-menu');

        if (attendeeExportBtn && attendeeExportMenu) {
            attendeeExportBtn.addEventListener('click', function(e) {
                e.stopPropagation();
                attendeeExportMenu.classList.toggle('hidden');
            });

            document.addEventListener('click', function(e) {
                if (!attendeeExportBtn.contains(e.target) && !attendeeExportMenu.contains(e.target)) {
                    attendeeExportMenu.classList.add('hidden');
                }
            });
        }

        // Attendee Import Button
        const attendeeImportFile = document.getElementById('attendee-import-file');

        if (attendeeImportFile) {
            attendeeImportFile.addEventListener('change', function() {
                if (this.files.length > 0) {
                    importAttendeeList(this.files[0]);
                }
            });
        }

        // Session Selector Change Listener
        document.querySelectorAll('.session-selector').forEach(selector => {
            selector.addEventListener('change', function() {
                filterSessionAttendance(this);
            });
        });

        // Tab switching functionality
        const tabButtons = document.querySelectorAll('.tab-btn');
        const tabContents = document.querySelectorAll('.tab-content');
        const markAttendanceSection = document.getElementById('mark-attendance-section');
        const openMarkAttendanceBtn = document.getElementById('openMarkAttendanceBtn');

        tabButtons.forEach(button => {
            button.addEventListener('click', function() {
                const tabName = this.getAttribute('data-tab');
                
                // Update button styles
                tabButtons.forEach(btn => {
                    btn.classList.remove('text-primary', 'border-b-2', 'border-primary');
                    btn.classList.add('text-on-surface-variant', 'border-b-2', 'border-transparent');
                });
                
                this.classList.remove('text-on-surface-variant', 'border-b-2', 'border-transparent');
                this.classList.add('text-primary', 'border-b-2', 'border-primary');
                
                // Show/hide tab contents
                tabContents.forEach(content => {
                    content.classList.add('hidden');
                    content.style.display = 'none';
                });
                
                const activeTab = document.getElementById(tabName + '-tab');
                if (activeTab) {
                    activeTab.classList.remove('hidden');
                    activeTab.style.display = 'flex';
                }
                
                // Hide mark attendance section when switching tabs
                if (markAttendanceSection && tabName !== 'daily-attendance') {
                    markAttendanceSection.classList.add('hidden');
                }
                
                // Show/hide toggle buttons based on active tab
                const attendeeToggleBtn = document.getElementById('toggle-attendee-filters-btn');
                const dailyToggleBtn = document.getElementById('toggle-daily-filters-btn');
                if (tabName === 'attendee-list') {
                    attendeeToggleBtn.classList.remove('hidden');
                    dailyToggleBtn.classList.add('hidden');
                } else if (tabName === 'daily-attendance') {
                    attendeeToggleBtn.classList.add('hidden');
                    dailyToggleBtn.classList.remove('hidden');
                }
            });
        });

        // Mark Attendance button toggle - Open as Modal
        if (openMarkAttendanceBtn) {
            openMarkAttendanceBtn.addEventListener('click', function(e) {
                e.preventDefault();
                openMarkAttendanceModal();
            });
        }

        // Close Mark Attendance button - Keep for modal close
        const closeMarkAttendanceBtn = document.getElementById('closeMarkAttendanceBtn');
        if (closeMarkAttendanceBtn) {
            closeMarkAttendanceBtn.addEventListener('click', function(e) {
                e.preventDefault();
                const modal = document.getElementById('markAttendanceModal');
                if (modal) {
                    modal.remove();
                }
            });
        }

        // Handle barcode view button clicks
        document.querySelectorAll('.viewBarcodeBtn').forEach(button => {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                const studentId = this.getAttribute('data-student-id');
                const studentName = this.getAttribute('data-student-name');
                const studentRfid = this.getAttribute('data-student-rfid');
                const studentSection = this.getAttribute('data-student-section');
                showBarcodeModal(studentId, studentName, studentRfid, studentSection);
            });
        });

        // Initialize delete listeners
        attachDeleteListeners();

        // Attendee List Tab - Search functionality
        const attendeeSearchInput = document.querySelector('#attendee-list-tab input[type="text"]');
        
        if (attendeeSearchInput) {
            attendeeSearchInput.addEventListener('keyup', filterAttendeeList);
        }

        // Add Attendee Button
        const addAttendeeBtn = document.getElementById('addAttendeeBtn');
        if (addAttendeeBtn) {
            addAttendeeBtn.addEventListener('click', function(e) {
                e.preventDefault();
                addAttendeeToEvent();
            });
        }

        // Manual Add Toggle Button
        const manualAddBtn = document.getElementById('manual-add-btn');
        const addStudentForm = document.getElementById('add-student-form');
        
        if (manualAddBtn && addStudentForm) {
            manualAddBtn.addEventListener('click', function(e) {
                e.preventDefault();
                addStudentForm.classList.toggle('hidden');
                
                // Toggle button text and style
                if (addStudentForm.classList.contains('hidden')) {
                    manualAddBtn.innerHTML = '<span class="material-symbols-outlined text-lg">add</span>Manual Add';
                    manualAddBtn.classList.remove('bg-error', 'hover:bg-error');
                    manualAddBtn.classList.add('bg-primary', 'hover:bg-primary-fixed');
                } else {
                    manualAddBtn.innerHTML = '<span class="material-symbols-outlined text-lg">close</span>Cancel';
                    manualAddBtn.classList.remove('bg-primary', 'hover:bg-primary-fixed');
                    manualAddBtn.classList.add('bg-error', 'hover:bg-error');
                }
            });
        }

        // Import Toggle Button
        const importBtn = document.getElementById('attendee-import-btn');
        const importCsvForm = document.getElementById('import-csv-form');
        const selectCsvFileBtn = document.getElementById('select-csv-file-btn');
        const downloadTemplateBtn = document.getElementById('download-template-btn');
        
        if (importBtn && importCsvForm) {
            importBtn.addEventListener('click', function(e) {
                e.preventDefault();
                importCsvForm.classList.toggle('hidden');
                
                // Toggle button text and style
                if (importCsvForm.classList.contains('hidden')) {
                    importBtn.innerHTML = '<span class="material-symbols-outlined text-lg">upload</span>Import';
                    importBtn.classList.remove('bg-error', 'hover:bg-error', 'text-on-error');
                    importBtn.classList.remove('border-outline');
                    importBtn.classList.add('text-on-surface', 'hover:bg-surface-container', 'border-transparent');
                } else {
                    importBtn.innerHTML = '<span class="material-symbols-outlined text-lg">close</span>Cancel';
                    importBtn.classList.remove('text-on-surface', 'hover:bg-surface-container');
                    importBtn.classList.remove('border-outline');
                    importBtn.classList.add('border-transparent');
                    importBtn.classList.add('bg-error', 'hover:bg-error', 'text-on-error');
                }
            });
        }

        // Select CSV File Button
        if (selectCsvFileBtn && attendeeImportFile) {
            selectCsvFileBtn.addEventListener('click', function() {
                attendeeImportFile.click();
            });
        }

        // Download Template Button
        if (downloadTemplateBtn) {
            downloadTemplateBtn.addEventListener('click', function() {
                downloadCsvTemplate();
            });
        }

        // Daily Attendance Tab - Search and Filter functionality
        const dailySearchInput = document.getElementById('daily-search');
        const dailyStatusFilter = document.getElementById('daily-status-filter');
        const dailySessionFilter = document.getElementById('daily-session-filter');
        // Daily Attendance Controls Toggle
        const toggleDailyBtn = document.getElementById('toggle-daily-filters-btn');
        const dailyControlsContent = document.getElementById('daily-controls-content');
        
        if (toggleDailyBtn && dailyControlsContent) {
            toggleDailyBtn.addEventListener('click', function(e) {
                e.preventDefault();
                dailyControlsContent.classList.toggle('hidden');
                
                const icon = toggleDailyBtn.querySelector('.material-symbols-outlined');
                if (dailyControlsContent.classList.contains('hidden')) {
                    icon.textContent = 'unfold_less';
                    toggleDailyBtn.style.opacity = '0.6';
                } else {
                    icon.textContent = 'unfold_more';
                    toggleDailyBtn.style.opacity = '1';
                }
            });
        }
        
        // Attendee List Controls Toggle
        const toggleAttendeeBtn = document.getElementById('toggle-attendee-filters-btn');
        const attendeeFiltersContent = document.getElementById('attendee-filters-content');
        
        if (toggleAttendeeBtn && attendeeFiltersContent) {
            toggleAttendeeBtn.addEventListener('click', function(e) {
                e.preventDefault();
                attendeeFiltersContent.classList.toggle('hidden');
                
                const icon = toggleAttendeeBtn.querySelector('.material-symbols-outlined');
                if (attendeeFiltersContent.classList.contains('hidden')) {
                    icon.textContent = 'unfold_less';
                    toggleAttendeeBtn.style.opacity = '0.6';
                } else {
                    icon.textContent = 'unfold_more';
                    toggleAttendeeBtn.style.opacity = '1';
                }
            });
        }
        
        if (dailySearchInput) {
            dailySearchInput.addEventListener('keyup', filterDailyAttendance);
        }
        
        if (dailyStatusFilter) {
            dailyStatusFilter.addEventListener('change', filterDailyAttendance);
        }

        if (dailySessionFilter) {
            dailySessionFilter.addEventListener('change', filterDailyAttendance);
        }

        attachDailyPaginationListeners();
        attachAttendeePaginationListeners();
        attachViewBarcodeListeners();

        // Initialize session dropdown for day 1
        updateSessionDropdown(1);

        // Load and sync attendance data from API
        loadDailyAttendanceData(eventId);
        
        // Initialize footer session button
        updateFooterSessionButton();
        
        // Refresh attendance data every 3 seconds to keep in sync with scan page
        setInterval(() => {
            loadDailyAttendanceData(eventId);
        }, 3000);

        // Day Toggle Buttons - Add click listeners
        document.querySelectorAll('.day-toggle-btn').forEach(button => {
            button.addEventListener('click', function() {
                const dayNum = this.getAttribute('data-day');
                switchDay(dayNum);
            });
        });

        // Session Day Toggle Buttons - Add click listeners
        document.querySelectorAll('.session-day-toggle-btn').forEach(button => {
            button.addEventListener('click', function() {
                const dayNum = this.getAttribute('data-day');
                switchSessionDay(dayNum);
            });
        });

        // Session Management Buttons - Add click listeners
        const endCurrentSessionBtn = document.getElementById('endCurrentSessionBtn');
        const startNextSessionBtn = document.getElementById('startNextSessionBtn');
        
        if (endCurrentSessionBtn) {
            endCurrentSessionBtn.addEventListener('click', function() {
                endCurrentSession();
            });
        }
        
        if (startNextSessionBtn) {
            startNextSessionBtn.addEventListener('click', function() {
                startNextSession();
            });
        }

        // Sessions Modal - Add click listeners
        const openSessionsModalBtn = document.getElementById('openSessionsModalBtn');
        const closeSessionsModalBtn = document.getElementById('closeSessionsModalBtn');
        const sessionsModal = document.getElementById('sessionsModal');
        const startNextSessionBtnModal = document.getElementById('startNextSessionBtnModal');
        const endCurrentSessionBtnModal = document.getElementById('endCurrentSessionBtnModal');

        if (openSessionsModalBtn) {
            openSessionsModalBtn.addEventListener('click', function(e) {
                e.preventDefault();
                sessionsModal.classList.remove('hidden');
            });
        }

        if (closeSessionsModalBtn) {
            closeSessionsModalBtn.addEventListener('click', function(e) {
                e.preventDefault();
                sessionsModal.classList.add('hidden');
            });
        }

        if (sessionsModal) {
            sessionsModal.addEventListener('click', function(e) {
                if (e.target === sessionsModal) {
                    sessionsModal.classList.add('hidden');
                }
            });
        }

        if (endCurrentSessionBtnModal) {
            endCurrentSessionBtnModal.addEventListener('click', function() {
                endCurrentSession();
            });
        }
        
        if (startNextSessionBtnModal) {
            startNextSessionBtnModal.addEventListener('click', function() {
                startNextSession();
            });
        }

        // Check if event date hasn't come yet and disable start buttons
        function checkEventDateAndDisableStartButton() {
            const eventStartDateStr = document.querySelector('[data-event-start-date]')?.getAttribute('data-event-start-date');
            if (!eventStartDateStr) return;

            const eventStartDate = new Date(eventStartDateStr);
            const today = new Date();
            today.setHours(0, 0, 0, 0);
            eventStartDate.setHours(0, 0, 0, 0);

            if (today < eventStartDate) {
                // Event date hasn't come yet, disable start buttons
                const startBtns = [
                    document.getElementById('startNextSessionBtn'),
                    document.getElementById('startNextSessionBtnModal')
                ];

                startBtns.forEach(btn => {
                    if (btn) {
                        btn.disabled = true;
                        btn.classList.add('opacity-50', 'cursor-not-allowed');
                        btn.classList.remove('hover:bg-primary-fixed');
                        btn.title = `This event starts on ${eventStartDate.toLocaleDateString()}. Cannot start sessions before the event date.`;
                    }
                });
            }
        }

        // Check event date on page load
        checkEventDateAndDisableStartButton();
        let currentSessionModalDay = 1;

        function updateSessionModalDayFilter(dayNum) {
            currentSessionModalDay = dayNum;
            
            // Update day button styling
            document.querySelectorAll('.session-modal-day-toggle').forEach(btn => {
                if (btn.getAttribute('data-day') === String(dayNum)) {
                    btn.classList.remove('bg-surface-container', 'text-on-surface-variant', 'hover:bg-surface-container-high');
                    btn.classList.add('bg-error', 'text-on-error');
                } else {
                    btn.classList.remove('bg-error', 'text-on-error');
                    btn.classList.add('bg-surface-container', 'text-on-surface-variant', 'hover:bg-surface-container-high');
                }
            });
            
            // Filter session cards by day
            document.querySelectorAll('.session-modal-card').forEach(card => {
                if (card.getAttribute('data-day') === String(dayNum)) {
                    card.style.display = 'block';
                } else {
                    card.style.display = 'none';
                }
            });
            
            // Update button text based on active session in selected day
            updateSessionModalButtonState();
        }

        function updateSessionModalButtonState() {
            const startBtn = document.getElementById('startNextSessionBtnModal');
            const endBtn = document.getElementById('endCurrentSessionBtnModal');
            
            if (!startBtn || !endBtn) return;
            
            // Check if there's an active session on the current day
            const daySession = sessionsData && sessionsData[currentSessionModalDay] && 
                              sessionsData[currentSessionModalDay].find(s => s.status === 'active');
            
            // Check if all sessions for current day are completed
            const daySessions = sessionsData[currentSessionModalDay] || [];
            const allSessionsCompleted = daySessions.length > 0 && 
                                        daySessions.every(s => s.status === 'completed');
            
            // Check if all previous days' sessions are completed
            let allPreviousDaysCompleted = true;
            for (let day = 1; day < currentSessionModalDay; day++) {
                const prevDaySessions = sessionsData[day] || [];
                if (prevDaySessions.length > 0) {
                    const dayCompleted = prevDaySessions.every(s => s.status === 'completed' || s.status === 'active');
                    if (!dayCompleted) {
                        allPreviousDaysCompleted = false;
                        break;
                    }
                }
            }
            
            // Handle end button - show only if there's an active session
            if (daySession) {
                startBtn.style.display = 'none';
                endBtn.style.display = 'flex';
                endBtn.disabled = false;
                endBtn.style.opacity = '1';
                endBtn.style.cursor = 'pointer';
            } else {
                endBtn.style.display = 'none';
                
                // Handle start button - disable if day is complete or previous days not complete
                if (allSessionsCompleted || !allPreviousDaysCompleted || daySessions.length === 0) {
                    startBtn.disabled = true;
                    startBtn.style.opacity = '0.5';
                    startBtn.style.cursor = 'not-allowed';
                } else {
                    startBtn.disabled = false;
                    startBtn.style.opacity = '1';
                    startBtn.style.cursor = 'pointer';
                }
                startBtn.style.display = 'flex';
            }
        }

        // Add event listeners to day filter buttons
        document.querySelectorAll('.session-modal-day-toggle').forEach(btn => {
            btn.addEventListener('click', function() {
                const dayNum = parseInt(this.getAttribute('data-day'));
                // Always allow clicking to view sessions, even for completed/future days
                updateSessionModalDayFilter(dayNum);
            });
        });

        // Initial state - filter to day 1 and update button state
        updateSessionModalDayFilter(1);

        // Initialize toggle button visibility - show attendee toggle by default (attendee list tab is active)
        const initialAttendeeToggleBtn = document.getElementById('toggle-attendee-filters-btn');
        if (initialAttendeeToggleBtn) {
            initialAttendeeToggleBtn.classList.remove('hidden');
        }

        // Accordion toggle functionality
        document.querySelectorAll('.accordion-toggle').forEach(button => {
            button.addEventListener('click', function() {
                const content = this.nextElementSibling;
                const icon = this.querySelector('[data-lucide="chevron-down"]');
                
                content.classList.toggle('hidden');
                if (icon) {
                    icon.style.transform = content.classList.contains('hidden') ? 'rotate(0deg)' : 'rotate(180deg)';
                }
                
                // Re-initialize lucide icons
                if (typeof lucide !== 'undefined') {
                    lucide.createIcons();
                }
            });
        });
    });

    function toggleAccordion(button) {
        const content = button.nextElementSibling;
        const icon = button.querySelector('[data-lucide="chevron-down"]');
        
        content.classList.toggle('hidden');
        if (icon) {
            icon.style.transform = content.classList.contains('hidden') ? 'rotate(0deg)' : 'rotate(180deg)';
        }
    }

    /**
     * Update Footer Session Button based on active session status
     * Shows "Start Event Session" with primary (blue) color when no session is active
     * Shows "End Event Session" with error (red) color when a session is active
     */
    function updateFooterSessionButton() {
        const footerBtn = document.getElementById('footerEventSessionBtn');
        if (!footerBtn) return;
        
        // Check if there's an active session
        const hasActiveSession = sessionsData && Object.values(sessionsData).some(daySessions => 
            Array.isArray(daySessions) && daySessions.some(session => session.status === 'active')
        );
        
        // Check if all sessions are completed
        const allSessionsCompleted = sessionsData && Object.values(sessionsData).every(daySessions =>
            Array.isArray(daySessions) && daySessions.length > 0 && daySessions.every(session => session.status === 'completed')
        );
        
        if (allSessionsCompleted && Object.keys(sessionsData).length > 0) {
            // Show "Event Finished" with disabled state
            footerBtn.textContent = 'Event Finished';
            footerBtn.disabled = true;
            footerBtn.classList.remove('bg-primary', 'shadow-primary/10', 'text-white', 'bg-error', 'shadow-error/10', 'text-on-error', 'hover:brightness-95', 'cursor-pointer');
            footerBtn.classList.add('bg-gray-400', 'text-gray-700', 'opacity-50', 'cursor-not-allowed');
            footerBtn.removeEventListener('click', handleFooterSessionBtnClick);
        } else if (hasActiveSession) {
            // Show "End Event Session" with error (red) color
            footerBtn.textContent = 'End Event Session';
            footerBtn.disabled = false;
            footerBtn.classList.remove('bg-primary', 'shadow-primary/10', 'text-white', 'bg-gray-400', 'text-gray-700', 'opacity-50', 'cursor-not-allowed');
            footerBtn.classList.add('bg-error', 'shadow-error/10', 'text-on-error', 'hover:brightness-95', 'cursor-pointer');
            footerBtn.addEventListener('click', handleFooterSessionBtnClick);
        } else {
            // Show "Start Event Session" with primary (blue) color
            footerBtn.textContent = 'Start Event Session';
            footerBtn.disabled = false;
            footerBtn.classList.remove('bg-error', 'shadow-error/10', 'text-on-error', 'bg-gray-400', 'text-gray-700', 'opacity-50', 'cursor-not-allowed');
            footerBtn.classList.add('bg-primary', 'shadow-primary/10', 'text-white', 'hover:brightness-95', 'cursor-pointer');
            footerBtn.addEventListener('click', handleFooterSessionBtnClick);
        }
        
        // Update the "Live Now" status indicator
        updateLiveNowStatus();
    }

    /**
     * Handle footer session button click
     */
    function handleFooterSessionBtnClick() {
        const footerBtn = document.getElementById('footerEventSessionBtn');
        if (!footerBtn) return;
        
        if (footerBtn.textContent.includes('End')) {
            // End current session
            endCurrentSession();
        } else {
            // Start next session
            startNextSession();
        }
    }

    /**
     * Update the "Live Now" status indicator based on session completion
     */
    function updateLiveNowStatus() {
        const liveNowBadge = document.getElementById('liveNowBadge');
        if (!liveNowBadge) return;
        
        // Check if all sessions are completed
        const allSessionsCompleted = sessionsData && Object.values(sessionsData).every(daySessions =>
            Array.isArray(daySessions) && daySessions.length > 0 && daySessions.every(session => session.status === 'completed')
        );
        
        if (allSessionsCompleted && Object.keys(sessionsData).length > 0) {
            // Update badge to show "Finished"
            liveNowBadge.textContent = 'COMPLETED';
            liveNowBadge.classList.remove('bg-secondary-fixed', 'text-on-secondary-fixed');
            liveNowBadge.classList.add('bg-[#fcd400]', 'text-white');
            
            // Remove the animated pulse dot
            const pulseSpan = liveNowBadge.querySelector('span');
            if (pulseSpan) {
                pulseSpan.classList.add('hidden');
            }
        }
    }

    let currentDay = 1; // Track current selected day
    let currentSessionDay = 1; // Track current selected session day

    function switchDay(dayNum) {
        currentDay = parseInt(dayNum);
        
        // Update button styles
        document.querySelectorAll('.day-toggle-btn').forEach(btn => {
            if (btn.getAttribute('data-day') === String(dayNum)) {
                btn.classList.remove('bg-surface-container', 'text-on-surface-variant', 'hover:bg-surface-container-high');
                btn.classList.add('bg-error', 'text-on-error');
            } else {
                btn.classList.remove('bg-error', 'text-on-error');
                btn.classList.add('bg-surface-container', 'text-on-surface-variant', 'hover:bg-surface-container-high');
            }
        });
        
        // Update session dropdown for the selected day
        updateSessionDropdown(dayNum);
        
        // Filter rows for current day
        filterDailyAttendance();
    }

    function updateSessionDropdown(dayNum, preservePage = true) {
        const sessionFilter = document.getElementById('daily-session-filter');
        if (!sessionFilter) return;

        // Clear current options
        sessionFilter.innerHTML = '';

        // Get sessions for this day
        const daySessions = sessionsData[dayNum] || [];

        // Separate active and non-active sessions
        const activeSessions = daySessions.filter(s => s.status === 'active');
        const otherSessions = daySessions.filter(s => s.status !== 'active');

        // Add active sessions first
        activeSessions.forEach(session => {
            const option = document.createElement('option');
            option.value = session.id;
            option.textContent = `Session ${session.number} (Active)`;
            sessionFilter.appendChild(option);
        });

        // Add non-active sessions
        otherSessions.forEach(session => {
            const option = document.createElement('option');
            option.value = session.id;
            option.textContent = `Session ${session.number}`;
            
            // Add status labels
            if (session.status === 'upcoming') {
                option.disabled = true;
                option.textContent += ' (Not Started)';
            } else if (session.status === 'completed') {
                option.textContent += ' (Completed)';
            }
            
            sessionFilter.appendChild(option);
        });

        // If there are no sessions for this day, show a message
        if (daySessions.length === 0) {
            const option = document.createElement('option');
            option.value = '';
            option.textContent = 'No sessions available';
            option.disabled = true;
            sessionFilter.appendChild(option);
        } else if (activeSessions.length > 0) {
            // Auto-select the first active session
            sessionFilter.value = activeSessions[0].id;
        } else if (otherSessions.length > 0) {
            // If no active sessions, select the first available non-upcoming session
            const availableSessions = otherSessions.filter(s => s.status !== 'upcoming');
            if (availableSessions.length > 0) {
                sessionFilter.value = availableSessions[0].id;
            }
        }

        // Trigger filter after dropdown is updated
        if (preservePage) {
            filterDailyAttendance();
        } else {
            filterDailyAttendance(false);
        }
    }

    // Load and sync daily attendance data from API
    function loadDailyAttendanceData(eventId) {
        fetch(`/event/${eventId}/attendance-data`)
            .then(response => response.json())
            .then(data => {
                if (!data.success) return;

                attendanceData = data.attendanceData || [];
                const tableBody = document.getElementById('daily-table-body');
                if (!tableBody) return;

                if (Array.isArray(data.sessions)) {
                    sessionsData = {};
                    data.sessions.forEach(session => {
                        const day = session.day_number;
                        if (!sessionsData[day]) {
                            sessionsData[day] = [];
                        }
                        sessionsData[day].push({
                            id: session.id,
                            number: session.session_number,
                            day: session.day_number,
                            status: session.status,
                        });
                    });
                }

                tableBody.innerHTML = '';
                attendanceData.forEach(record => {
                    tableBody.appendChild(createDailyAttendanceRow(record));
                });

                updateSessionDropdown(currentDay, false);
                updateFooterSessionButton();
            })
            .catch(err => console.error('Error loading daily attendance data:', err));
    }

    function switchSessionDay(dayNum) {
        currentSessionDay = parseInt(dayNum);
        
        // Update button styles
        document.querySelectorAll('.session-day-toggle-btn').forEach(btn => {
            if (btn.getAttribute('data-day') === String(dayNum)) {
                btn.classList.remove('bg-surface-container', 'text-on-surface-variant', 'hover:bg-surface-container-high');
                btn.classList.add('bg-primary', 'text-on-primary');
            } else {
                btn.classList.remove('bg-primary', 'text-on-primary');
                btn.classList.add('bg-surface-container', 'text-on-surface-variant', 'hover:bg-surface-container-high');
            }
        });
        
        // Show/hide the session day block for current day
        document.querySelectorAll('.session-day-block').forEach(block => {
            if (block.getAttribute('data-day') === String(dayNum)) {
                block.classList.remove('hidden');
                block.style.display = 'block';
            } else {
                block.classList.add('hidden');
                block.style.display = 'none';
            }
        });
    }

    function filterForAttendeeList() {
        const searchQuery = document.querySelector('#attendee-list-tab input[type="text"]')?.value.toLowerCase() || '';
        
        const table = document.querySelector('#attendee-list-tab table tbody');
        if (!table) return;
        
        const rows = table.querySelectorAll('tr');
        let visibleCount = 0;
        
        rows.forEach(row => {
            // Get row data
            const nameCell = row.querySelector('td:nth-child(1)');
            const studentIdCell = row.querySelector('td:nth-child(2)');
            const sectionCell = row.querySelector('td:nth-child(3)');
            const programCell = row.querySelector('td:nth-child(4)');
            const rfidCell = row.querySelector('td:nth-child(5)');
            
            const name = nameCell?.textContent.toLowerCase() || '';
            const studentId = studentIdCell?.textContent.toLowerCase() || '';
            const section = sectionCell?.textContent.toLowerCase() || '';
            const program = programCell?.textContent.toLowerCase() || '';
            const rfid = rfidCell?.textContent.toLowerCase() || '';
            
            // Check if row matches search query
            const matchesSearch = name.includes(searchQuery) || studentId.includes(searchQuery) || section.includes(searchQuery) || program.includes(searchQuery) || rfid.includes(searchQuery);
            
            // Show/hide row
            if (matchesSearch) {
                row.classList.remove('hidden');
                visibleCount++;
            } else {
                row.classList.add('hidden');
            }
        });
        
        // Update pagination count
        const paginationInfo = document.getElementById('attendee-pagination-info');
        if (paginationInfo) {
            paginationInfo.innerHTML = `Showing 1 to <strong>${visibleCount}</strong> of <strong>${rows.length}</strong> results`;
        }
    }

    function attachAttendeePaginationListeners() {
        const prevButton = document.getElementById('attendee-page-prev');
        const nextButton = document.getElementById('attendee-page-next');

        if (prevButton) {
            prevButton.addEventListener('click', function(e) {
                e.preventDefault();
                const currentPage = parseInt(this.getAttribute('data-current-page') || '1', 10);
                if (currentPage > 1) {
                    fetchAttendeePage(currentPage - 1);
                }
            });
        }

        if (nextButton) {
            nextButton.addEventListener('click', function(e) {
                e.preventDefault();
                const currentPage = parseInt(this.getAttribute('data-current-page') || '1', 10);
                const lastPage = parseInt(this.getAttribute('data-last-page') || '1', 10);
                if (currentPage < lastPage) {
                    fetchAttendeePage(currentPage + 1);
                }
            });
        }
    }

    function attachViewBarcodeListeners() {
        document.querySelectorAll('.viewBarcodeBtn').forEach(button => {
            const newButton = button.cloneNode(true);
            button.parentNode.replaceChild(newButton, button);

            newButton.addEventListener('click', function(e) {
                e.preventDefault();
                const studentId = this.getAttribute('data-student-id');
                const studentName = this.getAttribute('data-student-name');
                const studentRfid = this.getAttribute('data-student-rfid');
                const studentSection = this.getAttribute('data-student-section');
                showBarcodeModal(studentId, studentName, studentRfid, studentSection);
            });
        });
    }

    async function fetchAttendeePage(page) {
        const pageInfo = document.getElementById('attendee-page-indicator');
        const paginationInfo = document.getElementById('attendee-pagination-info');
        const prevButton = document.getElementById('attendee-page-prev');
        const nextButton = document.getElementById('attendee-page-next');
        const tableBody = document.getElementById('attendee-table-body');

        if (!tableBody) return;

        const response = await fetch(`/event/${eventId}/attendee-page?page=${page}`);
        if (!response.ok) {
            console.error('Failed to load attendee page', response.statusText);
            return;
        }

        const data = await response.json();
        if (!data.success) {
            console.error('Attendee page fetch error', data);
            return;
        }

        tableBody.innerHTML = data.rowsHtml;

        if (paginationInfo) {
            paginationInfo.innerHTML = `Showing <strong>${data.from}</strong> to <strong>${data.to}</strong> of <strong>${data.total}</strong> results`;
        }

        if (pageInfo) {
            pageInfo.textContent = `Page ${data.currentPage} of ${data.lastPage}`;
        }

        if (prevButton) {
            prevButton.dataset.currentPage = data.currentPage;
            prevButton.dataset.lastPage = data.lastPage;
            prevButton.classList.toggle('pointer-events-none', data.onFirstPage);
            prevButton.classList.toggle('opacity-50', data.onFirstPage);
        }

        if (nextButton) {
            nextButton.dataset.currentPage = data.currentPage;
            nextButton.dataset.lastPage = data.lastPage;
            nextButton.classList.toggle('pointer-events-none', !data.hasMorePages);
            nextButton.classList.toggle('opacity-50', !data.hasMorePages);
        }

        attachDeleteListeners();
        attachViewBarcodeListeners();
        filterAttendeeList();
    }

    function filterAttendeeList() {
        filterForAttendeeList();
    }

    async function addAttendeeToEvent() {
        const studentName = document.getElementById('attendee_student_name').value.trim();
        const studentId = document.getElementById('attendee_student_snumber').value.trim();
        const section = document.getElementById('attendee_section').value.trim();
        const program = document.getElementById('attendee_program').value.trim();
        const rfid = document.getElementById('attendee_rfid').value.trim();
        const btn = document.getElementById('addAttendeeBtn');
        
        // Validation
        if (!studentName) {
            showToast('Full Name is required', 'error');
            document.getElementById('attendee_student_name').focus();
            return;
        }
        if (!studentId) {
            showToast('Student ID is required', 'error');
            document.getElementById('attendee_student_snumber').focus();
            return;
        }
        if (!section) {
            showToast('Section is required', 'error');
            document.getElementById('attendee_section').focus();
            return;
        }
        
        // Disable button
        btn.disabled = true;
        btn.innerHTML = '<svg class="w-5 h-5 animate-spin mx-auto"></svg>';
        
        try {
            const response = await fetch(`/event/${eventId}/add-student`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({
                    student_name: studentName,
                    student_number: studentId,
                    section: section,
                    program: program || null,
                    rfid: rfid || null
                })
            });
            
            const data = await response.json();
            
            if (response.ok) {
                // Show success message
                showToast('Student added successfully!', 'success');
                
                // Clear form
                document.getElementById('attendee_student_name').value = '';
                document.getElementById('attendee_student_snumber').value = '';
                document.getElementById('attendee_section').value = '';
                document.getElementById('attendee_program').value = '';
                document.getElementById('attendee_rfid').value = '';
                document.getElementById('attendee_student_name').focus();
                
                // Refresh the attendee list table
                refreshAttendeeListTable();
            } else {
                showToast(data.message || 'Failed to add student', 'error');
            }
        } catch (error) {
            console.error('Error:', error);
            showToast('An error occurred while adding the student', 'error');
        } finally {
            // Re-enable button
            btn.disabled = false;
            btn.innerHTML = '<svg class="w-5 h-5"></svg>Add Student';
        }
    }

    function refreshAttendeeListTable() {
        // Reload the entire page after a delay to allow toast to display
        setTimeout(() => {
            location.reload();
        }, 1500);
    }

    function filterDailyAttendance(resetPage = true) {
        if (resetPage) {
            dailyCurrentPage = 1;
        }

        const searchQuery = document.getElementById('daily-search')?.value.toLowerCase() || '';
        const statusFilter = document.getElementById('daily-status-filter')?.value.toLowerCase() || '';
        const sessionFilter = document.getElementById('daily-session-filter')?.value || '';
        
        // Check if selected session has started
        if (sessionFilter) {
            const currentDaySessions = sessionsData[currentDay] || [];
            const selectedSession = currentDaySessions.find(s => String(s.id) === String(sessionFilter));
            
            const noSessionMessage = document.getElementById('no-session-started-message');
            if (selectedSession && selectedSession.status === 'upcoming') {
                if (noSessionMessage) {
                    noSessionMessage.classList.remove('hidden');
                }
                // Hide all rows and hide pagination
                document.querySelectorAll('.daily-attendance-row').forEach(row => row.classList.add('hidden'));
                updateDailyPaginationControls(0, 0, 0);
                return;
            } else if (noSessionMessage) {
                noSessionMessage.classList.add('hidden');
            }
        }
        
        const rows = Array.from(document.querySelectorAll('.daily-attendance-row'));
        const matchingRows = [];
        
        rows.forEach(row => {
            const rowDay = row.getAttribute('data-day');
            const rowSessionId = row.getAttribute('data-session-id');
            const name = row.getAttribute('data-name') || '';
            const studentId = row.getAttribute('data-student-id') || '';
            const status = (row.getAttribute('data-status') || '').trim().toLowerCase();
            
            const sectionCell = row.querySelector('td:nth-child(3)');
            const section = sectionCell?.textContent.toLowerCase() || '';
            const programCell = row.querySelector('td:nth-child(4)');
            const program = programCell?.textContent.toLowerCase() || '';
            
            const isCurrentDay = String(rowDay) === String(currentDay);
            const matchesSearch = name.includes(searchQuery) || studentId.includes(searchQuery) || section.includes(searchQuery) || program.includes(searchQuery);
            const matchesStatus = !statusFilter || status === statusFilter;
            // Ensure session filter is properly applied
            const matchesSession = !sessionFilter || String(rowSessionId) === String(sessionFilter);
            
            if (isCurrentDay && matchesSearch && matchesStatus && matchesSession) {
                matchingRows.push(row);
            } else {
                row.classList.add('hidden');
            }
        });

        const totalMatching = matchingRows.length;
        const totalPages = Math.max(1, Math.ceil(totalMatching / dailyRowsPerPage));
        if (dailyCurrentPage > totalPages) {
            dailyCurrentPage = totalPages;
        }

        const startIndex = (dailyCurrentPage - 1) * dailyRowsPerPage;
        const endIndex = startIndex + dailyRowsPerPage;

        matchingRows.forEach((row, index) => {
            if (index >= startIndex && index < endIndex) {
                row.classList.remove('hidden');
            } else {
                row.classList.add('hidden');
            }
        });

        updateDailyPaginationControls(totalMatching, totalMatching === 0 ? 0 : startIndex + 1, Math.min(totalMatching, endIndex));
    }

    function showBarcodeModal(studentId, studentName, studentRfid, studentSection) {
        // Remove existing modal if any
        const existingModal = document.getElementById('barcodeModalAttendance');
        if (existingModal) {
            existingModal.remove();
        }

        // Create barcode data
        const barcodeData = `${studentId}`;

        // Create modal
        const modal = document.createElement('div');
        modal.id = 'barcodeModalAttendance';
        modal.className = 'fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50';
        modal.innerHTML = `
            <div class="bg-surface rounded-xl shadow-lg p-4 sm:p-8 max-w-xs sm:max-w-md w-full mx-2 sm:mx-4 border border-outline">
                <h2 class="text-lg font-bold text-on-surface mb-4">Student Barcode / QR Code</h2>
                
                <!-- Toggle Buttons -->
                <div class="flex gap-2 mb-6">
                    <button type="button" id="barcodeToggleBtn" class="flex-1 flex items-center justify-center gap-2 py-2 px-3 bg-primary text-on-primary rounded-lg font-bold text-sm transition-colors toggleCodeTypeBtn">
                        <span class="material-symbols-outlined text-lg">barcode</span>
                        Barcode
                    </button>
                    <button type="button" id="qrcodeToggleBtn" class="flex-1 flex items-center justify-center gap-2 py-2 px-3 bg-surface-container text-on-surface-variant rounded-lg font-bold text-sm transition-colors toggleCodeTypeBtn hover:bg-surface-container-high">
                        <span class="material-symbols-outlined text-lg">qr_code</span>
                        QR Code
                    </button>
                </div>
                
                <div class="bg-surface-container-low p-6 rounded-lg mb-6 flex flex-col items-center border border-outline">
                    <p class="text-sm font-semibold text-on-surface mb-4">${escapeHtml(studentName)}</p>
                    <div id="attendanceBarcodeContainer" class="barcodeContainer">
                        <svg id="attendanceBarcodeImg"></svg>
                    </div>
                    <div id="attendanceQrcodeContainer" class="qrcodeContainer hidden" style="width: 200px; height: 200px;"></div>
                </div>
                
                <div class="space-y-2 mb-6 text-sm">
                    <p><span class="font-semibold text-on-surface">Student ID:</span> <span class="text-on-surface-variant">${escapeHtml(studentId)}</span></p>
                    <p><span class="font-semibold text-on-surface">Section:</span> <span class="text-on-surface-variant">${escapeHtml(studentSection)}</span></p>
                </div>
                
                <div class="flex gap-3">
                    <button type="button" id="downloadCodeBtn" class="flex-1 py-2 bg-primary hover:bg-primary-fixed text-on-primary rounded-lg font-bold text-sm transition-colors" onclick="downloadCode('${escapeHtml(studentId)}')">Download</button>
                    <button type="button" class="flex-1 py-2 bg-surface-container hover:bg-surface-container-high text-on-surface rounded-lg font-bold text-sm transition-colors" onclick="document.getElementById('barcodeModalAttendance').remove()">Close</button>
                </div>
            </div>
        `;

        document.body.appendChild(modal);

        // Generate barcode
        JsBarcode("#attendanceBarcodeImg", barcodeData, {
            format: "CODE128",
            width: 2,
            height: 60,
            displayValue: true
        });

        // Generate QR Code
        const qrcodeContainer = document.getElementById('attendanceQrcodeContainer');
        qrcodeContainer.innerHTML = ''; // Clear previous QR code
        new QRCode(qrcodeContainer, {
            text: barcodeData,
            width: 200,
            height: 200,
            colorDark: "#000000",
            colorLight: "#ffffff",
            correctLevel: QRCode.CorrectLevel.H
        });

        // Store current code type
        let currentCodeType = 'barcode';

        // Toggle button functionality
        const barcodeToggleBtn = document.getElementById('barcodeToggleBtn');
        const qrcodeToggleBtn = document.getElementById('qrcodeToggleBtn');
        const barcodeContainer = document.getElementById('attendanceBarcodeContainer');
        const qrcodeContainerDiv = document.getElementById('attendanceQrcodeContainer');

        barcodeToggleBtn.addEventListener('click', function(e) {
            e.preventDefault();
            currentCodeType = 'barcode';
            barcodeContainer.classList.remove('hidden');
            qrcodeContainerDiv.classList.add('hidden');
            
            barcodeToggleBtn.classList.remove('bg-surface-container', 'text-on-surface-variant', 'hover:bg-surface-container-high');
            barcodeToggleBtn.classList.add('bg-primary', 'text-on-primary');
            
            qrcodeToggleBtn.classList.remove('bg-primary', 'text-on-primary');
            qrcodeToggleBtn.classList.add('bg-surface-container', 'text-on-surface-variant', 'hover:bg-surface-container-high');
        });

        qrcodeToggleBtn.addEventListener('click', function(e) {
            e.preventDefault();
            currentCodeType = 'qrcode';
            barcodeContainer.classList.add('hidden');
            qrcodeContainerDiv.classList.remove('hidden');
            
            qrcodeToggleBtn.classList.remove('bg-surface-container', 'text-on-surface-variant', 'hover:bg-surface-container-high');
            qrcodeToggleBtn.classList.add('bg-primary', 'text-on-primary');
            
            barcodeToggleBtn.classList.remove('bg-primary', 'text-on-primary');
            barcodeToggleBtn.classList.add('bg-surface-container', 'text-on-surface-variant', 'hover:bg-surface-container-high');
        });

        // Store code type in modal for download
        modal.dataset.codeType = 'barcode';
        modal.dataset.studentId = studentId;

        // Update download button text based on code type
        barcodeToggleBtn.addEventListener('click', () => {
            modal.dataset.codeType = 'barcode';
        });
        qrcodeToggleBtn.addEventListener('click', () => {
            modal.dataset.codeType = 'qrcode';
        });

        // Re-initialize lucide icons
        if (typeof lucide !== 'undefined') {
            lucide.createIcons();
        }

        // Close modal on outside click
        modal.addEventListener('click', function(e) {
            if (e.target === modal) {
                modal.remove();
            }
        });
    }

    function downloadCsvTemplate() {
        // Create CSV template with headers
        const csvContent = `Full Name,Student ID,Section,Program,RFID
John Doe,2024001,Section A,BSCS,
Jane Smith,2024002,Section A,BSCS,
Michael Johnson,2024003,Section B,BSIT,
Sarah Williams,2024004,Section B,BSIT,`;

        // Create blob and download
        const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
        const link = document.createElement('a');
        const url = URL.createObjectURL(blob);
        
        link.setAttribute('href', url);
        link.setAttribute('download', 'attendee-template.csv');
        link.style.visibility = 'hidden';
        
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        
        showToast('Template downloaded successfully!', 'success');
    }

    function downloadCode(studentId) {
        const modal = document.getElementById('barcodeModalAttendance');
        const codeType = modal?.dataset?.codeType || 'barcode';
        
        if (codeType === 'barcode') {
            downloadBarcode(studentId);
        } else {
            downloadQRCode(studentId);
        }
    }

    function downloadBarcode(studentId) {
        const svg = document.querySelector('#attendanceBarcodeImg');
        if (!svg) return;

        // Convert SVG to canvas
        const canvas = document.createElement('canvas');
        const ctx = canvas.getContext('2d');
        const svgXml = new XMLSerializer().serializeToString(svg);
        const img = new Image();
        
        img.onload = function() {
            canvas.width = img.width + 20;
            canvas.height = img.height + 20;
            ctx.fillStyle = 'white';
            ctx.fillRect(0, 0, canvas.width, canvas.height);
            ctx.drawImage(img, 10, 10);
            
            // Download as PNG
            const link = document.createElement('a');
            link.href = canvas.toDataURL('image/png');
            link.download = `barcode_${studentId}.png`;
            link.click();
        };
        
        img.src = 'data:image/svg+xml;base64,' + btoa(svgXml);
    }

    function downloadQRCode(studentId) {
        const qrcodeCanvas = document.querySelector('#attendanceQrcodeContainer canvas');
        if (!qrcodeCanvas) return;
        
        // Convert canvas to PNG
        const link = document.createElement('a');
        link.href = qrcodeCanvas.toDataURL('image/png');
        link.download = `qrcode_${studentId}.png`;
        link.click();
    }

    function openMarkAttendanceModal() {
        // Create modal
        const modal = document.createElement('div');
        modal.id = 'markAttendanceModal';
        modal.className = 'fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50';
        modal.innerHTML = `
            <div class="bg-surface rounded-xl shadow-lg p-4 sm:p-8 max-w-xs sm:max-w-md w-full mx-2 sm:mx-4 max-h-[90vh] overflow-y-auto border border-outline">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-lg font-bold text-on-surface flex items-center gap-2">
                        <span class="material-symbols-outlined text-primary text-xl">edit</span>
                        Mark Attendance
                    </h2>
                    <button type="button" class="text-on-surface-variant hover:text-on-surface transition-colors closeMarkModal">
                        <span class="material-symbols-outlined text-lg">close</span>
                    </button>
                </div>
                
                <!-- Error/Success Messages -->
                <div id="modal-message" class="mb-4 hidden px-4 py-3 rounded-lg text-sm font-medium"></div>

                <form class="space-y-4" id="attendance-form-modal">
                    <div>
                        <label class="block text-sm font-semibold text-on-surface mb-1" for="student_id_modal">Student ID *</label>
                        <input class="w-full px-3 py-2 border border-outline rounded-lg focus:ring-2 focus:ring-primary/40 focus:border-primary transition-all bg-surface text-on-surface placeholder-on-surface-variant" id="student_id_modal" placeholder="e.g. 2024-0012" type="text" autocomplete="off"/>
                        <p class="text-xs text-on-surface-variant mt-1">Or scan RFID tag below</p>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-on-surface mb-1" for="rfid_modal">RFID (Optional)</label>
                        <input class="w-full px-3 py-2 border border-outline rounded-lg focus:ring-2 focus:ring-primary/40 focus:border-primary bg-surface-container-low transition-all text-on-surface placeholder-on-surface-variant" id="rfid_modal" placeholder="Scan Tag..." type="text" autocomplete="off"/>
                    </div>
                    <button class="w-full bg-primary hover:bg-primary-fixed text-on-primary font-bold py-3 px-4 rounded-lg transition-colors flex items-center justify-center gap-2 mt-6 disabled:opacity-50 disabled:cursor-not-allowed" type="submit" id="mark-present-btn">
                        <span class="material-symbols-outlined text-lg">check_circle</span>
                        Mark Present
                    </button>
                </form>
                <div class="mt-8 pt-6 border-t border-outline">
                    <div class="flex justify-between items-center mb-4">
                        <span class="text-sm font-bold text-on-surface-variant uppercase">Live Stats</span>
                        <span class="text-sm font-bold text-primary" id="attendance-percentage">${document.querySelector('[data-width]')?.getAttribute('data-width') || '0'}% Full</span>
                    </div>
                    <div class="w-full bg-surface-container rounded-full h-2.5">
                        <div class="bg-primary h-2.5 rounded-full" id="attendance-progress-bar" style="width: ${document.querySelector('[data-width]')?.getAttribute('data-width') || '0'}%"></div>
                    </div>
                    <div class="mt-4 flex justify-between">
                        <div class="text-center">
                            <p class="text-2xl font-bold text-on-surface" id="stats-present">${initialStats.checkedIn}</p>
                            <p class="text-[10px] text-on-surface-variant font-bold uppercase">Present</p>
                        </div>
                        <div class="text-center">
                            <p class="text-2xl font-bold text-on-surface" id="stats-absent">${initialStats.absent}</p>
                            <p class="text-[10px] text-on-surface-variant font-bold uppercase">Absent</p>
                        </div>
                        <div class="text-center">
                            <p class="text-2xl font-bold text-on-surface" id="stats-total">${initialStats.totalStudents}</p>
                            <p class="text-[10px] text-on-surface-variant font-bold uppercase">Total</p>
                        </div>
                    </div>
                </div>
            </div>
        `;

        document.body.appendChild(modal);

        // Re-initialize lucide icons
        if (typeof lucide !== 'undefined') {
            lucide.createIcons();
        }

        // Get elements
        const form = document.getElementById('attendance-form-modal');
        const studentIdInput = document.getElementById('student_id_modal');
        const rfidInput = document.getElementById('rfid_modal');
        const submitBtn = document.getElementById('mark-present-btn');
        const messageEl = document.getElementById('modal-message');
        const closeBtn = modal.querySelector('.closeMarkModal');

        // Close modal on close button
        closeBtn.addEventListener('click', function() {
            modal.remove();
        });

        // Close modal on outside click
        modal.addEventListener('click', function(e) {
            if (e.target === modal) {
                modal.remove();
            }
        });

        // Show message
        function showMessage(message, type = 'error') {
            messageEl.textContent = message;
            messageEl.className = `mb-4 px-4 py-3 rounded-lg text-sm font-medium ${
                type === 'success' 
                    ? 'bg-secondary-fixed text-on-secondary-fixed border border-secondary-fixed' 
                    : 'bg-error text-on-error border border-error'
            }`;
            messageEl.classList.remove('hidden');
        }

        // Clear message
        function clearMessage() {
            messageEl.classList.add('hidden');
            messageEl.textContent = '';
        }

        // Handle form submission
        form.addEventListener('submit', async function(e) {
            e.preventDefault();
            clearMessage();

            const studentId = studentIdInput.value.trim();
            const rfid = rfidInput.value.trim();

            // Validation
            if (!studentId && !rfid) {
                showMessage('Please enter Student ID or scan RFID tag', 'error');
                studentIdInput.focus();
                return;
            }

            // Disable submit button
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<svg class="w-4 h-4 inline-block animate-spin" fill="none" stroke="currentColor" viewbox="0 0 24 24" style="display: inline; margin-right: 0.5rem;"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2" fill="none"></circle><path d="M12 2a10 10 0 010 20" stroke="currentColor" stroke-width="2" fill="none"></path></svg> Processing...';
            if (typeof lucide !== 'undefined') {
                lucide.createIcons();
            }

            try {
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                
                const response = await fetch(`/event/${eventId}/mark-attendance-present`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken || ''
                    },
                    body: JSON.stringify({
                        student_id: studentId || '',
                        rfid: rfid || ''
                    })
                });

                // Check if response is ok first
                if (!response.ok) {
                    const errorData = await response.json().catch(() => ({message: 'An error occurred'}));
                    showMessage(errorData.message || `Error: ${response.status}`, 'error');
                    console.error('Response error:', { status: response.status, data: errorData });
                    return;
                }

                const data = await response.json();

                if (data.success) {
                    showMessage(data.message, 'success');
                    
                    // Update Daily Attendance table
                    if (data.student && data.attendance) {
                        try {
                            updateDailyAttendanceRow(data.student.snumber, data.attendance.time_in);
                            // Also update Session Attendance tables
                            updateSessionAttendanceTable(data.student.snumber, data.attendance.time_in, data.attendance.session_id);
                        } catch (updateError) {
                            console.error('Failed to update table:', updateError);
                            showMessage('Marked present successfully, but table update had an issue (reload page to see changes)', 'error');
                        }
                    }
                    
                    // Clear form inputs
                    studentIdInput.value = '';
                    rfidInput.value = '';
                    
                    // Focus back to student ID input for quick marking
                    setTimeout(() => {
                        studentIdInput.focus();
                        clearMessage();
                    }, 2000);

                    // Update stats if available
                    if (data.attendance) {
                        try {
                            const presentCount = parseInt(document.getElementById('stats-present').textContent) || 0;
                            const totalCount = parseInt(document.getElementById('stats-total').textContent) || 1;
                            const newPresentCount = presentCount + 1;
                            const newPercentage = Math.round((newPresentCount / totalCount) * 100);
                            
                            document.getElementById('stats-present').textContent = newPresentCount;
                            document.getElementById('stats-absent').textContent = totalCount - newPresentCount;
                            document.getElementById('stats-percentage').textContent = newPercentage + '% Full';
                            document.getElementById('attendance-progress-bar').style.width = newPercentage + '%';
                        } catch (statsError) {
                            console.warn('Failed to update stats:', statsError);
                        }
                    }
                } else {
                    showMessage(data.message || 'An error occurred', 'error');
                    console.error('API error:', data);
                }
            } catch (error) {
                console.error('Network/Parse error:', error);
                showMessage('Network error. Please check your connection and try again.', 'error');
            } finally {
                // Re-enable submit button
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<svg class="w-4 h-4" fill="currentColor" viewbox="0 0 24 24" style="display: inline; margin-right: 0.5rem;"><path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41L9 16.17z"></path></svg> Mark Present';
                if (typeof lucide !== 'undefined') {
                    lucide.createIcons();
                }
            }
        });

        // Auto-focus on student ID input
        studentIdInput.focus();
    }

    /**
     * Update the Daily Attendance table row in real-time after marking present
     */
    function updateDailyAttendanceRow(studentNumber, timeIn) {
        try {
            // Parse the time_in to format it as h:i A
            const timeInDate = new Date(timeIn);
            const displayHours = timeInDate.getHours() % 12 || 12;
            const minutes = String(timeInDate.getMinutes()).padStart(2, '0');
            const ampm = timeInDate.getHours() >= 12 ? 'PM' : 'AM';
            const formattedTime = `${displayHours}:${minutes} ${ampm}`;

            // Find the row for this student in Daily Attendance table
            const tableBody = document.getElementById('daily-table-body');
            if (!tableBody) {
                console.warn('Daily table body not found');
                return;
            }

            const tableRows = tableBody.querySelectorAll('tr.daily-attendance-row');
            
            let found = false;
            tableRows.forEach(row => {
                // Check if this row is for the marked student
                if (row.getAttribute('data-student-id') === studentNumber) {
                    found = true;
                    try {
                        // Find the Time In cell (5th column - index 4)
                        const cells = row.querySelectorAll('td');
                        
                        if (cells.length >= 7) {
                            // Update Time In cell (column 4 - 0 indexed)
                            const timeInCell = cells[4];
                            if (timeInCell) {
                                timeInCell.textContent = formattedTime;
                                timeInCell.classList.add('font-semibold', 'text-on-surface');
                                timeInCell.classList.remove('text-on-surface-variant');
                            }
                            
                            // Update Status cell (column 6 - 0 indexed)
                            const statusCell = cells[6];
                            if (statusCell) {
                                statusCell.innerHTML = '<span class="inline-block px-3 py-1 bg-secondary-fixed text-on-secondary-fixed text-xs font-bold rounded-full">Present</span>';
                            }
                            
                            // Change row background to green
                            row.classList.remove('hover:bg-surface-container-low', 'bg-error/5');
                            row.classList.add('bg-secondary-fixed/5', 'hover:bg-secondary-fixed/10');
                            
                            // Update data-status attribute
                            row.setAttribute('data-status', 'present');
                        } else {
                            console.warn(`Expected at least 7 cells, found ${cells.length}`, cells);
                        }
                    } catch (cellError) {
                        console.error('Error updating cell:', cellError);
                    }
                }
            });

            if (!found) {
                console.warn(`Student ${studentNumber} not found in daily attendance table`);
            }
        } catch (error) {
            console.error('Error updating daily attendance row:', error);
        }
    }

    /**
     * Update the Session Attendance table when a student is marked present
     */
    function updateSessionAttendanceTable(studentNumber, timeIn, sessionId) {
        try {
            // Parse the time_in to format it as h:i A
            const timeInDate = new Date(timeIn);
            const displayHours = timeInDate.getHours() % 12 || 12;
            const minutes = String(timeInDate.getMinutes()).padStart(2, '0');
            const ampm = timeInDate.getHours() >= 12 ? 'PM' : 'AM';
            const formattedTime = `${displayHours}:${minutes} ${ampm}`;

            // Find all session attendance tables
            const sessionDayBlocks = document.querySelectorAll('.session-day-block');
            let updated = false;

            sessionDayBlocks.forEach(dayBlock => {
                // Find all session containers in this day block
                const sessionContainers = dayBlock.querySelectorAll('.session-table-wrapper');
                
                sessionContainers.forEach(container => {
                    // Find the attendance table within this session container
                    const table = container.querySelector('table');
                    if (!table) return; // Skip if no table found

                    // Find the student row in this session table
                    const tbody = table.querySelector('tbody');
                    if (!tbody) return;

                    const rows = tbody.querySelectorAll('tr');
                    rows.forEach(row => {
                        const cells = row.querySelectorAll('td');
                        if (cells.length >= 6) {
                            // Student ID is in the 2nd cell (index 1)
                            const cellText = cells[1].textContent.trim();
                            if (cellText === studentNumber) {
                                try {
                                    // Update Time In cell (index 3)
                                    const timeInCell = cells[3];
                                    if (timeInCell) {
                                        timeInCell.textContent = formattedTime;
                                        timeInCell.classList.add('font-semibold', 'text-on-surface');
                                        timeInCell.classList.remove('text-on-surface-variant');
                                    }

                                    // Update Status cell (index 5)
                                    const statusCell = cells[5];
                                    if (statusCell) {
                                        statusCell.innerHTML = '<span class="inline-block px-3 py-1 bg-secondary-fixed text-on-secondary-fixed text-xs font-bold rounded-full">Present</span>';
                                    }

                                    // Change row background to green
                                    row.classList.remove('hover:bg-surface-container-low', 'bg-error/5');
                                    row.classList.add('bg-secondary-fixed/5', 'hover:bg-secondary-fixed/10 transition-colors');

                                    // Update session statistics (Present/Absent counts)
                                    const sessionHeader = container.querySelector('.px-6.py-4.border-b');
                                    if (sessionHeader) {
                                        const headerCells = sessionHeader.querySelectorAll('span');
                                        // Find the Present and Absent badges
                                        for (let i = 0; i < headerCells.length; i++) {
                                            const cell = headerCells[i];
                                            if (cell.textContent.includes('Present:')) {
                                                const presentMatch = cell.textContent.match(/Present:\s*(\d+)/);
                                                if (presentMatch) {
                                                    const currentPresent = parseInt(presentMatch[1]) || 0;
                                                    cell.textContent = `Present: ${currentPresent + 1}`;
                                                }
                                            } else if (cell.textContent.includes('Absent:')) {
                                                const absentMatch = cell.textContent.match(/Absent:\s*(\d+)/);
                                                if (absentMatch) {
                                                    const currentAbsent = parseInt(absentMatch[1]) || 0;
                                                    if (currentAbsent > 0) {
                                                        cell.textContent = `Absent: ${currentAbsent - 1}`;
                                                    }
                                                }
                                            }
                                        }
                                    }

                                    updated = true;
                                } catch (cellError) {
                                    console.error('Error updating session cell:', cellError);
                                }
                            }
                        }
                    });
                });
            });

            if (!updated) {
                console.warn(`Student ${studentNumber} not found in session attendance tables (may not be visible if session tab is hidden)`);
            }
        } catch (error) {
            console.error('Error updating session attendance table:', error);
        }
    }

    function deleteAttendance(attendanceId) {
        // Send delete request to server
        console.log('Deleting attendance ID:', attendanceId);
        fetch(`/attendance/${attendanceId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                'Content-Type': 'application/json'
            }
        })
        .then(response => {
            console.log('Delete response status:', response.status);
            return response.json().then(data => {
                console.log('Delete response data:', data);
                if (response.ok && data.success) {
                    // Remove the row from the table in DOM
                    const row = document.querySelector(`[data-attendance-id="${attendanceId}"]`)?.closest('tr');
                    if (row) {
                        row.style.animation = 'fadeOut 0.3s ease-in-out';
                        setTimeout(() => {
                            row.remove();
                            showToast('Student removed successfully', 'success');
                        }, 300);
                    } else {
                        showToast('Student removed successfully', 'success');
                    }
                } else {
                    showToast(data.message || 'Failed to delete student', 'error');
                }
            });
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('An error occurred while deleting the student', 'error');
        });
    }

    function attachDeleteListeners() {
        document.querySelectorAll('.deleteAttendanceBtn').forEach(button => {
            // Remove existing listeners by cloning
            const newButton = button.cloneNode(true);
            button.parentNode.replaceChild(newButton, button);
            
            newButton.addEventListener('click', function(e) {
                e.preventDefault();
                const attendanceId = this.getAttribute('data-attendance-id');
                if (confirm('Are you sure you want to delete this student from the attendance list?')) {
                    deleteAttendance(attendanceId);
                }
            });
        });
    }

    function showToast(message, type = 'success') {
        const bgColor = type === 'success' ? 'bg-secondary-fixed' : type === 'error' ? 'bg-error' : 'bg-primary';
        const icon = type === 'success' ? '✓' : type === 'error' ? '✕' : 'ℹ';
        
        const toast = document.createElement('div');
        toast.className = `fixed top-6 right-6 ${bgColor} text-white px-6 py-4 rounded-lg shadow-xl z-50 flex items-center gap-3 animate-fadeIn`;
        toast.innerHTML = `
            <span class="text-xl font-bold">${icon}</span>
            <span class="font-medium">${message}</span>
        `;
        document.body.appendChild(toast);
        
        setTimeout(() => {
            toast.style.animation = 'fadeOut 0.3s ease-in-out forwards';
            setTimeout(() => toast.remove(), 300);
        }, 3000);
    }

    function showConfirmDialog(title, message, onConfirm, onCancel) {
        const overlay = document.createElement('div');
        overlay.className = 'fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50';
        
        const dialog = document.createElement('div');
        dialog.className = 'bg-surface rounded-lg shadow-2xl p-6 max-w-sm mx-4 border border-outline';
        dialog.innerHTML = `
            <h2 class="text-lg font-bold text-on-surface mb-2">${title}</h2>
            <p class="text-on-surface-variant mb-6">${message}</p>
            <div class="flex gap-3 justify-end">
                <button class="px-4 py-2 text-on-surface-variant bg-surface-container rounded-lg hover:bg-surface-container-high transition-colors font-medium" onclick="this.closest('.dialog-overlay').remove();">Cancel</button>
                <button class="px-4 py-2 bg-error text-on-error rounded-lg hover:bg-error/90 transition-colors font-medium" onclick="this.closest('.dialog-overlay').remove(); window.__confirmCallback?.();">Confirm</button>
            </div>
        `;
        
        overlay.className += ' dialog-overlay';
        overlay.appendChild(dialog);
        window.__confirmCallback = onConfirm;
        document.body.appendChild(overlay);
    }

    function refreshSessionTimeline() {
        const eventId = document.getElementById('endCurrentSessionBtn').getAttribute('data-event-id');
        
        // Fetch updated event data
        fetch(`/event/${eventId}`, {
            method: 'GET',
            headers: {
                'Accept': 'application/json'
            }
        })
        .then(response => response.text())
        .then(html => {
            // Parse the new HTML
            const parser = new DOMParser();
            const newDoc = parser.parseFromString(html, 'text/html');
            
            // Find and replace the session timeline section
            const oldTimeline = document.querySelector('[data-purpose="session-timeline"]');
            const newTimeline = newDoc.querySelector('[data-purpose="session-timeline"]');
            
            if (newTimeline && oldTimeline) {
                oldTimeline.replaceWith(newTimeline);
                
                // Re-initialize Lucide icons
                if (typeof lucide !== 'undefined') {
                    lucide.createIcons();
                }
                
                // Re-attach event listeners for the new buttons
                attachSessionListeners();
                
                console.log('Session timeline updated successfully');
            }

            // Also refresh the Daily Attendance table
            refreshDailyAttendanceTable(newDoc);
        })
        .catch(error => {
            console.error('Error refreshing timeline:', error);
        });
    }

    function refreshDailyAttendanceTable(newDoc) {
        try {
            // Find the new Daily Attendance table from updated page
            const newDailyTab = newDoc.querySelector('#daily-attendance-tab');
            const oldDailyTab = document.querySelector('#daily-attendance-tab');
            
            if (newDailyTab && oldDailyTab) {
                // Replace the entire Daily Attendance tab content
                oldDailyTab.replaceWith(newDailyTab);
                
                // Reset to Day 1
                currentDay = 1;
                
                // Re-attach event listeners to day toggle buttons
                document.querySelectorAll('.day-toggle-btn').forEach(button => {
                    button.removeEventListener('click', null);
                    button.addEventListener('click', function() {
                        const dayNum = this.getAttribute('data-day');
                        switchDay(dayNum);
                    });
                });

                // Re-attach filter listeners
                const dailySearchInput = document.getElementById('daily-search');
                const dailyStatusFilter = document.getElementById('daily-status-filter');
                const dailySessionFilter = document.getElementById('daily-session-filter');
                
                if (dailySearchInput) {
                    dailySearchInput.removeEventListener('keyup', filterDailyAttendance);
                    dailySearchInput.addEventListener('keyup', filterDailyAttendance);
                }
                
                if (dailyStatusFilter) {
                    dailyStatusFilter.removeEventListener('change', filterDailyAttendance);
                    dailyStatusFilter.addEventListener('change', filterDailyAttendance);
                }

                if (dailySessionFilter) {
                    dailySessionFilter.removeEventListener('change', filterDailyAttendance);
                    dailySessionFilter.addEventListener('change', filterDailyAttendance);
                }

                attachDailyPaginationListeners();

                // Reinitialize session dropdown for day 1
                updateSessionDropdown(1);

                console.log('Daily Attendance table refreshed successfully');
            }
        } catch (error) {
            console.error('Error refreshing daily attendance table:', error);
        }
    }

    function attachSessionListeners() {
        const endBtn = document.getElementById('endCurrentSessionBtn');
        const startBtn = document.getElementById('startNextSessionBtn');
        
        if (endBtn) {
            endBtn.onclick = null;
            endBtn.addEventListener('click', function() {
                endCurrentSession();
            });
        }
        
        if (startBtn) {
            startBtn.onclick = null;
            startBtn.addEventListener('click', function() {
                startNextSession();
            });
        }

        // Reattach day toggle button listeners
        document.querySelectorAll('.session-day-toggle-btn').forEach(button => {
            button.removeEventListener('click', null);
            button.addEventListener('click', function() {
                const dayNum = this.getAttribute('data-day');
                switchSessionDay(dayNum);
            });
        });
    }

    function endCurrentSession() {
        showConfirmDialog(
            'End Current Session',
            'Are you sure you want to end the current session? This action cannot be undone.',
            () => {
                const eventId = document.getElementById('endCurrentSessionBtnModal')?.getAttribute('data-event-id') || 
                               document.getElementById('endCurrentSessionBtn')?.getAttribute('data-event-id');
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
                
                if (!eventId) {
                    showToast('Event ID not found', 'error');
                    return;
                }
                
                console.log('Ending session for event:', eventId);
                console.log('CSRF Token:', csrfToken);
                
                fetch(`/event/${eventId}/end-session`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ event_id: eventId })
                })
                .then(response => {
                    console.log('Response status:', response.status);
                    if (!response.ok) {
                        throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                    }
                    return response.json();
                })
                .then(data => {
                    console.log('Response data:', data);
                    if (data.success) {
                        showToast('Session ended successfully!', 'success');
                        // Reload page after short delay to show success message
                        setTimeout(() => {
                            location.reload();
                        }, 1000);
                    } else {
                        showToast('Error: ' + (data.message || 'Failed to end session'), 'error');
                    }
                })
                .catch(error => {
                    console.error('Full error:', error);
                    showToast(error.message || 'An error occurred while ending the session', 'error');
                });
            }
        );
    }

    function startNextSession() {
        const eventId = document.getElementById('startNextSessionBtnModal')?.getAttribute('data-event-id') || 
                       document.getElementById('startNextSessionBtn')?.getAttribute('data-event-id');
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
        
        if (!eventId) {
            showToast('Event ID not found', 'error');
            return;
        }
        
        console.log('Starting next session for event:', eventId);
        
        fetch(`/event/${eventId}/start-session`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ event_id: eventId })
        })
        .then(response => {
            console.log('Response status:', response.status);
            return response.json().then(data => ({ status: response.status, data }));
        })
        .then(({ status, data }) => {
            console.log('Response data:', data);
            
            if (status === 403) {
                // Event date hasn't come yet
                showToast('Error: ' + data.message, 'error');
                return;
            }
            
            if (data.success) {
                showToast('Session started successfully!', 'success');
                // Trigger a broadcast to notify other windows/tabs of the session change
                if (window.opener && window.opener.loadSessionManagersForAllEvents) {
                    window.opener.loadSessionManagersForAllEvents();
                }
                // Reload page after short delay to show success message
                setTimeout(() => {
                    location.reload();
                }, 1000);
            } else {
                showToast('Error: ' + (data.message || 'Failed to start session'), 'error');
            }
        })
        .catch(error => {
            console.error('Full error:', error);
            showToast(error.message || 'An error occurred while starting the session', 'error');
        });
    }

    function escapeHtml(text) {
        const map = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        };
        return text.replace(/[&<>"']/g, m => map[m]);
    }

    // Export Dropdown Toggle
    
    // Filter Session Attendance Tables
    function filterSessionAttendance(selectElement) {
        const selectedValue = selectElement.value;
        
        // Find the parent session day block container
        let dayBlock = selectElement.closest('.session-day-block');
        
        if (!dayBlock) {
            const dayNum = selectElement.getAttribute('data-day') || currentSessionDay;
            dayBlock = document.querySelector(`.session-day-block[data-day="${dayNum}"]`);
        }

        if (!dayBlock) {
            console.warn('Could not find session day block');
            return;
        }

        const sessionContainers = dayBlock.querySelectorAll('.session-table-wrapper');

        // If "All Sessions" is selected (value = "all"), show all containers
        if (selectedValue === 'all') {
            sessionContainers.forEach(container => {
                container.style.display = 'block';
            });
            return;
        }

        // Otherwise, filter by selected session ID
        sessionContainers.forEach(container => {
            if (container.getAttribute('data-session-id') === selectedValue) {
                container.style.display = 'block';
            } else {
                container.style.display = 'none';
            }
        });
    }

    function exportToPDF() {
        const eventName = document.querySelector('h1.text-3xl')?.textContent || 'Event';
        const tableHtml = document.getElementById('main-daily-table')?.outerHTML || '';
        
        if (!tableHtml) {
            showToast('No data to export', 'error');
            return;
        }

        // Basic PDF export - requires html2pdf library (you may need to add it)
        const element = document.createElement('div');
        element.innerHTML = `
            <h1>${escapeHtml(eventName)} - Daily Attendance Report</h1>
            <p>Generated: ${new Date().toLocaleString()}</p>
            ${tableHtml}
        `;

        const opt = {
            margin: 10,
            filename: `daily-attendance-${new Date().toISOString().split('T')[0]}.pdf`,
            image: { type: 'jpeg', quality: 0.98 },
            html2canvas: { scale: 2 },
            jsPDF: { orientation: 'landscape', unit: 'mm', format: 'a4' }
        };

        // Check if html2pdf is available
        if (typeof html2pdf !== 'undefined') {
            html2pdf().set(opt).from(element).save();
            showToast('PDF exported successfully!', 'success');
        } else {
            // Fallback: use browser print
            const printWindow = window.open('', '', 'height=600,width=800');
            printWindow.document.write(`
                <html>
                <head>
                    <title>${eventName} - Daily Attendance</title>
                    <style>
                        body { font-family: Arial, sans-serif; margin: 20px; }
                        table { border-collapse: collapse; width: 100%; margin-top: 20px; }
                        th, td { border: 1px solid #ddd; padding: 10px; text-align: left; }
                        th { background-color: #f2f2f2; font-weight: bold; }
                        tr:nth-child(even) { background-color: #f9f9f9; }
                    </style>
                </head>
                <body>
                    <h1>${escapeHtml(eventName)} - Daily Attendance Report</h1>
                    <p>Generated: ${new Date().toLocaleString()}</p>
                    ${tableHtml}
                </body>
                </html>
            `);
            printWindow.document.close();
            printWindow.print();
            showToast('Opened print dialog for PDF export', 'success');
        }

        // Close dropdown
        document.getElementById('export-dropdown-menu').classList.add('hidden');
    }

    // Export to Excel
    function exportToExcel() {
        const eventName = document.querySelector('h1.text-3xl')?.textContent || 'Event';
        const table = document.getElementById('main-daily-table');
        
        if (!table) {
            showToast('No data to export', 'error');
            return;
        }

        // Get table data
        const rows = [];
        table.querySelectorAll('tr').forEach(tr => {
            const cells = [];
            tr.querySelectorAll('td, th').forEach(td => {
                cells.push(td.textContent.trim());
            });
            if (cells.length > 0) {
                rows.push(cells);
            }
        });

        // Create CSV content
        let csvContent = `Event: ${eventName}\nGenerated: ${new Date().toLocaleString()}\n\n`;
        csvContent += rows.map(row => row.map(cell => `"${cell.replace(/"/g, '""')}"`).join(',')).join('\n');

        // Create blob and download
        const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
        const link = document.createElement('a');
        const url = URL.createObjectURL(blob);
        
        link.setAttribute('href', url);
        link.setAttribute('download', `daily-attendance-${new Date().toISOString().split('T')[0]}.csv`);
        link.style.visibility = 'hidden';
        
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        
        showToast('Excel file downloaded successfully!', 'success');

        // Close dropdown
        document.getElementById('export-dropdown-menu').classList.add('hidden');
    }

    // Export Attendee List to PDF
    function exportAttendeeListToPDF() {
        const eventName = document.querySelector('h1.text-3xl')?.textContent || 'Event';
        const table = document.querySelector('#attendee-list-tab table');
        
        if (!table) {
            showToast('No data to export', 'error');
            return;
        }

        // Clone the table and remove the Actions column (last column)
        const clonedTable = table.cloneNode(true);
        clonedTable.querySelectorAll('th:last-child, td:last-child').forEach(cell => {
            cell.remove();
        });
        const tableHtml = clonedTable.outerHTML;

        // Create a print-friendly version
        const printWindow = window.open('', '', 'height=600,width=800');
        
        printWindow.document.write(`
            <html>
            <head>
                <title>${escapeHtml(eventName)} - Attendee List</title>
                <style>
                    body { font-family: Arial, sans-serif; margin: 0; padding: 20px; }
                    .header { background: linear-gradient(135deg, #003f87 0%, #0056b3 100%); color: white; padding: 30px; border-radius: 8px; margin-bottom: 30px; }
                    .header h1 { margin: 0 0 10px 0; font-size: 28px; }
                    .header-info { display: flex; justify-content: space-between; font-size: 13px; margin-top: 15px; }
                    .header-info div { display: flex; gap: 30px; }
                    table { width: 100%; border-collapse: collapse; margin-top: 20px; }
                    th { background-color: #f3f4f6; padding: 12px; text-align: left; font-weight: bold; border: 1px solid #ddd; color: #1f2937; }
                    td { padding: 10px; border: 1px solid #ddd; color: #374151; }
                    tr:nth-child(even) { background-color: #f9fafb; }
                    .footer { color: #999; font-size: 12px; margin-top: 30px; text-align: center; border-top: 1px solid #ddd; padding-top: 15px; }
                </style>
            </head>
            <body>
                <div class="header">
                    <h1>📋 ${escapeHtml(eventName)}</h1>
                    <div class="header-info">
                        <div>
                            <span><strong>Document:</strong> Attendee List Report</span>
                            <span><strong>Generated:</strong> ${new Date().toLocaleString()}</span>
                        </div>
                    </div>
                </div>
                ${tableHtml}
                <div class="footer">
                    <p>This report was generated by the Event Attendance Management System</p>
                    <p>© 2024 STI College Balagtas. All rights reserved.</p>
                </div>
            </body>
            </html>
        `);
        printWindow.document.close();
        printWindow.print();
        
        showToast('PDF export ready - use your browser print dialog to save as PDF', 'success');
        document.getElementById('attendee-export-dropdown-menu').classList.add('hidden');
    }

    // Export Attendee List to Excel
    function exportAttendeeListToExcel() {
        const eventName = document.querySelector('h1.text-3xl')?.textContent || 'Event';
        const table = document.querySelector('#attendee-list-tab table');
        
        if (!table) {
            showToast('No data to export', 'error');
            return;
        }

        // Get table data, excluding the Actions column (last column)
        const rows = [];
        table.querySelectorAll('tr').forEach(tr => {
            const cells = [];
            const allCells = tr.querySelectorAll('td, th');
            // Loop through all cells except the last one (Actions column)
            for (let i = 0; i < allCells.length - 1; i++) {
                const cell = allCells[i];
                let cellText = '';
                
                // For the first column (Full Name), extract from the span to avoid avatar initials
                if (i === 0) {
                    const nameSpan = cell.querySelector('span');
                    if (nameSpan) {
                        cellText = nameSpan.textContent.trim();
                    } else {
                        cellText = cell.textContent.trim();
                    }
                } else {
                    cellText = cell.textContent.trim();
                }
                
                cells.push(cellText);
            }
            if (cells.length > 0) {
                rows.push(cells);
            }
        });

        // Create CSV content
        let csvContent = `Event: ${eventName}\nGenerated: ${new Date().toLocaleString()}\n\n`;
        csvContent += rows.map(row => row.map(cell => `"${cell.replace(/"/g, '""')}"`).join(',')).join('\n');

        // Create blob and download
        const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
        const link = document.createElement('a');
        const url = URL.createObjectURL(blob);
        
        link.setAttribute('href', url);
        link.setAttribute('download', `attendee-list-${new Date().toISOString().split('T')[0]}.csv`);
        link.style.visibility = 'hidden';
        
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        
        showToast('Excel file downloaded successfully!', 'success');
        document.getElementById('attendee-export-dropdown-menu').classList.add('hidden');
    }

    // Import Attendee List from CSV
    let importCancelled = false; // Flag to track if import is cancelled

    async function importAttendeeList(file) {
        importCancelled = false; // Reset flag at start
        
        return new Promise((resolve, reject) => {
            const reader = new FileReader();
            
            reader.onload = async function(e) {
                try {
                    const csv = e.target.result;
                    const lines = csv.split('\n').filter(line => line.trim());
                    
                    // Skip header rows (Event:, Generated:, empty line)
                    const dataStart = lines.findIndex(line => line.includes('Full Name'));
                    if (dataStart === -1) {
                        showToast('Invalid CSV format. Missing header row.', 'error');
                        reject('Invalid format');
                        return;
                    }
                    
                    // Parse CSV data
                    const csvHeader = lines[dataStart].split(',').map(cell => cell.replace(/^"|"$/g, '').trim().toLowerCase());
                    const csvLines = lines.slice(dataStart + 1);
                    const students = [];
                    
                    csvLines.forEach((line, index) => {
                        const cells = line.split(',').map(cell => cell.replace(/^"|"$/g, '').trim());
                        if (cells.length >= 2 && cells[0] && cells[1]) {
                            const row = {};
                            csvHeader.forEach((header, cellIndex) => {
                                row[header] = cells[cellIndex] || '';
                            });

                            students.push({
                                name: row['full name'] || row['name'] || cells[0],
                                snumber: row['student id'] || row['student_id'] || cells[1],
                                section: row['section'] || 'General',
                                program: row['program'] || null,
                                rfid: row['rfid'] || null
                            });
                        }
                    });
                    
                    if (students.length === 0) {
                        showToast('No valid student records found in CSV', 'error');
                        reject('No records');
                        return;
                    }
                    
                    // Create and show progress modal
                    showImportProgressModal(students.length);

                    // Prepare batch data
                    const batchData = students.map(student => ({
                        student_name: student.name,
                        student_number: student.snumber,
                        section: student.section,
                        program: student.program || null,
                        rfid: student.rfid
                    }));

                    try {
                        // Check if import was cancelled before starting
                        if (importCancelled) {
                            closeImportProgressModal();
                            showToast('Import cancelled before starting.', 'info');
                            reject('Import cancelled');
                            return;
                        }

                        // Update progress to show processing
                        updateImportProgress(0, students.length, 0, 0, 'Processing...');

                        // Send batch request
                        const response = await fetch(`/event/${eventId}/batch-add-students`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                            },
                            body: JSON.stringify({
                                students: batchData
                            })
                        });

                        if (!response.ok) {
                            const errorData = await response.json();
                            throw new Error(errorData.message || 'Batch import failed');
                        }

                        const result = await response.json();

                        // Update final progress
                        updateImportProgress(
                            students.length,
                            students.length,
                            result.results.added,
                            result.results.errors.length,
                            'Complete'
                        );

                        // Close progress modal and show results
                        closeImportProgressModal();
                        showToast(result.message, result.success ? 'success' : 'error');

                        // Refresh page if any students were imported
                        if (result.results.added > 0) {
                            setTimeout(() => {
                                location.reload();
                            }, 2000);
                        }

                        resolve();

                    } catch (err) {
                        console.error('Batch import error:', err);
                        closeImportProgressModal();
                        showToast('Error during batch import: ' + err.message, 'error');
                        reject(err);
                    }
                    
                    // Reset file input
                    document.getElementById('attendee-import-file').value = '';
                    resolve();
                    
                } catch (error) {
                    console.error('Import error:', error);
                    closeImportProgressModal();
                    showToast('Error reading CSV file', 'error');
                    reject(error);
                }
            };
            
            reader.onerror = function() {
                closeImportProgressModal();
                showToast('Error reading file', 'error');
                reject(reader.error);
            };
            
            reader.readAsText(file);
        });
    }

    // Show import progress modal
    function showImportProgressModal(totalStudents) {
        // Remove existing modal if any
        const existingModal = document.getElementById('importProgressModal');
        if (existingModal) {
            existingModal.remove();
        }

        const modal = document.createElement('div');
        modal.id = 'importProgressModal';
        modal.className = 'fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50';
        modal.innerHTML = `
            <div class="bg-surface rounded-2xl shadow-2xl w-full max-w-md mx-2 sm:mx-4 border border-outline p-6 sm:p-8">
                <!-- Header -->
                <div class="flex items-center gap-3 mb-6">
                    <span class="material-symbols-outlined text-3xl text-primary animate-spin">progress_activity</span>
                    <h2 class="text-2xl font-bold text-on-surface">Importing Students</h2>
                </div>

                <!-- Progress Bar -->
                <div class="mb-6">
                    <div class="bg-surface-container-high rounded-full h-3 overflow-hidden shadow-inner">
                        <div id="progressBarFill" class="bg-gradient-to-r from-primary to-primary-fixed h-full w-0 transition-all duration-300" style="width: 0%"></div>
                    </div>
                </div>

                <!-- Stats -->
                <div class="space-y-3 mb-6">
                    <div class="flex justify-between items-center">
                        <span class="text-sm font-semibold text-on-surface">Progress:</span>
                        <span id="progressText" class="text-sm font-bold text-primary">0 / ${totalStudents}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-sm font-semibold text-on-surface">Percentage:</span>
                        <span id="percentageText" class="text-sm font-bold text-primary">0%</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-sm font-semibold text-secondary-fixed">Succeeded:</span>
                        <span id="succeededText" class="text-sm font-bold text-secondary-fixed">0</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-sm font-semibold text-error">Failed:</span>
                        <span id="failedText" class="text-sm font-bold text-error">0</span>
                    </div>
                </div>

                <!-- Status Message -->
                <div class="bg-surface-container-low p-3 rounded-lg mb-6 border border-outline">
                    <p id="statusMessage" class="text-xs text-on-surface-variant text-center font-medium">Initializing import...</p>
                </div>

                <!-- Action Buttons -->
                <div class="flex gap-3">
                    <button id="cancelImportBtn" class="flex-1 py-2.5 border border-outline text-on-surface font-bold rounded-lg hover:bg-surface-container transition-colors" onclick="cancelImport()">
                        Cancel
                    </button>
                    <button id="closeImportBtn" class="flex-1 py-2.5 bg-primary text-on-primary font-bold rounded-lg hover:bg-primary-fixed transition-colors hidden" onclick="closeImportProgressModal()">
                        Close
                    </button>
                </div>
            </div>
        `;

        document.body.appendChild(modal);
    }

    // Update import progress
    function updateImportProgress(current, total, succeeded, failed, statusMessage = null) {
        const percentage = Math.round((current / total) * 100);

        document.getElementById('progressBarFill').style.width = percentage + '%';
        document.getElementById('progressText').textContent = `${current} / ${total}`;
        document.getElementById('percentageText').textContent = `${percentage}%`;
        document.getElementById('succeededText').textContent = succeeded;
        document.getElementById('failedText').textContent = failed;

        // Update status message
        const statusMessageEl = document.getElementById('statusMessage');
        if (statusMessageEl) {
            statusMessageEl.textContent = statusMessage || `Processing student ${current} of ${total}...`;
        }
    }

    // Close import progress modal
    function closeImportProgressModal() {
        const modal = document.getElementById('importProgressModal');
        if (modal) {
            modal.remove();
        }
    }

    // Cancel import process
    function cancelImport() {
        importCancelled = true;
        
        const cancelBtn = document.getElementById('cancelImportBtn');
        if (cancelBtn) {
            cancelBtn.disabled = true;
            cancelBtn.textContent = 'Cancelling...';
            cancelBtn.classList.add('opacity-50', 'cursor-not-allowed');
        }
    }

    // Export all students' QR codes as a combined HTML page
    async function exportAllQRCodes() {
        try {
            // Get all student rows from the attendee list table
            const studentRows = document.querySelectorAll('#attendee-list-tab table tbody tr');
            
            if (studentRows.length === 0) {
                showToast('No students to export', 'warning');
                return;
            }

            // Collect all student data
            const students = [];
            studentRows.forEach(row => {
                const cells = row.querySelectorAll('td');
                if (cells.length >= 2) {
                    // Extract student ID from cell 1 (Student ID column)
                    const studentId = cells[1]?.textContent?.trim() || '';
                    
                    // Extract student name from cell 0 (Name column) - get the span with the actual name
                    let studentName = 'Unknown';
                    const nameSpan = cells[0]?.querySelector('span');
                    if (nameSpan) {
                        studentName = nameSpan.textContent.trim();
                    } else {
                        studentName = cells[0]?.textContent?.trim() || 'Unknown';
                    }
                    
                    if (studentId && studentName !== 'Unknown') {
                        students.push({ id: studentId, name: studentName });
                    }
                }
            });

            if (students.length === 0) {
                showToast('Could not extract student data', 'error');
                return;
            }

            // Create HTML with QR codes for all students
            let htmlContent = '<!DOCTYPE html>\n<html lang="en">\n<head>\n<meta charset="UTF-8">\n<meta name="viewport" content="width=device-width, initial-scale=1.0">\n<title>QR Codes - Event Attendance</title>\n<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"><\/script>\n<style>\nbody { font-family: Arial, sans-serif; margin: 0; padding: 20px; background-color: #f5f5f5; }\n.container { max-width: 1200px; margin: 0 auto; }\nh1 { text-align: center; color: #333; margin-bottom: 30px; }\n.qr-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 20px; }\n.qr-card { background: white; border: 1px solid #ddd; border-radius: 8px; padding: 15px; text-align: center; box-shadow: 0 2px 4px rgba(0,0,0,0.1); page-break-inside: avoid; }\n.qr-code { margin: 10px auto; display: flex; justify-content: center; }\n.qr-code > div { display: inline-block !important; }\n.qr-info { margin-top: 10px; font-size: 12px; }\n.student-name { font-weight: bold; color: #333; margin-bottom: 5px; font-size: 13px; word-wrap: break-word; }\n.student-id { color: #666; font-size: 11px; }\n@media print { body { background-color: white; } .qr-card { page-break-inside: avoid; } }\n</style>\n</head>\n<body>\n<div class="container">\n<h1>Event Attendance QR Codes</h1>\n<div class="qr-grid">\n';

            // Generate QR code for each student
            students.forEach((student, index) => {
                htmlContent += '<div class="qr-card">\n' +
                    '<div class="qr-code" id="qr-code-' + index + '"><\/div>\n' +
                    '<div class="qr-info">\n' +
                    '<div class="student-name">' + escapeHtml(student.name) + '<\/div>\n' +
                    '<div class="student-id">ID: ' + escapeHtml(student.id) + '<\/div>\n' +
                    '</div>\n</div>\n';
            });

            htmlContent += '</div>\n</div>\n<script>\nvar qrStudents = ' + JSON.stringify(students) + ';\n' +
                'qrStudents.forEach(function(student, index) {\n' +
                'new QRCode(document.getElementById("qr-code-" + index), {\n' +
                'text: student.id,\nwidth: 150,\nheight: 150,\n' +
                'colorDark: "#000000",\ncolorLight: "#ffffff",\n' +
                'correctLevel: QRCode.CorrectLevel.H\n});\n});\n<\/script>\n</body>\n</html>';

            // Create and download the HTML file
            const blob = new Blob([htmlContent], { type: 'text/html' });
            const url = URL.createObjectURL(blob);
            const link = document.createElement('a');
            link.href = url;
            link.download = `qr-codes-${new Date().toISOString().split('T')[0]}.html`;
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
            URL.revokeObjectURL(url);

            showToast(`Generated QR codes for ${students.length} students`, 'success');
        } catch (error) {
            console.error('QR code export error:', error);
            showToast('Error generating QR codes', 'error');
        }
    }
