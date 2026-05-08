<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <meta name="csrf-token" content="{{ csrf_token() }}"/>
    <title>Event Details | STI Attendance</title>
    @php
        /** @var \App\Models\Event $event */
        /** @var \Illuminate\Support\Collection|\App\Models\Session[] $sessions */
        /** @var int $numDays */
        /** @var bool $checkedIn */
        /** @var bool $absent */
        /** @var int $totalStudents */
    @endphp
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
              "on-error": "#ffffff",
              "primary": "#003f87",
              "primary-fixed-dim": "#acc7ff",
              "on-surface-variant": "#424752",
              "on-secondary": "#ffffff",
              "tertiary-fixed": "#d6e3ff",
              "primary-fixed": "#d7e2ff",
              "primary-container": "#0056b3"
            },
            borderRadius: {
              DEFAULT: "0.125rem",
              lg: "0.25rem",
              xl: "0.5rem",
              full: "0.75rem"
            },
            fontFamily: {
              headline: ["Work Sans"],
              body: ["Inter"],
              label: ["Inter"]
            }
          }
        }
      }
    </script>
    <!-- jsBarcode Library -->
    <script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js"></script>
    <!-- QRCode.js Library for QR Codes -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
    <!-- Custom Styles -->
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #f9f9fb; color: #1a1c1d; }
        .font-headline { font-family: 'Work Sans', sans-serif; }
        .material-symbols-outlined { font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24; }
    </style>
