<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <meta name="csrf-token" content="{{ csrf_token() }}"/>
    <title>Scan Attendance | STI Attendance</title>
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com" rel="preconnect" />
    <link crossorigin="" href="https://fonts.gstatic.com" rel="preconnect" />
    <link href="https://fonts.googleapis.com/css2?family=Work+Sans:wght@400;600;700;800;900&family=Inter:wght@400;500;600&display=swap" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet" />
    <!-- Tailwind CSS v3 CDN with plugins -->
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <!-- Tailwind Custom Configuration -->
    <script id="tailwind-config">
      tailwind.config = {
        darkMode: "class",
        theme: {
          extend: {
            colors: {
              "inverse-primary": "#acc7ff",
              "on-primary-fixed": "#001a40",
              "secondary-container": "#fcd400",
              "tertiary-container": "#38598c",
              "on-secondary-container": "#6e5c00",
              "on-primary": "#ffffff",
              "inverse-surface": "#2f3132",
              "surface-container-high": "#e8e8ea",
              "on-tertiary-container": "#b8d0ff",
              "surface": "#f9f9fb",
              "inverse-on-surface": "#f0f0f2",
              "tertiary": "#1e4173",
              "outline": "#727784",
              "background": "#f9f9fb",
              "surface-variant": "#e2e2e4",
              "surface-container": "#eeeef0",
              "on-tertiary": "#ffffff",
              "secondary": "#705d00",
              "surface-container-highest": "#e2e2e4",
              "on-error-container": "#93000a",
              "surface-tint": "#115cb9",
              "surface-bright": "#f9f9fb",
              "on-tertiary-fixed": "#001b3d",
              "error-container": "#ffdad6",
              "surface-container-low": "#f3f3f5",
              "surface-dim": "#d9dadc",
              "on-primary-fixed-variant": "#004491",
              "on-surface": "#1a1c1d",
              "error": "#ba1a1a",
              "on-secondary-fixed-variant": "#544600",
              "secondary-fixed-dim": "#e9c400",
              "tertiary-fixed-dim": "#a9c7ff",
              "surface-container-lowest": "#ffffff",
              "on-tertiary-fixed-variant": "#244779",
              "on-background": "#1a1c1d",
              "outline-variant": "#c2c6d4",
              "secondary-fixed": "#ffe16d",
              "on-secondary-fixed": "#221b00",
              "on-primary-container": "#bbd0ff",
              "primary-container": "#cce4ff",
              "on-surface-variant": "#46464f",
              "on-primary": "#ffffff",
              "scrim": "#000000",
              "primary": "#115cb9"
            }
          }
        }
      };
    </script>
    <!-- HTML5 QR Code Scanner Library -->
    <script src="https://unpkg.com/html5-qrcode"></script>
    <!-- Lucide Icons Library -->
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        .material-symbols-outlined {
            font-family: 'Material Symbols Outlined';
            font-weight: normal;
            font-style: normal;
            font-size: 24px;
            display: inline-block;
            line-height: 1;
            text-transform: none;
            letter-spacing: normal;
            word-wrap: normal;
            white-space: nowrap;
            direction: ltr;
            font-feature-settings: 'liga' 1;
            font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0;
        }
        .material-symbols-outlined.filled {
            font-variation-settings: 'FILL' 1;
        }
        
        /* Scan Mode Styles */
        .scan-mode-btn {
            transition: all 0.2s ease;
        }
        .scan-mode-btn.active {
            box-shadow: 0 0 0 3px rgba(17, 92, 185, 0.2);
            transform: scale(1.05);
        }
        .scan-mode-normal.active {
            background-color: #cce4ff;
            border-color: #115cb9;
        }
        .scan-mode-break.active {
            background-color: #fef3c7;
            border-color: #f59e0b;
        }
        .scan-mode-timeout.active {
            background-color: #fee2e2;
            border-color: #ef4444;
        }
    </style>
