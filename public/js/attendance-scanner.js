/**
 * Attendance Scanner Module
 * Handles QR/barcode scanning, session management, and attendance marking
 */

document.addEventListener('DOMContentLoaded', function() {
    let qrScanner;
    let qrCameraActive = false;
    let scannedStudents = [];
    let currentTab = 'qr';
    let lastBarcodeTime = 0;
    let attendanceData = [];
    let allSessions = [];
    let hasActiveSession = false;
    const COOLDOWN_DURATION = 15000; // 15 seconds in milliseconds
    let lastScannedStudents = {}; // Track last scan time and mode per student ID
    
    // Event ID and settings from data attributes
    const eventId = document.body.dataset.eventId;
    const requireActionPrompts = document.body.dataset.requireActionPrompts === '1';
    const STORAGE_KEY = `scanned_students_${eventId}`;
    const SCAN_MODE_STORAGE_KEY = `scan_mode_${eventId}`;
    
    let currentScanMode = 'normal'; // SCAN MODE: normal, break, or timeout
    
    const qrTabBtn = document.getElementById('qrTabBtn');
    const barcodeTabBtn = document.getElementById('barcodeTabBtn');
    const qrTab = document.getElementById('qrTab');
    const barcodeTab = document.getElementById('barcodeTab');
    const toggleQRCameraBtn = document.getElementById('toggleQRCameraBtn');
    const qrCameraButtonText = document.getElementById('qrCameraButtonText');
    const qrLastScannedText = document.getElementById('qrLastScannedText');
    const barcodeLastScannedText = document.getElementById('barcodeLastScannedText');
    const qrScanStatus = document.getElementById('qrScanStatus');
    const barcodeScanStatus = document.getElementById('barcodeScanStatus');
    const qrStatusMessage = document.getElementById('qrStatusMessage');
    const barcodeStatusMessage = document.getElementById('barcodeStatusMessage');
    const scannedList = document.getElementById('scannedList');
    const scannedCount = document.getElementById('scannedCount');
    const barcodeInput = document.getElementById('barcodeInput');
    const scanSessionFilter = document.getElementById('scan-session-filter');
    const scanSearch = document.getElementById('scan-search');

    // ===== Load Session Manager Info =====
    async function loadSessionManagerInfo() {
        try {
            const response = await fetch(`/api/event/${eventId}/active-session-manager`);
            const data = await response.json();
            
            if (data.success && data.manager) {
                document.getElementById('managerName').textContent = data.manager.name;
                document.getElementById('managerEmail').textContent = data.manager.email;
            } else {
                // Keep the event creator as fallback (already set in HTML)
            }
        } catch (error) {
            // Keep the event creator as fallback
        }
    }
    
    // Load manager info when page loads
    loadSessionManagerInfo();

    // ===== LocalStorage Management Functions =====
    function saveScannedStudentsToStorage() {
        try {
            localStorage.setItem(STORAGE_KEY, JSON.stringify(scannedStudents));
        } catch (e) {
            console.warn('Failed to save to localStorage:', e);
        }
    }

    function loadScannedStudentsFromStorage() {
        try {
            const stored = localStorage.getItem(STORAGE_KEY);
            if (stored) {
                scannedStudents = JSON.parse(stored);
                renderScannedList();
            }
        } catch (e) {
            console.warn('Failed to load from localStorage:', e);
        }
    }

    function clearScannedStudentsStorage() {
        try {
            localStorage.removeItem(STORAGE_KEY);
        } catch (e) {
            console.warn('Failed to clear localStorage:', e);
        }
    }
    // ===== End LocalStorage Management =====

    // ===== SCAN MODE MANAGEMENT =====
    function loadScanModeFromStorage() {
        try {
            const stored = localStorage.getItem(SCAN_MODE_STORAGE_KEY);
            if (stored && ['normal', 'break', 'timeout'].includes(stored)) {
                currentScanMode = stored;
                updateScanModeUI();
            }
        } catch (e) {
            console.warn('Failed to load scan mode from storage:', e);
        }
    }

    function saveScanModeToStorage() {
        try {
            localStorage.setItem(SCAN_MODE_STORAGE_KEY, currentScanMode);
        } catch (e) {
            console.warn('Failed to save scan mode to storage:', e);
        }
    }

    function updateScanModeUI() {
        // Update mode button states in both QR and Barcode tabs
        document.querySelectorAll('.scan-mode-btn').forEach(btn => {
            btn.classList.remove('active');
            if (btn.dataset.mode === currentScanMode) {
                btn.classList.add('active');
            }
        });
    }

    function initializeScanModeListeners() {
        // Add listeners for all mode buttons
        document.querySelectorAll('.scan-mode-btn').forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                currentScanMode = this.dataset.mode;
                saveScanModeToStorage();
                updateScanModeUI();
                
                // Show confirmation message
                const modeNames = { normal: 'Normal', break: 'Break', timeout: 'Timeout' };
                const message = `Scan mode changed to ${modeNames[currentScanMode]}`;
                if (currentTab === 'qr') {
                    showQRStatus(message, 'info');
                } else {
                    showBarcodeStatus(message, 'info');
                }
            });
        });
    }
    // ===== END SCAN MODE MANAGEMENT =====

    // ===== COOLDOWN MANAGEMENT =====
    function isStudentInCooldown(studentId) {
        const lastScanEntry = lastScannedStudents[studentId];
        if (!lastScanEntry) return false;
        
        const timeSinceLastScan = Date.now() - lastScanEntry.time;
        return timeSinceLastScan < COOLDOWN_DURATION;
    }

    function getTimeUntilCooldownEnds(studentId) {
        const lastScanEntry = lastScannedStudents[studentId];
        if (!lastScanEntry) return 0;
        
        const timeSinceLastScan = Date.now() - lastScanEntry.time;
        const timeRemaining = COOLDOWN_DURATION - timeSinceLastScan;
        return Math.ceil(timeRemaining / 1000); // Return seconds
    }

    function recordStudentScan(studentId) {
        lastScannedStudents[studentId] = {
            time: Date.now(),
            mode: currentScanMode
        };
    }

    function cleanupOldCooldowns() {
        // Remove entries older than cooldown duration to prevent memory leak
        const now = Date.now();
        Object.keys(lastScannedStudents).forEach(studentId => {
            if (now - lastScannedStudents[studentId].time > COOLDOWN_DURATION) {
                delete lastScannedStudents[studentId];
            }
        });
    }
    // ===== END COOLDOWN MANAGEMENT =====

    // Tab switching
    qrTabBtn.addEventListener('click', function() {
        currentTab = 'qr';
        qrTab.classList.remove('hidden');
        barcodeTab.classList.add('hidden');
        qrTabBtn.classList.remove('text-on-surface-variant', 'border-transparent');
        qrTabBtn.classList.add('text-on-surface', 'border-primary');
        barcodeTabBtn.classList.remove('text-on-surface', 'border-primary');
        barcodeTabBtn.classList.add('text-on-surface-variant', 'border-transparent');
        
        // Clear barcode input when switching away
        barcodeInput.value = '';
    });

    barcodeTabBtn.addEventListener('click', function() {
        currentTab = 'barcode';
        barcodeTab.classList.remove('hidden');
        qrTab.classList.add('hidden');
        barcodeTabBtn.classList.remove('text-on-surface-variant', 'border-transparent');
        barcodeTabBtn.classList.add('text-on-surface', 'border-primary');
        qrTabBtn.classList.remove('text-on-surface', 'border-primary');
        qrTabBtn.classList.add('text-on-surface-variant', 'border-transparent');
        
        // Stop QR camera if running
        if (qrCameraActive) {
            stopQRCamera();
        }
        
        // Focus on barcode input for scanner
        setTimeout(() => barcodeInput.focus(), 100);
    });

    // Initialize QR Scanner
    function initializeQRScanner() {
        qrScanner = new Html5Qrcode("qrReader");
    }

    // Load attendance data from server
    function loadAttendanceData() {
        // Clean up old cooldown entries
        cleanupOldCooldowns();
        
        // Initialize column visibility based on toggle
        updateColumnVisibility();
        
        // Show/hide mode selectors based on requireActionPrompts setting
        const qrModeSelector = document.getElementById('qrScanModeSelector');
        const barcodeModeSelector = document.getElementById('barcodeScanModeSelector');
        if (qrModeSelector && barcodeModeSelector) {
            if (requireActionPrompts) {
                qrModeSelector.style.display = '';
                barcodeModeSelector.style.display = '';
            } else {
                qrModeSelector.style.display = 'none';
                barcodeModeSelector.style.display = 'none';
            }
        }
        
        fetch(`/event/${eventId}/attendance-data`)
            .then(response => response.json())
            .then(data => {
                attendanceData = data.attendanceData || [];
                allSessions = data.sessions || [];
                
                
                // Check if there's an active session
                hasActiveSession = allSessions.some(s => s.status === 'active');
                
                // Populate session dropdown
                populateSessionFilter();
                
                // Update camera button state
                updateCameraButtonState();
                
                // Sync scanned list with attendance data
                syncScannedListWithAttendanceData();
                
                // Render initial table
                renderAttendanceTable();
            })
            .catch(error => {
                console.error('[ERROR] Failed to load attendance data:', error);
            });
    }

    // Sync scanned students with attendance data from server
    function syncScannedListWithAttendanceData() {
        // Remove scanned students that are no longer in attendance data
        scannedStudents = scannedStudents.filter(scannedStudent => {
            return attendanceData.some(a => a.student_id === scannedStudent.snumber);
        });
        saveScannedStudentsToStorage();
        renderScannedList();
    }

    // Update column visibility based on toggle state
    function updateColumnVisibility() {
        const durationHeader = document.getElementById('duration-header');
        const cyclesHeader = document.getElementById('cycles-header');
        
        
        if (requireActionPrompts) {
            durationHeader.style.display = '';
            cyclesHeader.style.display = '';
        } else {
            durationHeader.style.display = 'none';
            cyclesHeader.style.display = 'none';
        }
    }

    // Populate session filter dropdown
    function populateSessionFilter() {
        if (!scanSessionFilter) return;
        
        scanSessionFilter.innerHTML = '';
        
        // Separate active and non-active sessions
        const activeSessions = allSessions.filter(s => s.status === 'active');
        const otherSessions = allSessions.filter(s => s.status !== 'active');
        
        // Add active sessions first
        activeSessions.forEach(session => {
            const option = document.createElement('option');
            option.value = session.id;
            option.textContent = `Day ${session.day_number}, Session ${session.session_number} (Active)`;
            scanSessionFilter.appendChild(option);
        });
        
        // Add other sessions and label completed ones
        otherSessions.forEach(session => {
            const option = document.createElement('option');
            option.value = session.id;
            const sessionLabel = session.status === 'completed'
                ? `Day ${session.day_number}, Session ${session.session_number} (Completed)`
                : `Day ${session.day_number}, Session ${session.session_number} (Not Active)`;
            option.textContent = sessionLabel;
            scanSessionFilter.appendChild(option);
        });
        
        // Set active session as default if available
        if (activeSessions.length > 0) {
            scanSessionFilter.value = activeSessions[0].id;
        }
    }

    // Render attendance table
    function renderAttendanceTable() {
        const table = document.getElementById('attendance-table-body');
        if (!table) return;
        
        // Update column visibility
        updateColumnVisibility();

        const sessionId = scanSessionFilter?.value;
        const searchTerm = scanSearch?.value.toLowerCase() || '';


        let filtered = attendanceData;

        // Only filter by session if a session is selected and exists
        if (sessionId) {
            filtered = filtered.filter(a => a.session_id == sessionId);
        }

        // Filter by search
        if (searchTerm) {
            filtered = filtered.filter(a => 
                a.student_name.toLowerCase().includes(searchTerm) ||
                a.student_id.toLowerCase().includes(searchTerm) ||
                (a.student_program || '').toLowerCase().includes(searchTerm) ||
                (a.student_rfid || '').toLowerCase().includes(searchTerm)
            );
        }

        // Sort: marked present first, then by student name
        filtered.sort((a, b) => {
            if (a.time_in && !b.time_in) return -1;
            if (!a.time_in && b.time_in) return 1;
            return a.student_name.localeCompare(b.student_name);
        });

        if (filtered.length === 0) {
            const colspan = requireActionPrompts ? '10' : '8';
            table.innerHTML = `<tr><td colspan="${colspan}" class="px-8 py-12 text-center text-gray-500">No records found</td></tr>`;
            document.getElementById('attendance-record-count').textContent = '0';
            return;
        }

        table.innerHTML = filtered.map(record => {
            // Status badge
            let statusBadge = '';
            if (record.status === 'present') {
                statusBadge = '<span class="px-3 py-1 bg-green-100 text-green-800 rounded-full text-xs font-semibold">Present</span>';
            } else if (record.status === 'left_session') {
                statusBadge = '<span class="px-3 py-1 bg-yellow-100 text-yellow-800 rounded-full text-xs font-semibold">On Break</span>';
            } else {
                statusBadge = '<span class="px-3 py-1 bg-surface-container text-on-surface-variant rounded-full text-xs font-semibold">Absent</span>';
            }
            
            const timeDisplay = record.time_in 
                ? new Date(record.time_in).toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit', second: '2-digit' })
                : '—';
            
            // Get time_out from the last cycle if available
            let timeOutDisplay = '—';
            if (record.cycles_data && Array.isArray(record.cycles_data)) {
                const cycles = record.cycles_data;
                for (let i = cycles.length - 1; i >= 0; i--) {
                    if (cycles[i].time_out) {
                        timeOutDisplay = new Date(cycles[i].time_out).toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit', second: '2-digit' });
                        break;
                    }
                }
            }

            // Calculate duration if toggle is ON
            let durationDisplay = '—';
            let cyclesDisplay = '—';
            if (requireActionPrompts && record.cycles_data && Array.isArray(record.cycles_data)) {
                const cycles = record.cycles_data;
                if (cycles.length > 0) {
                    cyclesDisplay = cycles.length;
                    // Calculate total duration
                    let totalDuration = 0;
                    cycles.forEach(cycle => {
                        if (cycle.time_in && cycle.time_out) {
                            const timeIn = new Date(cycle.time_in).getTime();
                            const timeOut = new Date(cycle.time_out).getTime();
                            totalDuration += (timeOut - timeIn);
                        }
                    });
                    if (totalDuration > 0) {
                        const hours = Math.floor(totalDuration / 3600000);
                        const minutes = Math.floor((totalDuration % 3600000) / 60000);
                        durationDisplay = `${hours}h ${minutes}m`;
                    }
                }
            }

            let row = `
                <tr class="hover:bg-surface-container transition-colors border-b border-outline-variant/20">
                    <td class="px-8 py-4 text-sm font-semibold text-on-surface">${record.student_name}</td>
                    <td class="px-8 py-4 text-sm text-on-surface-variant">${record.student_id}</td>
                    <td class="px-8 py-4 text-sm text-on-surface-variant">${record.student_program || '—'}</td>
                    <td class="px-8 py-4 text-sm text-on-surface-variant">${record.student_rfid || '—'}</td>
                    <td class="px-8 py-4">${statusBadge}</td>
                    <td class="px-8 py-4 text-sm text-on-surface-variant">Day ${record.day_num}, S${record.session_number}</td>
                    <td class="px-8 py-4 text-sm font-mono text-on-surface-variant">${timeDisplay}</td>
                    <td class="px-8 py-4 text-sm font-mono text-on-surface-variant">${timeOutDisplay}</td>`;
            
            if (requireActionPrompts) {
                row += `<td class="px-8 py-4 text-sm text-on-surface-variant">${durationDisplay}</td>`;
                row += `<td class="px-8 py-4 text-sm text-on-surface-variant">${cyclesDisplay}</td>`;
            }
            
            row += `</tr>`;
            return row;
        }).join('');

        // Update record count
        document.getElementById('attendance-record-count').textContent = filtered.length;
    }

    // Update camera button state based on active session
    function updateCameraButtonState() {
        if (!hasActiveSession) {
            toggleQRCameraBtn.disabled = true;
            toggleQRCameraBtn.classList.add('opacity-50', 'cursor-not-allowed');
            toggleQRCameraBtn.title = 'No active session. Please start a session first.';
            showQRStatus('⚠️ No active session. Please start a session first to use the camera.', 'warning');
        } else {
            toggleQRCameraBtn.disabled = false;
            toggleQRCameraBtn.classList.remove('opacity-50', 'cursor-not-allowed');
            toggleQRCameraBtn.title = '';
        }
        
        // Update footer session button text
        updateFooterSessionButton();
    }
    
    // Update footer session button based on active session status
    function updateFooterSessionButton() {
        const footerSessionBtn = document.getElementById('footerSessionBtn');
        if (!footerSessionBtn) return;
        
        // Check if all sessions are completed
        const allSessionsCompleted = allSessions.length > 0 && allSessions.every(session => session.status === 'completed');
        
        if (allSessionsCompleted) {
            // Show "Event Finished" with disabled state
            footerSessionBtn.textContent = 'Event Finished';
            footerSessionBtn.disabled = true;
            footerSessionBtn.classList.remove('bg-primary', 'text-white', 'bg-error', 'text-on-error', 'hover:brightness-95', 'cursor-pointer');
            footerSessionBtn.classList.add('bg-gray-400', 'text-gray-700', 'opacity-50', 'cursor-not-allowed');
        } else if (hasActiveSession) {
            footerSessionBtn.textContent = 'End Event Session';
            footerSessionBtn.disabled = false;
            footerSessionBtn.classList.remove('bg-primary', 'text-white', 'bg-gray-400', 'text-gray-700', 'opacity-50', 'cursor-not-allowed');
            footerSessionBtn.classList.add('bg-error', 'text-white', 'hover:brightness-95', 'cursor-pointer');
        } else {
            footerSessionBtn.textContent = 'Start Event Session';
            footerSessionBtn.disabled = false;
            footerSessionBtn.classList.remove('bg-error', 'text-on-error', 'bg-gray-400', 'text-gray-700', 'opacity-50', 'cursor-not-allowed');
            footerSessionBtn.classList.add('bg-primary', 'text-white', 'hover:brightness-95', 'cursor-pointer');
        }
    }

    // Start QR Camera
    function startQRCamera() {
        if (!hasActiveSession) {
            showQRStatus('⚠️ No active session. Please start a session first to use the camera.', 'warning');
            return;
        }
        
        if (qrCameraActive) return;

        if (!qrScanner) {
            initializeQRScanner();
        }

        // Check if we're on HTTPS or localhost
        const isSecure = window.location.protocol === 'https:' || window.location.hostname === 'localhost' || window.location.hostname === '127.0.0.1';
        
        if (!isSecure) {
            showQRStatus('⚠️ Camera access requires HTTPS. Please use https:// or access from localhost.', 'error');
            return;
        }

        Html5Qrcode.getCameras()
            .then(devices => {
                if (!devices || devices.length === 0) {
                    showQRStatus('⚠️ No camera devices found. 1) Check if camera is connected 2) Refresh page and grant permission 3) On mobile, ensure app has camera permission in system settings', 'error');
                    return;
                }
                
                const cameraId = devices[0].id;
                
                qrScanner.start(
                    cameraId,
                    {
                        fps: 10,
                        qrbox: { width: 250, height: 250 }
                    },
                    onQRScanSuccess,
                    onQRScanError
                ).then(() => {
                    qrCameraActive = true;
                    toggleQRCameraBtn.classList.remove('bg-primary', 'hover:bg-blue-700');
                    toggleQRCameraBtn.classList.add('bg-error', 'hover:bg-red-700');
                    qrCameraButtonText.textContent = 'Stop Camera';
                    showQRStatus('Camera started - Ready to scan QR codes', 'info');
                }).catch(err => {
                    console.error('Camera start error:', err);
                    showQRStatus('❌ Failed to start camera. Refresh page and ensure camera permission is granted.', 'error');
                });
            })
            .catch(err => {
                console.error('Get cameras error:', err);
                const isPermissionError = err.message.includes('permission') || err.message.includes('Permission');
                
                if (isPermissionError) {
                    showQRStatus('❌ Camera permission denied. Please: 1) Refresh page 2) Grant camera permission when prompted 3) Check browser settings', 'error');
                } else if (!isSecure) {
                    showQRStatus('⚠️ Camera access requires HTTPS. Use https:// or localhost to access camera.', 'error');
                } else {
                    showQRStatus('❌ Unable to access camera. Try: 1) Refresh page 2) Check camera is not in use 3) Restart browser 4) On mobile, check system permissions', 'error');
                }
            });
    }

    // Stop QR Camera
    function stopQRCamera() {
        if (!qrCameraActive) return;

        qrScanner.stop()
            .then(() => {
                qrCameraActive = false;
                toggleQRCameraBtn.classList.remove('bg-error', 'hover:bg-red-700');
                toggleQRCameraBtn.classList.add('bg-primary', 'hover:bg-blue-700');
                qrCameraButtonText.textContent = 'Start Camera';
                showQRStatus('Camera stopped', 'info');
            })
            .catch(err => {
            });
    }

    // Initialize Barcode Scanner (Input-based)
    function initializeBarcodeScanner() {
        barcodeInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                if (!hasActiveSession) {
                    showBarcodeStatus('⚠️ No active session. Please start a session first to scan.', 'warning');
                    barcodeInput.value = '';
                    return;
                }
                const barcode = barcodeInput.value.trim();
                if (barcode) {
                    onBarcodeScanSuccess(barcode);
                    barcodeInput.value = '';
                }
            }
        });
    }

    // Start Barcode Scanner (focuses input)
    function startBarcodeCamera() {
        barcodeInput.focus();
        showBarcodeStatus('Ready to scan - focus on input field', 'info');
    }

    // Stop Barcode Scanner
    function stopBarcodeCamera() {
        barcodeInput.value = '';
        barcodeInput.blur();
    }

    // QR Scan Success
    function onQRScanSuccess(decodedText) {
        processScannedStudent(decodedText, 'qr');
    }

    // QR Scan Error
    function onQRScanError(error) {
        // Silently ignore - just means no QR code found yet
    }

    // Barcode Scan Success
    function onBarcodeScanSuccess(decodedText) {
        processScannedStudent(decodedText, 'barcode');
    }

    // Process scanned student (common for both QR and barcode)
    function processScannedStudent(studentId, scanType) {
        const cleanId = studentId.trim();
        if (scanType === 'barcode') {
        }
        
        // Find student's current attendance record to check status
        const studentRecord = attendanceData.find(a => a.student_id === cleanId);
        const currentStatus = studentRecord ? studentRecord.status : null;
        
        // Don't allow marking students who haven't timed in yet in break or timeout modes
        if (currentScanMode === 'break' && currentStatus !== 'present' && currentStatus !== 'left_session') {
            const message = '⚠️ Student has not timed in yet and cannot be marked on break.';
            if (scanType === 'qr') {
                showQRStatus(message, 'warning');
            } else {
                showBarcodeStatus(message, 'warning');
            }
            return;
        }

        if (currentScanMode === 'timeout' && currentStatus !== 'present' && currentStatus !== 'left_session') {
            const message = '⚠️ Student has not timed in yet and cannot be timed out.';
            if (scanType === 'qr') {
                showQRStatus(message, 'warning');
            } else {
                showBarcodeStatus(message, 'warning');
            }
            return;
        }
        
        // Check cooldown for this student
        if (isStudentInCooldown(cleanId)) {
            // Allow bypass if in Break Mode and student is currently on break (return them to present)
            const allowBypass = (currentScanMode === 'break' && currentStatus === 'left_session');
            
            if (!allowBypass) {
                const secondsRemaining = getTimeUntilCooldownEnds(cleanId);
                const lastScanMode = lastScannedStudents[cleanId]?.mode;
                let message = `⏳ Please wait ${secondsRemaining}s before scanning this student again`;
                
                // Only show mode-specific cooldown messages when the last scan mode also matches the current action
                if (currentScanMode === 'break' && currentStatus === 'left_session' && lastScanMode === 'break') {
                    message = `☕ Student already marked on break. Wait ${secondsRemaining}s before scanning again.`;
                } else if (currentScanMode === 'timeout' && currentStatus === 'absent' && lastScanMode === 'timeout') {
                    message = `🚪 Student already timed out. Wait ${secondsRemaining}s before scanning again.`;
                }
                
                if (scanType === 'qr') {
                    showQRStatus(message, 'warning');
                } else {
                    showBarcodeStatus(message, 'warning');
                }
                return;
            }
        }
        
        // Record this scan immediately to prevent double scans
        recordStudentScan(cleanId);
        
        // Try to find student by ID or RFID in local data
        let matchedRecord = attendanceData.find(a => a.student_id === cleanId);
        let actualStudentId = cleanId;
        let isRFIDMatch = false;
        
        // If not found by ID, try RFID
        if (!matchedRecord) {
            matchedRecord = attendanceData.find(a => a.student_rfid === cleanId);
            if (matchedRecord) {
                actualStudentId = matchedRecord.student_id;
                isRFIDMatch = true;
            }
        }

        // Fetch student details from the backend
        // If we matched by RFID, use the student ID; otherwise use the scanned value
        const fetchId = isRFIDMatch ? actualStudentId : cleanId;
        fetch(`/api/student/${fetchId}`)
            .then(response => {
                return response.text().then(text => {
                    let jsonData = null;
                    try {
                        jsonData = text ? JSON.parse(text) : {};
                    } catch (e) {
                        jsonData = {};
                    }
                    return {
                        status: response.status,
                        ok: response.ok,
                        data: jsonData
                    };
                });
            })
            .then(result => {
                // If fetch failed, try RFID lookup as fallback
                if (!result.ok) {
                    return fetch(`/api/student/lookup-by-rfid/${cleanId}`)
                        .then(response => {
                            return response.text().then(text => {
                                let jsonData = null;
                                try {
                                    jsonData = text ? JSON.parse(text) : {};
                                } catch (e) {
                                    jsonData = {};
                                }
                                return {
                                    status: response.status,
                                    ok: response.ok,
                                    data: jsonData
                                };
                            });
                        });
                }
                return result;
            })
            .then(result => {
                // Check if student was found (status 200)
                if (!result.ok) {
                    // Student doesn't exist (404)
                    if (scanType === 'qr') {
                        showQRStatus('Student not found. Opening add dialog...', 'info');
                    } else {
                        showBarcodeStatus('Student not found. Opening add dialog...', 'info');
                    }
                    showAddStudentModal(cleanId, scanType);
                    return Promise.reject('Student not found - add dialog opened');
                } else if (result.data.success && result.data.student) {
                    // Student found - check if registered for this event
                    const student = result.data.student;
                    const isRegisteredForEvent = attendanceData.some(a => a.student_id === student.snumber);
                    
                    if (!isRegisteredForEvent) {
                        // Student exists but not registered for this event
                        if (scanType === 'qr') {
                            showQRStatus('Confirming student...', 'info');
                        } else {
                            showBarcodeStatus('Confirming student...', 'info');
                        }
                        showConfirmIncludeStudentModal(student, scanType);
                        return Promise.reject('Confirmation modal shown');
                    } else {
                        // Student is registered
                        return student;
                    }
                } else {
                    // Unexpected response
                    if (scanType === 'qr') {
                        showQRStatus('Student not found. Opening add dialog...', 'info');
                    } else {
                        showBarcodeStatus('Student not found. Opening add dialog...', 'info');
                    }
                    showAddStudentModal(cleanId, scanType);
                    return Promise.reject('Student not found - add dialog opened');
                }
            })
            .then(student => {
                // If attendance was successfully marked
                if (student) {
                    addScannedStudent(student);
                    
                    // Handle based on scan mode
                    if (currentScanMode === 'normal') {
                        // Mark as present
                        markAttendanceInDatabase(eventId, student.snumber, student, scanType)
                            .then(() => {
                                if (scanType === 'qr') {
                                    qrLastScannedText.textContent = `${student.name} (${student.snumber})`;
                                    showQRStatus(`Attendance marked for ${student.name}`, 'success');
                                } else {
                                    barcodeLastScannedText.textContent = `${student.name} (${student.snumber})`;
                                    showBarcodeStatus(`Attendance marked for ${student.name}`, 'success');
                                }
                            })
                            .catch(err => {
                                // Handle error
                                const errorMsg = err.message || 'Failed to mark attendance';
                                if (scanType === 'qr') {
                                    showQRStatus(errorMsg, 'error');
                                } else {
                                    showBarcodeStatus(errorMsg, 'error');
                                }
                            });
                    } else {
                        // Handle break or timeout actions
                        const studentAttendance = attendanceData.find(a => a.student_id === student.snumber);
                        if (studentAttendance) {
                            let action;
                            let statusMessage;
                            
                            if (currentScanMode === 'break') {
                                action = currentStatus === 'left_session' ? 'return_from_break' : 'break';
                                statusMessage = currentStatus === 'left_session' ? 
                                    `Student returned from break: ${student.name}` : 
                                    `Student marked on break: ${student.name}`;
                            } else if (currentScanMode === 'timeout') {
                                action = 'time_out';
                                statusMessage = `Student timed out: ${student.name}`;
                            }
                            
                            processAttendanceAction(student, studentAttendance.id, action, scanType)
                                .then(() => {
                                    if (scanType === 'qr') {
                                        qrLastScannedText.textContent = `${student.name} (${student.snumber})`;
                                        showQRStatus(statusMessage, 'success');
                                    } else {
                                        barcodeLastScannedText.textContent = `${student.name} (${student.snumber})`;
                                        showBarcodeStatus(statusMessage, 'success');
                                    }
                                })
                                .catch(err => {
                                    const errorMsg = err.message || 'Failed to process action';
                                    if (scanType === 'qr') {
                                        showQRStatus(errorMsg, 'error');
                                    } else {
                                        showBarcodeStatus(errorMsg, 'error');
                                    }
                                });
                        }
                    }
                    
                    // Vibrate if available
                    if (navigator.vibrate) {
                        navigator.vibrate(200);
                    }
                    // Do NOT updateAttendanceRecord here; table will update after loadAttendanceData()
                }
            })
            .catch(err => {
                // Normalize error text so string rejections are handled safely
                const errorText = err && err.message ? err.message : String(err || '');

                // These are expected errors - don't show error message
                const knownErrors = ['Action modal shown', 'Cooldown active', 'Student not found - add dialog opened', 'Confirmation modal shown'];
                if (knownErrors.some(msg => errorText === msg || errorText.startsWith(msg))) {
                    return;
                }
                
                // Extract the actual error message if it came from backend
                let displayMessage = 'Failed to process scan';
                if (errorText.includes('|BACKEND_ERROR')) {
                    displayMessage = errorText.split('|BACKEND_ERROR')[0];
                } else if (errorText) {
                    displayMessage = errorText;
                }
                
                // Display specific error message
                const icon = displayMessage.includes('already') || displayMessage.includes('cannot') ? '⚠️' : '❌';
                if (scanType === 'qr') {
                    showQRStatus(`${icon} ${displayMessage}`, 'warning');
                } else {
                    showBarcodeStatus(`${icon} ${displayMessage}`, 'warning');
                }
            });
    }

    // Show confirmation modal for including existing student in event
    function showConfirmIncludeStudentModal(student, scanType) {
        // Remove existing modal if any
        const existingModal = document.getElementById('confirmIncludeStudentModal');
        if (existingModal) {
            existingModal.remove();
        }

        // Create modal
        const modal = document.createElement('div');
        modal.id = 'confirmIncludeStudentModal';
        modal.className = 'fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50';
        modal.innerHTML = `
            <div class="bg-surface-container-lowest rounded-xl shadow-lg p-8 max-w-md w-full mx-4">
                <h2 class="text-lg font-bold text-on-surface mb-2">Include Student in Event?</h2>
                <div class="bg-surface rounded-lg p-4 mb-6 border border-outline-variant">
                    <p class="text-sm text-on-surface-variant mb-3">This student exists in another event.</p>
                    <div class="space-y-2">
                        <p class="text-sm font-semibold text-on-surface">Student: <span class="text-primary">${student.name}</span></p>
                        <p class="text-sm font-semibold text-on-surface">ID: <span class="text-primary">${student.snumber}</span></p>
                        <p class="text-sm font-semibold text-on-surface">Section: <span class="text-primary">${student.section || 'N/A'}</span></p>
                    </div>
                </div>
                <p class="text-sm text-on-surface-variant mb-6">Do you want to include this student in the attendance list for this event?</p>
                <div class="flex gap-3">
                    <button type="button" class="flex-1 px-4 py-2 border border-outline-variant text-on-surface rounded-lg font-semibold hover:bg-surface transition-colors" onclick="document.getElementById('confirmIncludeStudentModal').remove()">
                        No, Skip
                    </button>
                    <button type="button" class="flex-1 px-4 py-2 bg-primary hover:bg-blue-700 text-on-primary rounded-lg font-semibold transition-colors" id="confirmIncludeBtn">
                        Yes, Include
                    </button>
                </div>
            </div>
        `;

        document.body.appendChild(modal);

        // Close modal on outside click
        modal.addEventListener('click', function(e) {
            if (e.target === modal) {
                modal.remove();
            }
        });

        // Handle YES button
        const confirmBtn = document.getElementById('confirmIncludeBtn');
        confirmBtn.addEventListener('click', async function() {
            confirmBtn.disabled = true;
            confirmBtn.innerHTML = '...';

            try {
                // Register the student to this event
                const response = await fetch(`/api/event/${eventId}/register-student`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        snumber: student.snumber
                    })
                });

                const data = await response.json();

                if (data.success) {
                    // Close modal
                    modal.remove();
                    
                    // Add student to attendance data so table updates
                    attendanceData.push({
                        student_id: student.snumber,
                        student_name: student.name,
                        session_id: null,
                        time_in: null,
                        time_out: null,
                        day_num: null,
                        session_number: null
                    });

                    // Now mark attendance
                    const markResult = await markAttendanceInDatabase(eventId, student.snumber, student);

                    if (scanType === 'qr') {
                        qrLastScannedText.textContent = `${student.name} (${student.snumber})`;
                        showQRStatus(`${student.name} added and attendance marked!`, 'success');
                    } else {
                        barcodeLastScannedText.textContent = `${student.name} (${student.snumber})`;
                        showBarcodeStatus(`${student.name} added and attendance marked!`, 'success');
                    }

                    // Update the scanned list and table
                    addScannedStudent(student);
                    loadAttendanceData(); // Reload to get all correct session info
                    
                    if (navigator.vibrate) {
                        navigator.vibrate(200);
                    }
                } else {
                    let errorMsg = data.message || 'Failed to register student';
                    if (data.errors) {
                        errorMsg = Object.values(data.errors).flat().join('\n');
                    }
                    alert('Error: ' + errorMsg);
                    confirmBtn.disabled = false;
                    confirmBtn.textContent = 'Yes, Include';
                }
            } catch (error) {
                alert('Error registering student: ' + error.message);
                confirmBtn.disabled = false;
                confirmBtn.textContent = 'Yes, Include';
            }
        });
    }

    // Mark attendance in the database
    function markAttendanceInDatabase(eventId, studentId, student, scanType) {
        return fetch(`/event/${eventId}/mark-attendance-present`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({
                student_id: studentId,
                rfid: student.rfid || null
            })
        })
            .then(response => {
                return response.text().then(text => {
                    let jsonData = null;
                    try {
                        jsonData = text ? JSON.parse(text) : {};
                    } catch (e) {
                        jsonData = {};
                    }
                    return {
                        status: response.status,
                        ok: response.ok,
                        data: jsonData
                    };
                });
            })
            .then(async result => {
                // Handle cooldown active (too many scans)
                if (result.data.cooldown_active) {
                    const remaining = result.data.cooldown_remaining || 0;
                    const message = `⚠️ Scan too fast! Wait ${remaining}s before scanning again.`;
                    if (scanType === 'qr') {
                        showQRStatus(message, 'warning');
                    } else {
                        showBarcodeStatus(message, 'warning');
                    }
                    throw new Error('Cooldown active');
                }
                
                // Handle already timed out error - show and return null (don't throw)
                if (result.status === 422 && result.data.message && result.data.message.includes('already timed out')) {
                    const message = `⚠️ ${result.data.message}`;
                    if (scanType === 'qr') {
                        showQRStatus(message, 'warning');
                    } else {
                        showBarcodeStatus(message, 'warning');
                    }
                    return null; // Exit gracefully without throwing
                }
                
                // Handle auto timed out (second scan with toggle OFF)
                if (result.data.auto_timed_out) {
                    if (scanType === 'qr') {
                        showQRStatus(result.data.message, 'success');
                    } else {
                        showBarcodeStatus(result.data.message, 'success');
                    }
                    loadAttendanceData();
                    return student;
                }
                
                // Handle show modal (student can choose action) - ONLY if setting enabled
                if (result.data.show_modal) {
                    if (requireActionPrompts) {
                        // Check scan mode - if not normal, skip modal and auto-process
                        if (currentScanMode === 'break') {
                            // Auto-process based on current status
                            const action = result.data.attendance.status === 'left_session' ? 'return_from_break' : 'break';
                            await processAttendanceAction(student, result.data.attendance.attendance_id, action, scanType);
                            return student;
                        } else if (currentScanMode === 'timeout') {
                            // Auto-process as timeout only if not already absent
                            if (result.data.attendance.status !== 'absent') {
                                await processAttendanceAction(student, result.data.attendance.attendance_id, 'time_out', scanType);
                                return student;
                            } else {
                                // Already timed out, show message
                                const message = '🚪 Student is already timed out.';
                                if (scanType === 'qr') {
                                    showQRStatus(message, 'warning');
                                } else {
                                    showBarcodeStatus(message, 'warning');
                                }
                                return null;
                            }
                        } else {
                            // Normal mode - show modal for manual action selection
                            showAttendanceActionModal(student, result.data.attendance.attendance_id, scanType, result.data.attendance.status);
                            throw new Error('Action modal shown');
                        }
                    } else {
                        // Auto timeout without showing modal
                        await processAttendanceAction(student, result.data.attendance.attendance_id, 'time_out', scanType);
                        return student; // Return after auto-timeout
                    }
                }
                // Handle normal success (first time_in)
                if (result.data.success && !result.data.show_modal) {
                    // Instead of just updating local record, reload from backend for accurate status/duration
                    loadAttendanceData();
                    return student;
                }
                // Any other error
                throw new Error(result.data.message || 'Failed to mark attendance');
            })
            .catch(err => {
                if (err.message === 'Cooldown active' || err.message === 'Action modal shown') {
                    // Expected errors - re-throw silently
                    throw err;
                }
                
                // For any other error, include the message
                throw new Error(err.message + '|BACKEND_ERROR');
            });
    }

    // Show action modal for taking break or timing out
    function showAttendanceActionModal(student, attendanceId, scanType, status) {
        // Remove existing modal if any
        const existingModal = document.getElementById('attendanceActionModal');
        if (existingModal) {
            existingModal.remove();
        }

        // Determine button labels and actions based on current status
        const isOnBreak = status === 'left_session';
        const breakButtonLabel = isOnBreak ? 'Time In' : 'Taking a Break';
        const breakButtonIcon = isOnBreak ? 'log-in' : 'coffee';
        const breakButtonColor = isOnBreak ? 'bg-green-50 hover:bg-green-100 border-green-300' : 'bg-yellow-50 hover:bg-yellow-100 border-yellow-300';
        const breakAction = isOnBreak ? 'return_from_break' : 'break';

        // Create modal
        const modal = document.createElement('div');
        modal.id = 'attendanceActionModal';
        modal.className = 'fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50';
        modal.innerHTML = `
            <div class="bg-white rounded-xl shadow-lg p-8 max-w-md w-full mx-4">
                <div class="text-center mb-6">
                    <p class="text-sm text-gray-600 mb-1">Next action for:</p>
                    <h2 class="text-xl font-bold text-gray-900">${student.name}</h2>
                    <p class="text-xs text-gray-500 mt-1">${student.snumber}</p>
                    <p class="text-xs text-gray-400 mt-2">Current: <span class="font-semibold">${isOnBreak ? 'On Break' : 'Present'}</span></p>
                </div>
                
                <div class="space-y-3">
                    <button type="button" class="w-full px-4 py-3 ${breakButtonColor} border-2 text-gray-900 rounded-lg font-semibold transition-colors flex items-center justify-center gap-2" id="breakBtn">
                        <i class="w-5 h-5" data-lucide="${breakButtonIcon}"></i>
                        <span>${breakButtonLabel}</span>
                    </button>
                    <button type="button" class="w-full px-4 py-3 bg-red-50 hover:bg-red-100 border-2 border-red-300 text-gray-900 rounded-lg font-semibold transition-colors flex items-center justify-center gap-2" id="timeoutBtn">
                        <i class="w-5 h-5" data-lucide="log-out"></i>
                        <span>Time Out</span>
                    </button>
                </div>
                
                <button type="button" class="w-full mt-4 px-4 py-2 border border-gray-300 text-gray-700 rounded-lg font-semibold hover:bg-gray-50 transition-colors" onclick="document.getElementById('attendanceActionModal').remove()">
                    Cancel
                </button>
            </div>
        `;

        document.body.appendChild(modal);
        
        // Initialize Lucide icons in modal
        if (typeof lucide !== 'undefined') {
            lucide.createIcons();
        }

        // Close modal on outside click
        modal.addEventListener('click', function(e) {
            if (e.target === modal) {
                modal.remove();
            }
        });

        // Handle break/return action
        const breakBtn = document.getElementById('breakBtn');
        breakBtn.addEventListener('click', async function() {
            await processAttendanceAction(student, attendanceId, breakAction, scanType);
            modal.remove();
        });

        // Handle time out action
        const timeoutBtn = document.getElementById('timeoutBtn');
        timeoutBtn.addEventListener('click', async function() {
            await processAttendanceAction(student, attendanceId, 'time_out', scanType);
            modal.remove();
        });
    }

    // Process attendance action (break, time_out, return_from_break)
    async function processAttendanceAction(student, attendanceId, action, scanType) {
        try {
            const response = await fetch(`/event/${eventId}/process-attendance-action`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({
                    attendance_id: attendanceId,
                    action: action
                })
            });

            const data = await response.json();

            if (data.success) {
                let message = '';
                if (action === 'break') {
                    message = `${student.name} is taking a break`;
                    // Add mode indicator if in break mode
                    if (currentScanMode === 'break') {
                        message = `⚡ BREAK MODE: ${message}`;
                    }
                } else if (action === 'time_out') {
                    message = `${student.name} has timed out (${data.attendance.formatted_duration})`;
                    // Add mode indicator if in timeout mode
                    if (currentScanMode === 'timeout') {
                        message = `⚡ TIMEOUT MODE: ${message}`;
                    }
                } else if (action === 'return_from_break') {
                    message = `${student.name} returned from break`;
                }

                if (scanType === 'qr') {
                    showQRStatus(`✓ ${message}`, 'success');
                } else {
                    showBarcodeStatus(`✓ ${message}`, 'success');
                }
                
                // Vibrate
                if (navigator.vibrate) {
                    navigator.vibrate(200);
                }

                // Update the attendance table
                loadAttendanceData();
            } else {
                const errorMsg = data.message || 'Failed to process action';
                if (scanType === 'qr') {
                    showQRStatus(`Error: ${errorMsg}`, 'error');
                } else {
                    showBarcodeStatus(`Error: ${errorMsg}`, 'error');
                }
            }
        } catch (error) {
            const errorMsg = `Error processing action: ${error.message}`;
            if (scanType === 'qr') {
                showQRStatus(errorMsg, 'error');
            } else {
                showBarcodeStatus(errorMsg, 'error');
            }
        }
    }

    // Check if student already scanned
    function isStudentAlreadyScanned(studentId) {
        return scannedStudents.some(s => s.snumber === studentId);
    }

    // Add scanned student to list
    function addScannedStudent(student) {
        // Check if student already in list to avoid duplicates
        if (!scannedStudents.some(s => s.snumber === student.snumber)) {
            scannedStudents.push(student);
            saveScannedStudentsToStorage();
            renderScannedList();
        }
    }

    // Render scanned students list
    function renderScannedList() {
        if (scannedStudents.length === 0) {
            scannedList.innerHTML = `
                <div class="p-6 text-center text-gray-500">
                    <i class="w-8 h-8 mx-auto mb-2 opacity-50" data-lucide="inbox"></i>
                    <p class="text-sm">No scans yet</p>
                </div>
            `;
            scannedCount.textContent = '0';
            return;
        }

        scannedList.innerHTML = scannedStudents.map((student, index) => {
            // Find the student's attendance record for the current session
            const sessionId = scanSessionFilter?.value;
            const studentAttendance = attendanceData.find(a => 
                a.student_id === student.snumber && a.session_id == sessionId
            );
            
            // Determine badge based on student status and current scan mode
            let badge = '<span class="text-xs font-bold text-green-600 bg-green-100 px-2 py-1 rounded-full">✓ Present</span>';
            
            if (studentAttendance) {
                if (studentAttendance.status === 'left_session') {
                    badge = '<span class="text-xs font-bold text-yellow-600 bg-yellow-100 px-2 py-1 rounded-full">☕ On Break</span>';
                } else if (studentAttendance.status === 'absent') {
                    // Show timeout mode indicator if in timeout mode
                    if (currentScanMode === 'timeout') {
                        badge = '<span class="text-xs font-bold text-red-600 bg-red-100 px-2 py-1 rounded-full">⚡ 🚪 Timed Out</span>';
                    } else {
                        badge = '<span class="text-xs font-bold text-red-600 bg-red-100 px-2 py-1 rounded-full">✗ Timed Out</span>';
                    }
                } else if (studentAttendance.status === 'present') {
                    badge = '<span class="text-xs font-bold text-green-600 bg-green-100 px-2 py-1 rounded-full">✓ Present</span>';
                }
            }
            
            return `
                <div class="p-4 border-b border-gray-100 hover:bg-gray-50 transition-colors">
                    <div class="flex items-center justify-between">
                        <div class="flex-1">
                            <p class="font-semibold text-gray-900 text-sm">${student.name}</p>
                            <p class="text-xs text-gray-500">${student.snumber}</p>
                        </div>
                        ${badge}
                    </div>
                </div>
            `;
        }).join('');

        scannedCount.textContent = scannedStudents.length;
        if (typeof lucide !== 'undefined') {
            lucide.createIcons();
        }
    }

    // Show QR status message
    function showQRStatus(message, type) {
        qrScanStatus.classList.remove('hidden');
        qrStatusMessage.textContent = message;
        applyStatusStyling(qrScanStatus, type);
    }

    // Show Barcode status message
    function showBarcodeStatus(message, type) {
        barcodeScanStatus.classList.remove('hidden');
        barcodeStatusMessage.textContent = message;
        applyStatusStyling(barcodeScanStatus, type);
    }

    // Apply status styling
    function applyStatusStyling(statusEl, type) {
        statusEl.classList.remove('bg-red-50', 'text-red-700', 'bg-yellow-50', 'text-yellow-700', 'bg-blue-50', 'text-blue-700', 'bg-green-50', 'text-green-700');
        
        if (type === 'success') {
            statusEl.classList.add('bg-green-50', 'text-green-700');
        } else if (type === 'error') {
            statusEl.classList.add('bg-red-50', 'text-red-700');
        } else if (type === 'warning') {
            statusEl.classList.add('bg-yellow-50', 'text-yellow-700');
        } else {
            statusEl.classList.add('bg-blue-50', 'text-blue-700');
        }
    }

    // Show add student modal
    function showAddStudentModal(studentId, scanType) {
        // Remove existing modal if any
        const existingModal = document.getElementById('addStudentModal');
        if (existingModal) {
            existingModal.remove();
        }

        // Create modal
        const modal = document.createElement('div');
        modal.id = 'addStudentModal';
        modal.className = 'fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50';
        modal.innerHTML = `
            <div class="bg-white rounded-xl shadow-lg p-8 max-w-md w-full mx-4">
                <h2 class="text-lg font-bold text-gray-800 mb-2">Add New Student</h2>
                <p class="text-sm text-gray-600 mb-6">This student is not in the system. Would you like to add them?</p>
                <form id="addStudentForm" class="space-y-4">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Student ID</label>
                        <input type="text" id="modal_student_id" class="w-full px-4 py-2 border border-gray-300 rounded-lg bg-gray-50 font-semibold" value="${studentId}" disabled />
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Student Name *</label>
                        <input type="text" id="modal_student_name" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-sky-400" placeholder="Full name" required />
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Section *</label>
                        <input type="text" id="modal_student_section" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-sky-400" placeholder="Section" required />
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Program (Optional)</label>
                        <input type="text" id="modal_student_program" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-sky-400" placeholder="Program" />
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">RFID (Optional)</label>
                        <input type="text" id="modal_student_rfid" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-sky-400" placeholder="RFID tag" />
                    </div>
                    <div class="flex gap-3 pt-4">
                        <button type="button" class="flex-1 px-4 py-2 border border-gray-300 text-gray-700 rounded-lg font-semibold hover:bg-gray-50 transition-colors" onclick="document.getElementById('addStudentModal').remove()">
                            Cancel
                        </button>
                        <button type="submit" class="flex-1 px-4 py-2 bg-primary hover:brightness-95 text-white rounded-lg font-semibold transition-all">
                            Add & Mark Present
                        </button>
                    </div>
                </form>
            </div>
        `;

        document.body.appendChild(modal);

        // Close modal on outside click
        modal.addEventListener('click', function(e) {
            if (e.target === modal) {
                modal.remove();
            }
        });

        // Handle form submission
        const form = document.getElementById('addStudentForm');
        form.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const name = document.getElementById('modal_student_name').value.trim();
            const section = document.getElementById('modal_student_section').value.trim();
            const program = document.getElementById('modal_student_program').value.trim();
            const rfid = document.getElementById('modal_student_rfid').value.trim();
            
            if (!name || !section) {
                alert('Please fill in all required fields (Name, Section)');
                return;
            }

            // Disable submit button
            const submitBtn = e.target.querySelector('button[type="submit"]');
            submitBtn.disabled = true;
            submitBtn.innerHTML = '...';

            try {
                // Add student to database
                const response = await fetch('/api/student/create', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        snumber: studentId,
                        name: name,
                        section: section,
                        program: program || null,
                        rfid: rfid || null
                    })
                });

                const data = await response.json();
                
                if (data.success) {
                    // Register student to the event (creates attendance records for all sessions)
                    const registerResponse = await fetch(`/api/event/${eventId}/register-student`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: JSON.stringify({
                            snumber: data.student.snumber
                        })
                    });

                    const registerData = await registerResponse.json();

                    if (!registerData.success) {
                        throw new Error(registerData.message || 'Failed to register student to event');
                    }

                    // Close modal
                    modal.remove();
                    
                    // Now mark attendance for this student
                    await markAttendanceInDatabase(eventId, data.student.snumber, data.student);
                    
                    // Show success and update table
                    addScannedStudent(data.student);
                    if (scanType === 'qr') {
                        qrLastScannedText.textContent = `${data.student.name} (${data.student.snumber})`;
                        showQRStatus(`Student added and attendance marked for ${data.student.name}`, 'success');
                    } else {
                        barcodeLastScannedText.textContent = `${data.student.name} (${data.student.snumber})`;
                        showBarcodeStatus(`Student added and attendance marked for ${data.student.name}`, 'success');
                    }
                    
                    updateAttendanceRecord(data.student.snumber);
                    
                    if (navigator.vibrate) {
                        navigator.vibrate(200);
                    }
                } else {
                    // Show validation errors
                    let errorMsg = data.message || 'Failed to add student';
                    if (data.errors) {
                        errorMsg = Object.values(data.errors).flat().join('\n');
                    }
                    alert('Error: ' + errorMsg);
                    submitBtn.disabled = false;
                    submitBtn.textContent = 'Add & Mark Present';
                }
            } catch (error) {
                alert('Error adding student: ' + error.message);
                submitBtn.disabled = false;
                submitBtn.textContent = 'Add & Mark Present';
            }
        });

        // Focus on name input
        setTimeout(() => {
            const nameInput = document.getElementById('modal_student_name');
            if (nameInput) {
                nameInput.focus();
            }
        }, 100);
    }

    // Update attendance record in table
    function updateAttendanceRecord(studentId) {
        // Find the attendance record for this student
        const record = attendanceData.find(a => a.student_id === studentId);
        if (record && !record.time_in) {
            record.time_in = new Date().toISOString();
        }
        
        // Re-render the table
        renderAttendanceTable();
    }

    // Event listeners
    toggleQRCameraBtn.addEventListener('click', function() {
        if (qrCameraActive) {
            stopQRCamera();
        } else {
            startQRCamera();
        }
    });

    // Filter listeners
    if (scanSessionFilter) {
        scanSessionFilter.addEventListener('change', function() {
            renderAttendanceTable();
            // Re-check active session status when filter changes
            hasActiveSession = allSessions.some(s => s.status === 'active');
            updateCameraButtonState();
        });
    }
    if (scanSearch) {
        scanSearch.addEventListener('input', renderAttendanceTable);
    }
    
    // Footer session button listener
    const footerSessionBtn = document.getElementById('footerSessionBtn');
    if (footerSessionBtn) {
        footerSessionBtn.addEventListener('click', function() {
            if (hasActiveSession) {
                // End current session
                endCurrentSessionFromScan();
            } else {
                // Start next session
                startNextSessionFromScan();
            }
        });
    }

    // Check if event date hasn't come yet and disable start button
    function checkEventDateAndDisableStartButton() {
        const eventStartDateStr = document.querySelector('[data-event-start-date]')?.getAttribute('data-event-start-date');
        if (!eventStartDateStr) return;

        const eventStartDate = new Date(eventStartDateStr);
        const today = new Date();
        today.setHours(0, 0, 0, 0);
        eventStartDate.setHours(0, 0, 0, 0);

        if (today < eventStartDate && !hasActiveSession) {
            // Event date hasn't come yet and there's no active session, disable start button
            const btn = document.getElementById('footerSessionBtn');
            if (btn) {
                btn.disabled = true;
                btn.classList.add('opacity-50', 'cursor-not-allowed');
                btn.classList.remove('hover:brightness-95');
                btn.title = `This event starts on ${eventStartDate.toLocaleDateString()}. Cannot start sessions before the event date.`;
            }
        }
    }

    // Check event date on page load
    checkEventDateAndDisableStartButton();
    
    // End current session
    async function endCurrentSessionFromScan() {
        if (!confirm('Are you sure you want to end the current session?')) {
            return;
        }
        
        try {
            const response = await fetch(`/event/${eventId}/end-session`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            });
            
            const data = await response.json();
            if (data.success) {
                showQRStatus('Session ended successfully', 'success');
                // Reload page after short delay to show success message
                setTimeout(() => {
                    location.reload();
                }, 1000);
            } else {
                showQRStatus('Failed to end session: ' + data.message, 'error');
            }
        } catch (error) {
            showQRStatus('Error ending session: ' + error.message, 'error');
        }
    }
    
    // Start next session
    async function startNextSessionFromScan() {
        try {
            const response = await fetch(`/event/${eventId}/start-session`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({ event_id: eventId })
            });
            
            const data = await response.json();
            
            // Check if event date hasn't come yet
            if (response.status === 403) {
                showQRStatus('Error: ' + data.message, 'error');
                return;
            }
            
            if (data.success) {
                showQRStatus('Session started successfully', 'success');
                // Reset scanned students for new session
                scannedStudents = [];
                clearScannedStudentsStorage();
                // Reload page after short delay to show success message
                setTimeout(() => {
                    location.reload();
                }, 1000);
            } else {
                showQRStatus('Failed to start session: ' + data.message, 'error');
            }
        } catch (error) {
            showQRStatus('Error starting session: ' + error.message, 'error');
        }
    }

    // Initialize QR scanner on page load
    initializeQRScanner();
    
    // Initialize Barcode input-based scanner
    initializeBarcodeScanner();
    
    // Initialize Scan Mode listeners and load saved mode
    loadScanModeFromStorage();
    initializeScanModeListeners();
    
    // Restore scanned students from localStorage
    loadScannedStudentsFromStorage();
    
    // Load attendance data
    loadAttendanceData();
});