</head>
<body class="bg-background text-on-surface selection:bg-secondary-fixed/30 min-h-screen flex flex-col">
    <!-- Shared Header Component -->
    <x-header />

    <!-- ====== Main Content Area ====== -->
    <main class="flex-grow">
        <!-- Event Hero Section -->
        <section class="bg-surface-container-low pt-8 pb-12 px-4 sm:px-6 md:px-8" data-purpose="event-hero-section" data-event-start-date="{{ $event->start_date }}">
            <div class="max-w-7xl mx-auto grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 md:gap-10 lg:gap-12 items-start">
                    <div class="lg:col-span-2">
                        
                        <div id="liveNowBadge" class="inline-flex items-center gap-2 px-3 py-1 bg-secondary-fixed text-on-secondary-fixed rounded-full text-xs font-bold tracking-wider mb-6">
                            <span class="w-2 h-2 bg-error rounded-full animate-pulse"></span>
                            LIVE NOW
                        </div>
                        <h1 class="text-5xl md:text-6xl font-extrabold text-primary tracking-tight leading-tight mb-8">
                            {{ $event->e_name }}
                        </h1>
                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-6">
                            <div class="flex flex-col gap-1">
                                <span class="text-label-md text-on-surface-variant text-xs uppercase tracking-widest font-semibold">Date</span>
                                <span class="text-on-surface font-medium">{{ \Carbon\Carbon::parse($event->start_date)->format('M d, Y') }}</span>
                            </div>
                            <div class="flex flex-col gap-1">
                                <span class="text-label-md text-on-surface-variant text-xs uppercase tracking-widest font-semibold">Duration</span>
                                <span class="text-on-surface font-medium">{{ \Carbon\Carbon::parse($event->start_time)->format('h:i A') }} - {{ \Carbon\Carbon::parse($event->end_time)->format('h:i A') }}</span>
                            </div>
                            <div class="flex flex-col gap-1">
                                <span class="text-label-md text-on-surface-variant text-xs uppercase tracking-widest font-semibold">Venue</span>
                                <span class="text-on-surface font-medium">{{ $event->e_location }}</span>
                            </div>
                            <div class="flex flex-col gap-1">
                                <span class="text-label-md text-on-surface-variant text-xs uppercase tracking-widest font-semibold">Students</span>
                                <span class="text-on-surface font-medium">{{ $totalStudents }} Enrolled</span>
                            </div>
                        </div>
                    </div>
                    <div class="bg-surface-container-lowest p-4 sm:p-6 md:p-8 rounded-xl shadow-xl shadow-primary/5 border-t-4 border-secondary-container">
                        <h3 class="text-xl font-bold text-primary mb-4">Real-time Check-in</h3>
                        <p class="text-on-surface-variant text-sm mb-6 leading-relaxed">
                            Instant RFID scanning enabled for all students. Data synchronizes automatically with the central registry.
                        </p>
                        <a href="{{ route('event.scan-attendance', $event->e_id) }}" class="w-full py-4 bg-secondary-container hover:bg-secondary-fixed-dim text-on-secondary-container font-bold rounded-lg transition-all flex items-center justify-center gap-3 shadow-lg shadow-secondary-container/20">
                            <span class="material-symbols-outlined" style="font-variation-settings: 'FILL' 1;">qr_code_scanner</span>
                            Scan Attendance
                        </a>
                    </div>
                </div>
            </section>
        <!-- Bento Layout: Schedule & Content -->
        <section class="max-w-[80rem] mx-auto px-4 sm:px-6 md:px-8 -mt-8 md:-mt-10 pb-12 md:pb-20">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 ga   p-4 md:gap-6 lg:gap-8">
                <!-- Schedule Sidebar -->
                <aside class="lg:col-span-1 space-y-6">
                    <div class="bg-primary p-4 sm:p-6 rounded-xl text-on-primary">
                        <h4 class="text-lg font-bold mb-4 flex items-center gap-2"><span class="material-symbols-outlined">schedule</span> Event Schedule</h4>
                        <div class="space-y-6 relative before:left-[11px] before:top-2 before:bottom-2 before:w-[2px] before:bg-on-primary/20">
                            @php
                                $startDate = \Carbon\Carbon::parse($event->start_date);
                                $endDate = \Carbon\Carbon::parse($event->end_date);
                                $numDays = $startDate->diffInDays($endDate) + 1;
                            @endphp
                            @for($i = 0; $i < $numDays; $i++)
                                @php $dayDate = $startDate->clone()->addDays($i); @endphp
                                <div class="relative pl-8">
                                    <span class="absolute left-0 top-1 w-6 h-6 rounded-full bg-secondary-container ring-4 ring-primary"></span>
                                    <p class="text-sm font-semibold">Day {{ $i + 1 }}: {{ $dayDate->format('D, M d, Y') }}</p>
                                    <p class="text-xs font-bold text-secondary-container opacity-90">{{ \Carbon\Carbon::parse($event->start_time)->format('h:i A') }} - {{ \Carbon\Carbon::parse($event->end_time)->format('h:i A') }}</p>
                                </div>
                            @endfor
                        </div>
                    </div>
                </aside>
                <!-- Tabbed Content Area -->
                <div class="lg:col-span-3">
                        <div class="bg-surface-container-lowest rounded-2xl shadow-sm overflow-hidden flex flex-col h-[90vh] md:h-[85vh]">
                        <!-- Tabs -->
                        <div class="flex overflow-x-auto border-b border-surface-container-high px-2 sm:px-4 flex-shrink-0 bg-surface justify-between items-center">
                            <div class="flex overflow-x-auto">
                                <button class="tab-btn px-3 sm:px-6 py-3 sm:py-4 text-primary font-bold border-b-2 border-primary whitespace-nowrap text-sm sm:text-base" data-tab="attendee-list">Attendee List</button>
                                <button class="tab-btn px-3 sm:px-6 py-3 sm:py-4 text-on-surface-variant font-medium hover:text-primary transition-colors whitespace-nowrap border-b-2 border-transparent text-sm sm:text-base" data-tab="daily-attendance">Daily Attendance</button>
                            </div>
                            <div class="flex items-center gap-2 flex-shrink-0">
                                <!-- Toggle Button for Attendee List -->
                                <button id="toggle-attendee-filters-btn" class="hidden p-2 rounded-lg hover:bg-surface-container transition-colors text-on-surface-variant" title="Toggle controls">
                                    <span class="material-symbols-outlined text-lg">unfold_more</span>
                                </button>
                                <!-- Toggle Button for Daily Attendance -->
                                <button id="toggle-daily-filters-btn" class="hidden p-2 rounded-lg hover:bg-surface-container transition-colors text-on-surface-variant" title="Toggle controls">
                                    <span class="material-symbols-outlined text-lg">unfold_more</span>
                                </button>
                                <button id="openSessionsModalBtn" class="px-4 py-2 bg-primary text-on-primary font-bold text-sm rounded-lg hover:bg-primary-fixed transition-colors flex-shrink-0 mr-2 flex items-center gap-2 shadow-sm">
                                    <span class="material-symbols-outlined text-lg">event</span>
                                    Sessions
                                </button>
                            </div>
                        </div>
                        <!-- Tab Contents Wrapper -->
                        <div class="flex-grow overflow-hidden min-h-0 flex flex-col">
                            <!-- Tab Content: Attendee List -->
                            <div id="attendee-list-tab" class="tab-content w-full h-full flex flex-col overflow-hidden">
                            <!-- Search and Action Bar - Collapsible -->
                            <div id="attendee-filters-content" class="px-4 sm:px-6 md:px-8 py-3 sm:py-4 bg-surface border-b border-surface-container-low flex-shrink-0">
                                <div class="flex flex-col md:flex-row gap-4 items-center">
                                    <div class="relative flex-grow w-full">
                                        <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-on-surface-variant/50">search</span>
                                        <form method="GET" action="{{ route('event-detail', ['eventId' => $event->e_id]) }}">
                                            <input id="attendee-search" name="attendee_search" value="{{ request('attendee_search') }}" class="w-full pl-12 pr-4 py-3 bg-surface-container-low border-none rounded-lg focus:ring-2 focus:ring-primary/20 text-sm" placeholder="Search by name or student ID..." type="text" />
                                        </form>
                                    </div>
                                    <div class="flex gap-2 flex-shrink-0">
                                        <button class="px-4 py-2 flex items-center gap-2 bg-surface-container-low hover:bg-surface-container-high transition-colors text-primary font-semibold rounded-lg text-sm border border-transparent focus:outline-none focus:ring-0 focus-visible:outline-none focus-visible:ring-0" id="attendee-import-btn">
                                            <span class="material-symbols-outlined text-sm">upload</span> Import
                                        </button>
                                        <div class="relative">
                                            <button class="px-4 py-2 flex items-center gap-2 bg-surface-container-low hover:bg-surface-container-high transition-colors text-primary font-semibold rounded-lg text-sm" id="attendee-export-dropdown-btn">
                                                <span class="material-symbols-outlined text-sm">download</span> Export
                                            </button>
                                            <div id="attendee-export-dropdown-menu" class="absolute right-0 mt-2 w-48 bg-surface-container-lowest border border-outline rounded-lg shadow-lg z-50 hidden">
                                                <button type="button" class="w-full text-left px-4 py-2 text-sm font-semibold text-on-surface hover:bg-surface-container border-b flex items-center gap-2 transition-colors" onclick="exportAttendeeListToPDF()">
                                                    <span class="material-symbols-outlined text-lg">download</span>
                                                    Export PDF
                                                </button>
                                                <button type="button" class="w-full text-left px-4 py-2 text-sm font-semibold text-on-surface hover:bg-surface-container border-b flex items-center gap-2 transition-colors" onclick="exportAttendeeListToExcel()">
                                                    <span class="material-symbols-outlined text-lg">table</span>
                                                    Export Excel
                                                </button>
                                                <button type="button" class="w-full text-left px-4 py-2 text-sm font-semibold text-on-surface hover:bg-surface-container flex items-center gap-2 transition-colors" onclick="exportAllQRCodes()">
                                                    <span class="material-symbols-outlined text-lg">qr_code</span>
                                                    Download QR Codes
                                                </button>
                                            </div>
                                        </div>
                                        <button class="px-4 py-2 flex items-center gap-2 bg-primary text-on-primary font-semibold rounded-lg text-sm shadow-md shadow-primary/10" id="manual-add-btn">
                                            <span class="material-symbols-outlined text-sm">person_add</span> Manual Add
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- Add Student Form -->
                            <div id="add-student-form" class="hidden px-4 sm:px-6 md:px-8 py-4 sm:py-6 bg-surface border-b border-surface-container-low flex-shrink-0">
                                <div class="grid grid-cols-1 sm:grid-cols-6 gap-3">
                                    <div>
                                        <input id="attendee_student_name" class="w-full text-sm bg-surface-container-low border border-outline rounded-lg px-3 py-2 focus:ring-2 focus:ring-primary/40 focus:border-primary text-on-surface placeholder-on-surface-variant" placeholder="Full Name" type="text" />
                                    </div>
                                    <div>
                                        <input id="attendee_student_snumber" class="w-full text-sm bg-surface-container-low border border-outline rounded-lg px-3 py-2 focus:ring-2 focus:ring-primary/40 focus:border-primary text-on-surface placeholder-on-surface-variant" placeholder="Student ID" type="text" />
                                    </div>
                                    <div>
                                        <input id="attendee_section" class="w-full text-sm bg-surface-container-low border border-outline rounded-lg px-3 py-2 focus:ring-2 focus:ring-primary/40 focus:border-primary text-on-surface placeholder-on-surface-variant" placeholder="Section" type="text" />
                                    </div>
                                    <div>
                                        <input id="attendee_program" class="w-full text-sm bg-surface-container-low border border-outline rounded-lg px-3 py-2 focus:ring-2 focus:ring-primary/40 focus:border-primary text-on-surface placeholder-on-surface-variant" placeholder="Program (Optional)" type="text" />
                                    </div>
                                    <div>
                                        <input id="attendee_rfid" class="w-full text-sm bg-surface-container-low border border-outline rounded-lg px-3 py-2 focus:ring-2 focus:ring-primary/40 focus:border-primary text-on-surface placeholder-on-surface-variant" placeholder="RFID (Optional)" type="text" />
                                    </div>
                                    <div>
                                        <button type="button" id="addAttendeeBtn" class="w-full bg-primary hover:bg-primary-fixed text-on-primary px-4 py-2 rounded-lg transition-colors flex items-center justify-center font-bold text-sm">
                                            <span class="material-symbols-outlined text-lg">add</span> Add
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- Import CSV Form -->
                            <div id="import-csv-form" class="hidden px-4 sm:px-6 md:px-8 py-6 sm:py-8 bg-surface border-b border-surface-container-low flex-shrink-0 text-center">
                                <div class="flex justify-center mb-4">
                                    <span class="material-symbols-outlined text-7xl text-on-surface-variant">description</span>
                                </div>
                                <h3 class="text-lg font-bold text-on-surface mb-2">Upload CSV File</h3>
                                <p class="text-on-surface-variant text-sm mb-6">Upload a <span class="font-semibold">.csv</span> file to register multiple attendees at once.</p>
                                
                                <div class="flex gap-3 justify-center">
                                    <button type="button" id="download-template-btn" class="flex items-center gap-2 px-6 py-2 border border-outline text-on-surface font-semibold rounded-lg hover:bg-surface-container transition-colors">
                                        <span class="material-symbols-outlined text-lg">download</span>
                                        Download Template
                                    </button>
                                    <button type="button" id="select-csv-file-btn" class="flex items-center gap-2 px-6 py-2 bg-error hover:bg-error text-on-error font-semibold rounded-lg transition-colors">
                                        <span class="material-symbols-outlined text-lg">upload</span>
                                        Select File
                                    </button>
                                    <input type="file" id="attendee-import-file" accept=".csv" style="display: none;" />
                                </div>
                            </div>

                            <!-- Attendance Table -->
                            <div class="flex-grow overflow-hidden min-h-0 flex flex-col">
                                <div class="overflow-x-auto overflow-y-auto flex-grow border-b border-surface-container-low w-full max-w-full">
                                    <table class="min-w-[1100px] w-full text-left">
                                        <thead class="sticky top-0 z-10">
                                        <tr class="border-b border-surface-container-low bg-surface-container-low">
                                            <th class="py-5 px-6 text-sm font-bold text-on-surface-variant uppercase tracking-wider">Full Name</th>
                                            <th class="py-5 px-6 text-sm font-bold text-on-surface-variant uppercase tracking-wider">Student ID</th>
                                            <th class="py-5 px-6 text-sm font-bold text-on-surface-variant uppercase tracking-wider">Section</th>
                                            <th class="py-5 px-6 text-sm font-bold text-on-surface-variant uppercase tracking-wider">Program</th>
                                            <th class="py-5 px-6 text-sm font-bold text-on-surface-variant uppercase tracking-wider">RFID</th>
                                            <th class="py-5 px-6 text-sm font-bold text-on-surface-variant uppercase tracking-wider text-right">Actions</th>
                                        </tr>
                                        </thead>
                                        <tbody id="attendee-table-body" class="divide-y divide-surface-container-low">
                                            @include('event.partials.attendee-list-rows')
                                        </tbody>
                                    </table>
                                </div>

                                <!-- Table Summary -->
                                <div class="bg-surface px-4 sm:px-6 py-3 sm:py-4 border-t border-surface-container-low flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between flex-shrink-0">
                                    <div class="space-y-1 text-sm text-on-surface-variant">
                                        <p id="attendee-pagination-info">Showing <strong>{{ $uniqueStudents->count() ? $uniqueStudents->firstItem() : 0 }}</strong> to <strong>{{ $uniqueStudents->count() ? $uniqueStudents->lastItem() : 0 }}</strong> of <strong>{{ $uniqueStudents->total() }}</strong> results</p>
                                    </div>
                                    <div class="flex items-center gap-2 justify-start sm:justify-end">
                                        <a id="attendee-page-prev" href="{{ $uniqueStudents->appends(request()->query())->previousPageUrl() ?? '#' }}" data-current-page="{{ $uniqueStudents->currentPage() }}" data-last-page="{{ $uniqueStudents->lastPage() }}" class="px-4 py-2 bg-surface-container rounded-lg text-sm font-semibold text-on-surface-variant hover:bg-surface-container-high transition-colors {{ $uniqueStudents->onFirstPage() ? 'pointer-events-none opacity-50' : '' }}">
                                            Previous
                                        </a>
                                        <span id="attendee-page-indicator" class="text-sm text-on-surface-variant">Page {{ $uniqueStudents->currentPage() }} of {{ $uniqueStudents->lastPage() }}</span>
                                        <a id="attendee-page-next" href="{{ $uniqueStudents->appends(request()->query())->nextPageUrl() ?? '#' }}" data-current-page="{{ $uniqueStudents->currentPage() }}" data-last-page="{{ $uniqueStudents->lastPage() }}" class="px-4 py-2 bg-surface-container rounded-lg text-sm font-semibold text-on-surface-variant hover:bg-surface-container-high transition-colors {{ !$uniqueStudents->hasMorePages() ? 'pointer-events-none opacity-50' : '' }}">
                                            Next
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- END: Attendee List Tab Content -->

                        <!-- Daily Attendance Tab Content -->
                        <div id="daily-attendance-tab" class="tab-content w-full h-full hidden flex flex-col overflow-hidden">
                        <!-- Collapsible Controls -->
                        <div id="daily-controls-content" class="flex flex-col flex-shrink-0">
                            <!-- Day Toggle Buttons -->
                            <div id="day-toggle-buttons" class="px-2 sm:px-6 py-2 sm:py-4 bg-surface border-b border-surface-container-low flex gap-2 overflow-x-auto flex-shrink-0">
                                    @php
                                        $startDate = \Carbon\Carbon::parse($event->start_date);
                                        $endDate = \Carbon\Carbon::parse($event->end_date);
                                        $numDays = $startDate->diffInDays($endDate) + 1;
                                    @endphp
                                    @for($i = 0; $i < $numDays; $i++)
                                        @php $dayDate = $startDate->clone()->addDays($i); @endphp
                                        <button type="button" class="day-toggle-btn flex-shrink-0 px-4 py-2 rounded-full font-bold text-sm transition-all whitespace-nowrap {{ $i === 0 ? 'bg-error text-on-error' : 'bg-surface-container text-on-surface-variant hover:bg-surface-container-high' }}" data-day="{{ $i + 1 }}" data-day-date="{{ $dayDate->format('Y-m-d') }}">
                                        Day {{ $i + 1 }} ({{ $dayDate->format('D, M d, Y') }})
                                    </button>
                                    @endfor
                                </div>

                            <!-- Search and Filter Bar -->
                            <div id="daily-filters-container" class="px-4 sm:px-6 md:px-8 py-3 sm:py-4 bg-surface border-b border-surface-container-low flex-shrink-0">
                                <div class="flex flex-col md:flex-row gap-4 items-center">
                                    <div class="relative flex-grow w-full">
                                        <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-on-surface-variant/50">search</span>
                                        <input type="text" id="daily-search" class="w-full pl-12 pr-4 py-3 bg-surface-container-low border-none rounded-lg focus:ring-2 focus:ring-primary/20 text-sm" placeholder="Search by name or student ID..."/>
                                    </div>
                                    <div class="flex gap-2 flex-shrink-0">
                                        <select id="daily-status-filter" class="px-4 py-2 bg-surface-container-low hover:bg-surface-container-high transition-colors font-semibold rounded-lg text-sm text-on-surface border-none outline-none appearance-none focus:outline-none focus-visible:outline-none focus:border-none focus:ring-0 focus-visible:ring-0">
                                            <option value="">All</option>
                                            <option value="present">Present</option>
                                            <option value="absent">Absent</option>
                                        </select>
                                        <select id="daily-session-filter" class="px-4 py-2 bg-surface-container-low hover:bg-surface-container-high transition-colors font-semibold rounded-lg text-sm text-on-surface border-none outline-none appearance-none focus:outline-none focus-visible:outline-none focus:border-none focus:ring-0 focus-visible:ring-0">
                                            <!-- Sessions will be populated by JavaScript based on selected day -->
                                        </select>
                                        <div class="relative">
                                            <button id="export-dropdown-btn" class="px-4 py-2 flex items-center gap-2 bg-surface-container-low hover:bg-surface-container-high transition-colors text-primary font-semibold rounded-lg text-sm">
                                                <span class="material-symbols-outlined text-sm">download</span> Export
                                            </button>
                                            <div id="export-dropdown-menu" class="absolute right-0 mt-2 w-48 bg-surface-container-lowest border border-outline rounded-lg shadow-lg z-50 hidden">
                                                <button type="button" class="w-full text-left px-4 py-2 text-sm font-semibold text-on-surface hover:bg-surface-container border-b flex items-center gap-2 transition-colors" onclick="exportToPDF()">
                                                    <span class="material-symbols-outlined text-lg">download</span>
                                                    Export PDF
                                                </button>
                                                <button type="button" class="w-full text-left px-4 py-2 text-sm font-semibold text-on-surface hover:bg-surface-container border-b flex items-center gap-2 transition-colors" onclick="exportToExcel()">
                                                    <span class="material-symbols-outlined text-lg">table</span>
                                                    Export Excel
                                                </button>
                                                <button type="button" class="w-full text-left px-4 py-2 text-sm font-semibold text-on-surface hover:bg-surface-container flex items-center gap-2 transition-colors" onclick="exportAllQRCodes()">
                                                    <span class="material-symbols-outlined text-lg">qr_code</span>
                                                    Download QR Codes
                                                </button>
                                            </div>
                                        </div>
                                        <button id="openMarkAttendanceBtn" class="px-4 py-2 flex items-center gap-2 bg-primary text-on-primary font-semibold rounded-lg text-sm shadow-md shadow-primary/10">
                                            <span class="material-symbols-outlined text-sm">add</span> Mark Attendance
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Daily Attendance Table -->
                        <div class="flex-grow overflow-hidden flex flex-col">
                            <div class="overflow-x-auto overflow-y-auto flex-grow border-b border-surface-container-low w-full max-w-full">
                                <table class="w-full text-left daily-attendance-table" id="main-daily-table">
                                    <thead class="sticky top-0 z-10">
                                    <tr class="border-b border-surface-container-low bg-surface-container-low">
                                        <th class="py-5 px-6 text-sm font-bold text-on-surface-variant uppercase tracking-wider">Full Name</th>
                                        <th class="py-5 px-6 text-sm font-bold text-on-surface-variant uppercase tracking-wider">Student ID</th>
                                        <th class="py-5 px-6 text-sm font-bold text-on-surface-variant uppercase tracking-wider">Section</th>
                                        <th class="py-5 px-6 text-sm font-bold text-on-surface-variant uppercase tracking-wider">Program</th>
                                        <th class="py-5 px-6 text-sm font-bold text-on-surface-variant uppercase tracking-wider text-center">Time In</th>
                                        @if($event->require_action_prompts)
                                        <th class="py-5 px-6 text-sm font-bold text-on-surface-variant uppercase tracking-wider text-center">Duration</th>
                                        <th class="py-5 px-6 text-sm font-bold text-on-surface-variant uppercase tracking-wider text-center">Cycles</th>
                                        @endif
                                        <th class="py-5 px-6 text-sm font-bold text-on-surface-variant uppercase tracking-wider text-center">Status</th>
                                    </tr>
                                    </thead>
                                    <tbody class="divide-y divide-surface-container-low" id="daily-table-body"></tbody>
                                </table>
                            </div>

                            <!-- Summary -->
                            <div class="bg-surface px-4 sm:px-6 py-3 sm:py-4 border-t border-surface-container-low flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between flex-shrink-0">
                                <div class="space-y-1 text-sm text-on-surface-variant">
                                    <p id="daily-pagination-info">Showing 0 to 0 of 0 results</p>
                                </div>
                                <div class="flex items-center gap-2 justify-start sm:justify-end">
                                    <button id="daily-page-prev" type="button" class="px-4 py-2 bg-surface-container rounded-lg text-sm font-semibold text-on-surface-variant hover:bg-surface-container-high transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                                        Previous
                                    </button>
                                    <span id="daily-page-indicator" class="text-sm text-on-surface-variant">Page 1 of 1</span>
                                    <button id="daily-page-next" type="button" class="px-4 py-2 bg-surface-container rounded-lg text-sm font-semibold text-on-surface-variant hover:bg-surface-container-high transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                                        Next
                                    </button>
                                </div>
                            </div>
                        </div>
                        <!-- END: Daily Attendance Tab Content -->

                        <!-- Session Tab Content -->
                        <!-- Removed - now using modal approach -->
                        </div>
                        <!-- END: Tab Contents Wrapper -->
                    </div>
                </div>
            </div>
        </section>
        <!-- END: Bento Layout -->
    </main>
    <!-- END: Main Content Area -->
    <!-- Footer Area -->
    <footer class="sticky bottom-0 bg-slate-50 dark:bg-slate-950 w-full py-6 md:py-8 border-t border-slate-200 dark:border-slate-800 shadow-lg z-40">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 md:px-8 flex flex-col md:flex-row justify-between items-center gap-4 md:gap-6">
            <div class="flex items-center gap-6">
                <a href="{{ route('dashboard') }}" class="flex items-center gap-2 text-slate-500 hover:text-blue-900 transition-all font-medium text-xs">
                    <span class="material-symbols-outlined text-sm">arrow_back</span>
                    Back to Events
                </a>
                <span class="text-slate-300">|</span>
                <p class="text-xs font-medium text-slate-500">Â© 2024 STI College Balagtas. All rights reserved.</p>
            </div>
            <div class="flex items-center gap-4">
                <button id="footerEventSessionBtn" class="px-6 py-2.5 bg-error text-on-error font-bold rounded-lg text-sm hover:brightness-95 transition-all shadow-lg shadow-error/10">
                    End Event Session
                </button>
            </div>
        </div>
    </footer>

    <!-- Sessions Modal -->
    <div id="sessionsModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
        <div class="bg-surface rounded-2xl shadow-2xl w-full max-w-lg sm:max-w-2xl md:max-w-3xl lg:max-w-4xl mx-2 sm:mx-4 max-h-[90vh] overflow-hidden border border-outline flex flex-col">
            <!-- Modal Header -->
            <div class="px-4 sm:px-8 py-4 sm:py-6 bg-surface border-b border-surface-container-low flex items-center justify-between flex-shrink-0">
                <div class="flex items-center gap-3">
                    <span class="material-symbols-outlined text-3xl text-primary">event</span>
                    <h2 class="text-2xl font-bold text-on-surface">Event Sessions</h2>
                </div>
                <button id="closeSessionsModalBtn" class="p-2 hover:bg-surface-container rounded-lg transition-colors">
                    <span class="material-symbols-outlined text-on-surface-variant">close</span>
                </button>
            </div>

            <!-- Session Management Buttons -->
            <div class="px-4 sm:px-8 py-3 sm:py-4 bg-surface border-b border-surface-container-low flex items-center gap-2 sm:gap-3 flex-shrink-0">
                <button id="startNextSessionBtnModal" data-event-id="{{ $event->e_id }}" class="px-4 py-2 bg-primary text-on-primary font-bold text-sm rounded-lg hover:bg-primary-fixed transition-colors shadow-sm flex items-center gap-2">
                    <span class="material-symbols-outlined text-lg">play_arrow</span>
                    Start Session
                </button>
                <button id="endCurrentSessionBtnModal" data-event-id="{{ $event->e_id }}" class="px-4 py-2 border border-error text-error font-bold text-sm rounded-lg hover:bg-error/10 transition-colors flex items-center gap-2">
                    <span class="material-symbols-outlined text-lg">stop_circle</span>
                    End Session
                </button>
            </div>

            <!-- Session Content -->
            <div class="flex-1 overflow-y-auto min-h-0 bg-surface-container-low/50 p-4 sm:p-8">
                @php
                    $startDate = \Carbon\Carbon::parse($event->start_date);
                    $endDate = \Carbon\Carbon::parse($event->end_date);
                    $numEventDays = $startDate->diffInDays($endDate) + 1;
                @endphp

                <!-- Day Filter Buttons -->
                <div class="flex flex-wrap gap-2 mb-8">
                    @for ($day = 1; $day <= $numEventDays; $day++)
                        <button class="session-modal-day-toggle px-4 py-2 font-bold text-sm rounded-lg transition-colors {{ $day === 1 ? 'bg-error text-on-error' : 'bg-surface-container text-on-surface-variant hover:bg-surface-container-high' }}" data-day="{{ $day }}">
                            Day {{ $day }}
                        </button>
                    @endfor
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @forelse($sessions as $session)
                        @php
                            $attendanceCount = $session->attendances ? count($session->attendances) : 0;
                            $presentCount = $session->attendances ? $session->attendances->filter(fn($a) => $a->time_in !== null)->count() : 0;
                            $statusBg = $session->status === 'active' ? 'bg-primary-fixed' : ($session->status === 'completed' ? 'bg-secondary-fixed' : 'bg-surface-container');
                            $statusColor = $session->status === 'active' ? 'text-on-primary-fixed' : ($session->status === 'completed' ? 'text-on-secondary-fixed' : 'text-on-surface-variant');
                            // Card background color based on status
                            $cardBg = $session->status === 'active' ? 'bg-yellow-50 dark:bg-yellow-950 border-yellow-200 dark:border-yellow-800' : ($session->status === 'completed' ? 'bg-gray-50 dark:bg-gray-900 border-gray-300 dark:border-gray-700' : 'bg-blue-50 dark:bg-blue-950 border-blue-200 dark:border-blue-800');
                        @endphp
                        <div class="session-modal-card {{ $cardBg }} rounded-2xl border p-6 shadow-sm hover:shadow-md transition-shadow" data-day="{{ $session->day_number }}" data-status="{{ $session->status }}">
                            <div class="flex items-start justify-between mb-4">
                                <div>
                                    <p class="text-xs font-bold uppercase text-on-surface-variant mb-1">Session {{ $session->session_number }}</p>
                                    <p class="text-lg font-bold text-on-surface">Day {{ $session->day_number }}</p>
                                </div>
                                <span class="px-3 py-1 {{ $statusBg }} {{ $statusColor }} text-xs font-bold rounded-full whitespace-nowrap">
                                    @if($session->status === 'active')
                                        Live
                                    @elseif($session->status === 'completed')
                                        Done
                                    @else
                                        Upcoming
                                    @endif
                                </span>
                            </div>

                            <p class="text-sm text-on-surface-variant mb-4">
                                ðŸ“… {{ \Carbon\Carbon::parse($session->session_date)->format('M d, Y') }}
                            </p>

                            <div class="space-y-3 mb-6 pt-6 border-t border-outline">
                                <div class="flex justify-between items-center">
                                    <span class="text-sm text-on-surface-variant">Registered:</span>
                                    <span class="font-bold text-on-surface">{{ $attendanceCount }}</span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-sm text-on-surface-variant">Present:</span>
                                    <span class="font-bold text-secondary-fixed">{{ $presentCount }}</span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-sm text-on-surface-variant">Absent:</span>
                                    <span class="font-bold text-error">{{ $attendanceCount - $presentCount }}</span>
                                </div>
                            </div>

                            @if($session->start_time)
                                <p class="text-xs text-on-surface-variant mb-2">
                                    â±ï¸ Started: {{ \Carbon\Carbon::parse($session->start_time)->format('h:i A') }}
                                </p>
                            @endif
                            @if($session->end_time)
                                <p class="text-xs text-on-surface-variant mb-4">
                                    ðŸ Ended: {{ \Carbon\Carbon::parse($session->end_time)->format('h:i A') }}
                                </p>
                            @endif

                            <!-- Personnel Info -->
                            @if($session->status !== 'upcoming')
                                <div class="pt-4 border-t border-outline mt-4">
                                    <p class="text-xs font-bold uppercase text-on-surface-variant mb-2">Managed By</p>
                                    @if($session->user)
                                        <div class="flex items-center gap-2">
                                            <div class="w-8 h-8 rounded-full bg-primary text-on-primary flex items-center justify-center text-xs font-bold">
                                                {{ strtoupper(substr($session->user->name, 0, 2)) }}
                                            </div>
                                            <div class="flex-1 min-w-0">
                                                <p class="text-sm font-semibold text-on-surface truncate">{{ $session->user->name }}</p>
                                                <p class="text-xs text-on-surface-variant truncate">{{ $session->user->email }}</p>
                                            </div>
                                        </div>
                                    @else
                                        <p class="text-xs text-on-surface-variant italic">Not assigned</p>
                                    @endif
                                </div>
                            @endif
                        </div>
                    @empty
                        <div class="col-span-full text-center py-16">
                            <p class="text-on-surface-variant text-lg mb-2">ðŸ“‹ No sessions yet</p>
                            <p class="text-on-surface-variant text-sm">
                                This event is configured for <strong>{{ $event->sessions }} session(s) per day</strong>
                            </p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</body>
</html>

@php
    $sessionsForEvent = $event->sessions()->orderBy('day_number')->orderBy('session_number')->get();
    $sessionsByDay = $sessionsForEvent->groupBy('day_number');
    $sessionsArray = [];
    for ($day = 1; $day <= $numDays; $day++) {
        $daySessions = $sessionsByDay->get($day, collect());
        $sessionsArray[$day] = [];
        foreach ($daySessions as $session) {
            /** @var \App\Models\Session $session */
            $sessionsArray[$day][] = [
                'id' => $session->getKey(),
                'number' => $session->session_number,
                'day' => $session->day_number,
                'status' => $session->status
            ];
        }
    }
@endphp

<div id="sessions-data" style="display: none;" data-sessions="{{ json_encode($sessionsArray) }}"></div>
<div id="event-config" style="display: none;" data-event-id="{{ $event->getKey() }}" data-require-action-prompts="{{ $event->require_action_prompts }}" data-checked-in="{{ $checkedIn }}" data-absent="{{ $absent }}" data-total-students="{{ $totalStudents }}"></div>

<script src="{{ asset('js/event-detail.js') }}"></script>

<!-- Old inline script removed - functionality moved to public/js/event-detail.js -->