</head>
<body class="bg-surface flex flex-col min-h-screen" data-event-id="{{ $event->e_id }}" data-require-action-prompts="{{ $event->require_action_prompts }}">
    <!-- ====== Universal Header ====== -->
    <x-header />

    <!-- ====== Main Content Area ====== -->
    <main class="flex-1 flex flex-col overflow-y-auto h-[90vh]" data-event-start-date="{{ $event->start_date }}">
      <!-- Scrollable Body Content -->
      <div class="flex-1 flex justify-center">
        <div class="w-full max-w-7xl px-8 py-8 space-y-5">
                <!-- Event Header -->
                <div class="bg-surface-container-lowest rounded-xl shadow-sm border border-outline-variant p-6">
                    <div class="flex justify-between items-start mb-4">
                        <div>
                            <h1 class="text-2xl font-bold text-on-surface mb-2">{{ $event->e_name }}</h1>
                            <p class="text-on-surface-variant">Scan student barcodes or QR codes to mark attendance</p>
                        </div>
                        <div id="managedBySection" class="flex items-center gap-2 bg-primary/10 px-4 py-3 rounded-lg">
                            <span class="material-symbols-outlined text-base text-primary">person</span>
                            <div class="text-right">
                                <p class="text-xs text-on-surface-variant font-medium">Managed By</p>
                                <p id="managerName" class="text-sm font-semibold text-primary">{{ $event->user->name ?? 'No Session Started' }}</p>
                                <p id="managerEmail" class="text-xs text-on-surface-variant">{{ $event->user->email ?? '' }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Scanner Section -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                    <!-- Camera and Controls -->
                    <div class="lg:col-span-2">
                        <div class="bg-surface-container-lowest rounded-xl shadow-sm border border-outline-variant overflow-hidden flex flex-col">
                            <!-- Tab Navigation -->
                            <div class="flex border-b border-outline-variant">
                                <button id="qrTabBtn" class="flex-1 px-6 py-4 text-center font-semibold text-on-surface border-b-2 border-primary transition-colors" data-tab="qr">
                                    <span class="material-symbols-outlined inline-block mr-2">qr_code_2</span>
                                    QR Code Scanner
                                </button>
                                <button id="barcodeTabBtn" class="flex-1 px-6 py-4 text-center font-semibold text-on-surface-variant border-b-2 border-transparent hover:text-on-surface transition-colors" data-tab="barcode">
                                    <span class="material-symbols-outlined inline-block mr-2">barcode</span>
                                    Barcode Scanner
                                </button>
                            </div>

                            <!-- QR Tab Content -->
                            <div id="qrTab" class="flex flex-col">
                                <!-- Camera Feed -->
                                <div class="relative bg-on-surface aspect-video flex items-center justify-center overflow-hidden">
                                    <div id="qrReader" class="w-full h-full" style="display: flex; align-items: center; justify-content: center;"></div>
                                </div>

                                <!-- Scan Mode Selector -->
                                <div id="qrScanModeSelector" class="p-4 bg-surface rounded-lg border border-outline-variant" style="display: none;">
                                    <p class="text-xs uppercase text-on-surface-variant font-bold mb-3">Scan Mode</p>
                                    <div class="grid grid-cols-3 gap-2">
                                        <button type="button" class="scan-mode-btn scan-mode-normal active px-3 py-2 border-2 border-outline-variant rounded-lg text-xs font-semibold text-on-surface hover:bg-surface-container transition-colors" data-mode="normal" title="Mark present on first scan, show modal on second">
                                            <span class="material-symbols-outlined text-base">touch_app</span>
                                            <div>Normal</div>
                                        </button>
                                        <button type="button" class="scan-mode-btn scan-mode-break px-3 py-2 border-2 border-outline-variant rounded-lg text-xs font-semibold text-on-surface hover:bg-surface-container transition-colors" data-mode="break" title="Directly mark break without modal">
                                            <span class="material-symbols-outlined text-base">coffee</span>
                                            <div>Break</div>
                                        </button>
                                        <button type="button" class="scan-mode-btn scan-mode-timeout px-3 py-2 border-2 border-outline-variant rounded-lg text-xs font-semibold text-on-surface hover:bg-surface-container transition-colors" data-mode="timeout" title="Directly timeout without modal">
                                            <span class="material-symbols-outlined text-base">logout</span>
                                            <div>Timeout</div>
                                        </button>
                                    </div>
                                </div>

                                <!-- Controls -->
                                <div class="p-6 space-y-4 border-t border-outline-variant">
                                    <div class="flex gap-3">
                                        <button id="toggleQRCameraBtn" class="flex-1 flex items-center justify-center gap-2 px-4 py-3 bg-primary hover:bg-blue-700 text-on-primary rounded-lg font-semibold transition-colors">
                                            <span class="material-symbols-outlined">camera</span>
                                            <span id="qrCameraButtonText">Start Camera</span>
                                        </button>
                                    </div>

                                    <!-- Last Scanned -->
                                    <div class="p-4 bg-surface rounded-lg border border-outline-variant">
                                        <p class="text-xs uppercase text-on-surface-variant font-bold mb-2">Last Scanned</p>
                                        <p id="qrLastScannedText" class="text-sm font-semibold text-on-surface">Waiting for scan...</p>
                                    </div>

                                    <!-- Scan Status -->
                                    <div id="qrScanStatus" class="hidden p-4 rounded-lg text-sm font-semibold">
                                        <div id="qrStatusMessage"></div>
                                    </div>
                                </div>
                            </div>

                            <!-- Barcode Tab Content -->
                            <div id="barcodeTab" class="hidden flex flex-col">
                                <!-- Barcode Input Area -->
                                <div class="p-8 bg-surface flex flex-col items-center justify-center min-h-96 border-b border-outline-variant">
                                    <span class="material-symbols-outlined text-6xl text-on-surface-variant mb-4">barcode</span>
                                    <h3 class="text-lg font-semibold text-on-surface mb-2">Barcode Scanner</h3>
                                    <p class="text-sm text-on-surface-variant mb-6 text-center max-w-xs">
                                        Place barcode scanner focus on the input field below and scan the student barcode or RFID
                                    </p>
                                    <input 
                                        type="text" 
                                        id="barcodeInput" 
                                        class="w-full max-w-sm px-4 py-3 border-2 border-primary rounded-lg text-center text-lg font-semibold focus:outline-none focus:ring-2 focus:ring-primary/40"
                                        placeholder="Scan ID or RFID here..."
                                        autocomplete="off"
                                    />
                                    <p class="text-xs text-on-surface-variant mt-4">Scanner will auto-process Student ID or RFID when detected</p>
                                </div>

                                <!-- Scan Mode Selector -->
                                <div id="barcodeScanModeSelector" class="p-4 bg-surface rounded-lg border border-outline-variant" style="display: none;">
                                    <p class="text-xs uppercase text-on-surface-variant font-bold mb-3">Scan Mode</p>
                                    <div class="grid grid-cols-3 gap-2">
                                        <button type="button" class="scan-mode-btn scan-mode-normal active px-3 py-2 border-2 border-outline-variant rounded-lg text-xs font-semibold text-on-surface hover:bg-surface-container transition-colors" data-mode="normal" title="Mark present on first scan, show modal on second">
                                            <span class="material-symbols-outlined text-base">touch_app</span>
                                            <div>Normal</div>
                                        </button>
                                        <button type="button" class="scan-mode-btn scan-mode-break px-3 py-2 border-2 border-outline-variant rounded-lg text-xs font-semibold text-on-surface hover:bg-surface-container transition-colors" data-mode="break" title="Directly mark break without modal">
                                            <span class="material-symbols-outlined text-base">coffee</span>
                                            <div>Break</div>
                                        </button>
                                        <button type="button" class="scan-mode-btn scan-mode-timeout px-3 py-2 border-2 border-outline-variant rounded-lg text-xs font-semibold text-on-surface hover:bg-surface-container transition-colors" data-mode="timeout" title="Directly timeout without modal">
                                            <span class="material-symbols-outlined text-base">logout</span>
                                            <div>Timeout</div>
                                        </button>
                                    </div>
                                </div>

                                <!-- Controls -->
                                <div class="p-6 space-y-4 border-t border-outline-variant">
                                    <!-- Last Scanned -->
                                    <div class="p-4 bg-surface rounded-lg border border-outline-variant">
                                        <p class="text-xs uppercase text-on-surface-variant font-bold mb-2">Last Scanned</p>
                                        <p id="barcodeLastScannedText" class="text-sm font-semibold text-on-surface">Waiting for scan...</p>
                                    </div>

                                    <!-- Scan Status -->
                                    <div id="barcodeScanStatus" class="hidden p-4 rounded-lg text-sm font-semibold">
                                        <div id="barcodeStatusMessage"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Scanned List -->
                    <div class="bg-surface-container-lowest rounded-xl shadow-sm border border-outline-variant overflow-hidden flex flex-col h-full">
                        <div class="p-3 border-b border-outline-variant">
                            <h3 class="text-lg font-bold text-on-surface">Scanned Today</h3>
                            <p class="text-sm text-on-surface-variant mt-1"><span id="scannedCount">0</span> students scanned</p>
                        </div>
                        
                        <div class="overflow-y-auto max-h-[750px]" id="scannedList">
                            <div class="p-6 text-center text-on-surface-variant">
                                <span class="material-symbols-outlined text-4xl mx-auto mb-2 opacity-50">inbox</span>
                                <p class="text-sm">No scans yet</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex gap-4 mb-6">
                    <div class="flex-1">
                        <label for="scan-session-filter" class="block text-sm font-semibold text-on-surface mb-2">Filter by Session</label>
                        <select id="scan-session-filter" class="w-full px-4 py-2 border border-outline-variant rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/40 bg-surface-container-lowest text-on-surface">
                            <option value="all">All Sessions</option>
                        </select>
                    </div>
                    <div class="flex-1">
                        <label for="scan-search" class="block text-sm font-semibold text-on-surface mb-2">Search Student</label>
                        <input type="text" id="scan-search" placeholder="Name or ID" class="w-full px-4 py-2 border border-outline-variant rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/40 bg-surface-container-lowest text-on-surface"/>
                    </div>
                </div>

                <!-- Attendance Table -->
                <div class="bg-surface-container-lowest rounded-xl shadow-sm border border-outline-variant overflow-hidden flex flex-col">
                    <div class="overflow-x-auto overflow-y-auto max-h-96 flex-1 flex flex-col">
                        <table class="w-full border-collapse min-w-max" id="scan-attendance-table">
                            <thead class="bg-surface-container border-b border-outline-variant sticky top-0 z-10">
                                <tr>
                                    <th class="px-8 py-4 text-left text-sm font-bold text-on-surface uppercase min-w-52">Student</th>
                                    <th class="px-8 py-4 text-left text-sm font-bold text-on-surface uppercase min-w-32">ID</th>
                                    <th class="px-8 py-4 text-left text-sm font-bold text-on-surface uppercase min-w-36">Program</th>
                                    <th class="px-8 py-4 text-left text-sm font-bold text-on-surface uppercase min-w-32">RFID</th>
                                    <th class="px-8 py-4 text-left text-sm font-bold text-on-surface uppercase min-w-32">Status</th>
                                    <th class="px-8 py-4 text-left text-sm font-bold text-on-surface uppercase min-w-40">Session</th>
                                    <th class="px-8 py-4 text-left text-sm font-bold text-on-surface uppercase min-w-40">Time In</th>
                                    <th class="px-8 py-4 text-left text-sm font-bold text-on-surface uppercase min-w-40">Time Out</th>
                                    <th id="duration-header" class="px-8 py-4 text-left text-sm font-bold text-on-surface uppercase min-w-40" style="display: none;">Duration</th>
                                    <th id="cycles-header" class="px-8 py-4 text-left text-sm font-bold text-on-surface uppercase min-w-32" style="display: none;">Cycles</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-outline-variant/20" id="attendance-table-body">
                            </tbody>
                        </table>
                    </div>
                    <div class="px-8 py-4 bg-surface-container border-t border-outline-variant text-sm text-on-surface-variant">
                        <p>Showing <strong id="attendance-record-count">0</strong> records</p>
                    </div>
                </div>

            </div>
        </div>
      </div>
    </main>
    <!-- END: Main Content Area -->

    <!-- Footer Area -->
    <footer class="sticky bottom-0 bg-slate-50 dark:bg-slate-950 w-full py-8 border-t border-slate-200 dark:border-slate-800 shadow-lg z-40">
        <div class="max-w-7xl mx-auto px-8 flex flex-col md:flex-row justify-between items-center gap-6">
            <div class="flex items-center gap-6">
                <a href="{{ route('event-detail', $event->e_id) }}" class="flex items-center gap-2 text-slate-500 hover:text-blue-900 transition-all font-medium text-xs">
                    <span class="material-symbols-outlined text-sm">arrow_back</span>
                    Back
                </a>
                <span class="text-slate-300">|</span>
                <p class="text-xs font-medium text-slate-500">©STI College Balagtas. All rights reserved.</p>
            </div>
            <div class="flex items-center gap-4">
                <button id="footerSessionBtn" class="px-6 py-2.5 bg-error text-white font-bold rounded-lg text-sm hover:brightness-95 transition-all shadow-lg shadow-error/10">
                    End Event Session
                </button>
            </div>
        </div>
    </footer>
</body>
</html>

<!-- Attendance Scanner Module -->
<script src="{{ asset('js/attendance-scanner.js') }}"></script>

