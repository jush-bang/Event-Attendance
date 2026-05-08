<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>" />
    <title>Event Dashboard - STI Balagtas</title>

    <!-- Fonts -->
    <link href="https://fonts.googleapis.com" rel="preconnect" />
    <link crossorigin="" href="https://fonts.gstatic.com" rel="preconnect" />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Work+Sans:wght@400;500;600;700;800;900&display=swap" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet" />

    <!-- Tailwind CSS v3 with Plugins -->
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
              "primary-container": "#0056b3",
              "greeen-shade": "#98FB98"
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

    <!-- Custom Styles -->
    <style>
      body { font-family: 'Inter', sans-serif; background-color: #f9f9fb; color: #1a1c1d; }
      .font-headline { font-family: 'Work Sans', sans-serif; }
      .material-symbols-outlined { font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24; }
      .glass-nav { backdrop-filter: blur(12px); -webkit-backdrop-filter: blur(12px); }
      .card-hover-effect { transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); }
      .card-hover-effect:hover { transform: translateY(-4px); box-shadow: 0 20px 40px -20px rgba(0, 63, 135, 0.12); }
    </style>
  <body class="bg-surface selection:bg-secondary-fixed selection:text-on-secondary-fixed">
    <!-- Shared Header Component -->
    <?php if (isset($component)) { $__componentOriginalfd1f218809a441e923395fcbf03e4272 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalfd1f218809a441e923395fcbf03e4272 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.header','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('header'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalfd1f218809a441e923395fcbf03e4272)): ?>
<?php $attributes = $__attributesOriginalfd1f218809a441e923395fcbf03e4272; ?>
<?php unset($__attributesOriginalfd1f218809a441e923395fcbf03e4272); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalfd1f218809a441e923395fcbf03e4272)): ?>
<?php $component = $__componentOriginalfd1f218809a441e923395fcbf03e4272; ?>
<?php unset($__componentOriginalfd1f218809a441e923395fcbf03e4272); ?>
<?php endif; ?>

    <!-- Main Content Canvas -->
    <main class="max-w-[1200px] mx-auto px-10 pt-16 pb-24">
      <!-- Header Section -->
      <header class="mb-16">
        <div class="inline-block bg-secondary-fixed text-on-secondary-fixed px-3 py-1 rounded-lg text-xs font-bold uppercase tracking-widest mb-4">Administration Portal</div>
        <h1 class="text-6xl font-black text-primary font-headline tracking-tighter leading-none mb-4">Welcome, Admin</h1>
        <p class="text-on-surface-variant text-lg max-w-2xl leading-relaxed">Oversee campus engagement, manage institutional gatherings, and track student attendance with precision.</p>
      </header>

      <!-- Functional Controls -->
      <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 mb-12">
        <div class="flex p-1 bg-surface-container-low rounded-xl w-fit">
          <button class="filter-btn-all px-8 py-2.5 rounded-lg text-sm font-bold font-headline bg-surface-container-lowest text-primary shadow-sm transition-all duration-200" data-filter="all">All</button>
          <button class="filter-btn-active px-8 py-2.5 rounded-lg text-sm font-medium font-headline text-on-surface-variant hover:text-primary transition-all duration-200" data-filter="active">Active</button>
          <button class="filter-btn-history px-8 py-2.5 rounded-lg text-sm font-medium font-headline text-on-surface-variant hover:text-primary transition-all duration-200" data-filter="history">History</button>
          <button class="filter-btn-archive px-8 py-2.5 rounded-lg text-sm font-medium font-headline text-on-surface-variant hover:text-primary transition-all duration-200" data-filter="archive" onclick="openArchivesModal()">Archives</button>
        </div>
        <div class="flex items-center gap-3">
          <span class="text-xs font-bold text-on-surface-variant uppercase tracking-widest">Showing <?php echo e($events->count()); ?> of <?php echo e($events->total()); ?> Events (Page <?php echo e($events->currentPage()); ?> of <?php echo e($events->lastPage()); ?>)</span>
        </div>
      </div>

      <!-- Event Grid -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
        <?php $__empty_1 = true; $__currentLoopData = $events; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $event): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
          <!-- Event Card -->
          <div class="event-card card-hover-effect bg-surface-container-lowest border border-surface-container rounded-2xl p-8 flex flex-col h-full group relative" data-status="<?php echo e($event->status); ?>">
            <!-- Dropdown Menu (outside the link) -->
            <div class="absolute top-8 right-8">
              <div class="relative group">
                <button class="event-menu-btn p-2 hover:bg-surface-container rounded-lg transition-colors text-primary/50 hover:text-primary" onclick="event.stopPropagation(); toggleEventMenu(this)" data-event-id="<?php echo e($event->e_id); ?>">
                  <span class="material-symbols-outlined">more_vert</span>
                </button>
                <div class="event-menu hidden absolute right-0 mt-1 w-48 bg-surface-container-lowest border border-outline rounded-lg shadow-lg z-50">
                  <button class="w-full text-left px-4 py-3 text-sm font-semibold text-on-surface hover:bg-surface-container border-b border-outline flex items-center gap-2 transition-colors archive-btn" onclick="event.stopPropagation(); archiveEvent('<?php echo e($event->e_id); ?>')"> 
                    <span class="material-symbols-outlined text-lg">archive</span>
                    Archive Event
                  </button>
                  <button class="w-full text-left px-4 py-3 text-sm font-semibold text-error hover:bg-error/10 flex items-center gap-2 transition-colors delete-btn" onclick="event.stopPropagation(); deleteEvent('<?php echo e($event->e_id); ?>')"> 
                    <span class="material-symbols-outlined text-lg">delete</span>
                    Delete Event
                  </button>
                </div>
              </div>
            </div>
            
            <!-- Content link -->
            <a href="<?php echo e(route('event-detail', ['eventId' => $event->e_id])); ?>" class="flex-1 flex flex-col cursor-pointer">
            <div class="flex justify-between items-start mb-6">
              <?php if($event->status === 'live'): ?>
                <span class="bg-green-100 text-primary px-3 py-1 rounded-lg text-[10px] font-black uppercase tracking-widest">Active</span>
              <?php elseif($event->status === 'upcoming'): ?>
                <span class="bg-primary/5 text-primary px-3 py-1 rounded-lg text-[10px] font-black uppercase tracking-widest">Upcoming</span>
              <?php else: ?>
                <span class="bg-yellow-100 text-primary px-3 py-1 rounded-lg text-[10px] font-black uppercase tracking-widest">Completed</span>
              <?php endif; ?>
            </div>
            <h3 class="text-2xl font-bold font-headline text-primary mb-3 leading-tight"><?php echo e($event->e_name); ?></h3>
            <div class="flex items-center gap-2 text-on-surface-variant mb-10">
              <span class="material-symbols-outlined text-base">calendar_today</span>
              <span class="text-sm font-medium"><?php echo e(\Carbon\Carbon::parse($event->start_date)->format('M d, Y')); ?> • <?php echo e(\Carbon\Carbon::parse($event->start_time)->format('h:i A')); ?></span>
            </div>
            <!-- Managed by section - shown when session is active -->
            <div class="managed-by-section mb-6 pb-6 border-b border-surface-container-high hidden" data-event-id="<?php echo e($event->e_id); ?>">
              <p class="text-[10px] uppercase tracking-widest text-on-surface-variant font-bold mb-2">Managed by</p>
              <div class="flex items-center gap-2">
                <span class="material-symbols-outlined text-sm text-primary">person</span>
                <div>
                  <p class="text-sm font-semibold text-on-surface manager-name">—</p>
                  <p class="text-xs text-on-surface-variant manager-email">—</p>
                </div>
              </div>
            </div>
            <div class="mt-auto pt-8 grid grid-cols-2 gap-4">
              <div class="flex flex-col">
                <p class="text-[10px] uppercase tracking-widest text-on-surface-variant font-bold mb-1">Attendance</p>
                <p class="text-3xl font-black font-headline text-primary"><?php echo e($event->attendancePercentage); ?>%</p>
              </div>
              <div class="flex flex-col">
                <p class="text-[10px] uppercase tracking-widest text-on-surface-variant font-bold mb-1">Students</p>
                <p class="text-xl font-bold font-headline text-on-surface"><?php echo e($event->totalStudents); ?></p>
              </div>
              <div class="col-span-2 mt-4">
                <div class="w-full h-2 bg-surface-container rounded-full overflow-hidden">
                  <div class="h-full bg-primary rounded-full progress-bar" data-percentage="<?php echo e($event->attendancePercentage); ?>"></div>
                </div>
              </div>
            </div>
            </a>
          </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
          <!-- No Events Placeholder -->
          <div class="col-span-full">
            <div class="text-center py-12 bg-surface-container-lowest rounded-2xl border border-surface-container">
              <span class="material-symbols-outlined text-5xl text-primary/30 mx-auto mb-4 block">inbox</span>
              <h4 class="text-lg font-bold text-primary mb-2">No Events Yet</h4>
              <p class="text-on-surface-variant mb-6">Get started by scheduling your first event</p>
              <a href="<?php echo e(route('schedule-event')); ?>" class="inline-flex items-center gap-2 bg-primary text-on-primary px-6 py-2.5 rounded-xl font-headline text-sm font-bold tracking-tight hover:opacity-80 transition-all">
                <span class="material-symbols-outlined">add</span>
                <span>Schedule Event</span>
              </a>
            </div>
          </div>
        <?php endif; ?>

        <!-- Schedule New Event Card -->
        <?php if(Auth::user()->role === 'admin'): ?>
          <div class="border-2 border-dashed border-outline-variant/30 rounded-2xl flex flex-col items-center justify-center p-12 text-center group cursor-pointer hover:border-primary/40 hover:bg-primary/5 transition-all duration-300">
            <a href="<?php echo e(route('schedule-event')); ?>" class="w-full h-full flex flex-col items-center justify-center">
              <div class="w-16 h-16 rounded-full bg-surface-container-high flex items-center justify-center mb-6 group-hover:bg-primary group-hover:text-on-primary transition-all duration-300">
                <span class="material-symbols-outlined text-3xl">add</span>
              </div>
              <p class="font-headline font-bold text-primary mb-1">Schedule New Event</p>
              <p class="text-xs text-on-surface-variant">Click to start planning the next event</p>
            </a>
          </div>
        <?php endif; ?>
      </div>

      <!-- Pagination Links -->
      <?php if($events->hasPages()): ?>
        <div class="mt-12 mb-16">
          <nav class="flex flex-col md:flex-row items-center justify-center gap-2">
            <?php if($events->onFirstPage()): ?>
              <span class="px-3 py-2 text-sm text-on-surface-variant cursor-not-allowed opacity-50">← Previous</span>
            <?php else: ?>
              <a href="<?php echo e($events->previousPageUrl()); ?>" class="px-3 py-2 text-sm text-primary font-bold hover:bg-primary/10 rounded-lg transition-all">← Previous</a>
            <?php endif; ?>

            <div class="flex gap-1">
              <?php $__currentLoopData = $events->getUrlRange(1, $events->lastPage()); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $page => $url): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php if($page == $events->currentPage()): ?>
                  <span class="px-3 py-2 text-sm font-bold text-on-primary bg-primary rounded-lg"><?php echo e($page); ?></span>
                <?php else: ?>
                  <a href="<?php echo e($url); ?>" class="px-3 py-2 text-sm text-primary hover:bg-primary/10 rounded-lg transition-all"><?php echo e($page); ?></a>
                <?php endif; ?>
              <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>

            <?php if($events->hasMorePages()): ?>
              <a href="<?php echo e($events->nextPageUrl()); ?>" class="px-3 py-2 text-sm text-primary font-bold hover:bg-primary/10 rounded-lg transition-all">Next →</a>
            <?php else: ?>
              <span class="px-3 py-2 text-sm text-on-surface-variant cursor-not-allowed opacity-50">Next →</span>
            <?php endif; ?>
          </nav>
        </div>
      <?php endif; ?>

      <!-- Pagination/Footer Context -->
      <div class="mt-24 pt-12 border-t border-surface-container-high flex flex-col items-center text-center">
        <div class="w-12 h-1 bg-secondary-container mb-8"></div>
        <p class="text-on-surface-variant text-sm font-medium mb-2">STI College Balagtas Event Attendance Management System.</p>
        <p class="text-primary font-black font-headline tracking-widest uppercase text-[10px]">STI COLLEGE BALAGTAS</p>
      </div>
    </main>

    <!-- Contextual FAB -->
    <a href="<?php echo e(route('schedule-event')); ?>" class="fixed bottom-10 right-10 w-16 h-16 bg-primary text-on-primary rounded-full shadow-2xl flex items-center justify-center hover:scale-110 active:scale-95 transition-all duration-300 z-50">
      <span class="material-symbols-outlined text-3xl" style="font-variation-settings: 'FILL' 1;">calendar_add_on</span>
    </a>

  </body>

  <!-- Lucide Icon Initialization -->
  <script>
    /**
     * Load session manager info for all events
     */
    async function loadSessionManagersForAllEvents() {
      const managedBySections = document.querySelectorAll('.managed-by-section');
      
      for (const section of managedBySections) {
        const eventId = section.getAttribute('data-event-id');
        try {
          const response = await fetch(`/api/event/${eventId}/active-session-manager`);
          
          if (response.ok) {
            const data = await response.json();
            
            if (data.success && data.manager) {
              // Show the section and update manager info
              section.classList.remove('hidden');
              section.querySelector('.manager-name').textContent = data.manager.name;
              section.querySelector('.manager-email').textContent = data.manager.email;
            }
          }
          // Silently ignore 404 and other non-ok responses (no active session)
        } catch (error) {
          // Silently ignore fetch errors
        }
      }
    }

    // Initialize everything on DOM load
    document.addEventListener('DOMContentLoaded', function() {
      // Initialize progress bars with data attributes
      const progressBars = document.querySelectorAll('.progress-bar');
      progressBars.forEach(bar => {
        const percentage = bar.getAttribute('data-percentage');
        bar.style.width = percentage + '%';
      });

      // Load session manager info for each event
      loadSessionManagersForAllEvents();

      // Initialize filter functionality
      // Get all filter buttons and event cards
      const filterButtons = document.querySelectorAll('[data-filter]');
      const eventCards = document.querySelectorAll('.event-card');

      // Add click event listeners to filter buttons
      filterButtons.forEach(button => {
        button.addEventListener('click', function(e) {
          e.preventDefault();
          const filter = this.getAttribute('data-filter');

          // Update active button styling
          filterButtons.forEach(btn => {
            btn.classList.remove('bg-surface-container-lowest', 'shadow-sm', 'text-primary');
            btn.classList.add('text-on-surface-variant');
          });
          
          this.classList.add('bg-surface-container-lowest', 'shadow-sm', 'text-primary');
          this.classList.remove('text-on-surface-variant');

          // Filter and display events based on status
          eventCards.forEach(card => {
            const status = card.getAttribute('data-status');
            let shouldShow = false;

            if (filter === 'all') {
              shouldShow = true;
            } else if (filter === 'active') {
              shouldShow = status === 'live' || status === 'upcoming';
            } else if (filter === 'history') {
              shouldShow = status === 'completed';
            } else if (filter === 'archive') {
              // Archive filter is handled by openArchivesModal(), don't filter here
              return;
            }

            if (shouldShow) {
              card.classList.remove('hidden');
              card.style.display = '';
            } else {
              card.classList.add('hidden');
              card.style.display = 'none';
            }
          });
        });
      });

      // Close event menu when clicking outside
      document.addEventListener('click', function(e) {
        if (!e.target.closest('.event-menu-btn') && !e.target.closest('.event-menu')) {
          document.querySelectorAll('.event-menu').forEach(menu => {
            menu.classList.add('hidden');
          });
        }
      });
    });

    /**
     * Open archives modal and fetch archived events
     */
    function openArchivesModal() {
      const modal = document.createElement('div');
      modal.id = 'archivesModal';
      modal.className = 'fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-[9999] p-4';
      modal.innerHTML = `
        <div class="bg-surface rounded-2xl shadow-2xl w-full max-w-2xl max-h-[80vh] overflow-y-auto border border-outline">
          <div class="sticky top-0 bg-primary text-on-primary p-8 border-b border-outline/20">
            <div class="flex items-center justify-between">
              <h2 class="text-2xl font-bold font-headline">Archived Events</h2>
              <button onclick="document.getElementById('archivesModal')?.remove()" class="text-on-primary hover:opacity-80 transition-opacity">
                <span class="material-symbols-outlined text-2xl">close</span>
              </button>
            </div>
            <p class="text-sm opacity-90 mt-1">Events will be permanently deleted after 15 days</p>
          </div>
          
          <div id="archivesList" class="p-8 space-y-4">
            <div class="text-center py-12 text-on-surface-variant">
              <span class="material-symbols-outlined text-4xl opacity-50 block mb-2">hourglass_empty</span>
              <p>Loading archived events...</p>
            </div>
          </div>
        </div>
      `;
      
      document.body.appendChild(modal);
      
      // Close on outside click
      modal.addEventListener('click', function(e) {
        if (e.target === modal) {
          modal.remove();
        }
      });
      
      // Fetch archived events
      loadArchivedEvents();
    }

    /**
     * Format an archived event date string to a short, readable date.
     */
    function formatArchivedEventDate(value) {
      if (!value) return '';
      const date = new Date(value);
      if (Number.isNaN(date.getTime())) return value;
      return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
    }

    /**
     * Format an archived event time string without timezone text.
     */
    function formatArchivedEventTime(value) {
      if (!value) return '';
      const date = new Date(value);
      if (!Number.isNaN(date.getTime())) {
        return date.toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit', hour12: true });
      }
      return value.replace(/UTC/i, '').trim();
    }

    /**
     * Load archived events from server
     */
    function loadArchivedEvents() {
      fetch('/api/archived-events')
        .then(response => response.json())
        .then(data => {
          const archivesList = document.getElementById('archivesList');
          
          if (!data.success || !data.archivedEvents || data.archivedEvents.length === 0) {
            archivesList.innerHTML = `
              <div class="text-center py-12 text-on-surface-variant">
                <span class="material-symbols-outlined text-4xl opacity-50 block mb-2">inbox</span>
                <p>No archived events</p>
              </div>
            `;
            return;
          }
          
          archivesList.innerHTML = data.archivedEvents.map(event => {
            const daysRemaining = Math.ceil(event.daysRemaining);
            const deleteWarning = daysRemaining <= 3 ? `<p class="text-xs text-error font-semibold mt-2">⚠️ Only ${daysRemaining} days until permanent deletion!</p>` : '';
            const progressPercent = ((15 - daysRemaining) / 15) * 100;
            
            return `
              <div class="bg-surface-container-low rounded-xl p-6 border border-outline-variant/50 hover:border-outline-variant transition-colors">
                <div class="flex justify-between items-start mb-4">
                  <div class="flex-1">
                    <h3 class="font-bold text-lg text-on-surface">${event.e_name}</h3>
                    <p class="text-sm text-on-surface-variant mt-1">
                      <span class="material-symbols-outlined text-base align-middle inline">calendar_today</span>
                      ${formatArchivedEventDate(event.start_date)}${event.start_time ? ` • ${formatArchivedEventTime(event.start_time)}` : ''}
                    </p>
                  </div>
                  <div class="flex gap-2">
                    <button onclick="unarchiveEvent('${event.e_id}')" class="px-4 py-2 bg-primary hover:bg-blue-700 text-on-primary font-semibold rounded-lg text-sm transition-colors">
                      Restore
                    </button>
                    <button onclick="deleteEvent('${event.e_id}')" class="px-4 py-2 bg-error hover:bg-red-700 text-on-error font-semibold rounded-lg text-sm transition-colors flex items-center gap-1">
                      <span class="material-symbols-outlined text-base">delete</span>
                      Delete
                    </button>
                  </div>
                </div>
                
                <div class="space-y-3">
                  <div class="flex justify-between items-center text-sm">
                    <span class="text-on-surface-variant">Auto-delete in:</span>
                    <span class="font-bold text-on-surface">${daysRemaining} days</span>
                  </div>
                  <div class="w-full h-2 bg-surface-container rounded-full overflow-hidden">
                    <div class="h-full bg-yellow-500 rounded-full" style="width: ${progressPercent}%"></div>
                  </div>
                  ${deleteWarning}
                </div>
              </div>
            `;
          }).join('');
        })
        .catch(error => {
          console.error('Error loading archived events:', error);
          const archivesList = document.getElementById('archivesList');
          archivesList.innerHTML = `
            <div class="text-center py-12 text-error">
              <p>Failed to load archived events</p>
            </div>
          `;
        });
    }

    /**
     * Unarchive an event
     */
    function unarchiveEvent(eventId) {
      const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
      
      if (!confirm('Restore this event from archive?')) return;
      
      fetch(`/event/${eventId}/unarchive`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': csrfToken
        },
        body: JSON.stringify({ event_id: eventId })
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          showToast('Event restored successfully!', 'success');
          loadArchivedEvents();
          setTimeout(() => {
            location.reload();
          }, 1000);
        } else {
          showToast(data.message || 'Failed to restore event', 'error');
        }
      })
      .catch(error => {
        console.error('Error:', error);
        showToast('An error occurred while restoring the event', 'error');
      });
    }
    function toggleEventMenu(button) {
      const menu = button.closest('.relative').querySelector('.event-menu');
      if (menu) {
        menu.classList.toggle('hidden');
      }
    }

    /**
     * Archive event - moves to archive and auto-deletes after 15 days
     */
    function archiveEvent(eventId) {
      const confirmDialog = document.createElement('div');
      confirmDialog.className = 'fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-[9999]';
      confirmDialog.innerHTML = `
        <div class="bg-surface rounded-2xl shadow-2xl p-8 max-w-md w-full mx-4 border border-outline">
          <div class="flex items-center gap-3 mb-6">
            <div class="w-12 h-12 rounded-full bg-secondary-fixed/20 flex items-center justify-center">
              <span class="material-symbols-outlined text-secondary-fixed text-xl">archive</span>
            </div>
            <div>
              <h2 class="text-lg font-bold text-on-surface">Archive Event?</h2>
              <p class="text-sm text-on-surface-variant">This action can be undone</p>
            </div>
          </div>
          
          <div class="bg-surface-container-low p-4 rounded-lg mb-6 text-sm text-on-surface-variant">
            <p class="mb-2"><strong>What happens when you archive:</strong></p>
            <ul class="list-disc list-inside space-y-1">
              <li>Event will be moved to archive</li>
              <li>No longer visible in active events</li>
              <li>Auto-deleted after 15 days</li>
              <li>Can be restored before deletion</li>
            </ul>
          </div>
          
          <div class="flex gap-3">
            <button onclick="this.closest('.fixed').remove()" class="flex-1 px-4 py-2 bg-surface-container hover:bg-surface-container-high text-on-surface font-bold rounded-lg transition-colors">
              Cancel
            </button>
            <button onclick="confirmArchiveEvent('${eventId}')" class="flex-1 px-4 py-2 bg-secondary-fixed hover:bg-secondary-fixed-dim text-on-secondary-fixed font-bold rounded-lg transition-colors">
              Archive
            </button>
          </div>
        </div>
      `;
      
      document.body.appendChild(confirmDialog);
    }

    /**
     * Confirm archive event
     */
    function confirmArchiveEvent(eventId) {
      const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
      
      fetch(`/event/${eventId}/archive`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': csrfToken
        },
        body: JSON.stringify({ event_id: eventId })
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          showToast('Event archived successfully! It will be permanently deleted after 15 days.', 'success');
          setTimeout(() => {
            location.reload();
          }, 1500);
        } else {
          showToast(data.message || 'Failed to archive event', 'error');
        }
        document.querySelectorAll('.fixed').forEach(el => {
          if (el.querySelector('.bg-surface')) el.remove();
        });
      })
      .catch(error => {
        console.error('Error:', error);
        showToast('An error occurred while archiving the event', 'error');
        document.querySelectorAll('.fixed').forEach(el => {
          if (el.querySelector('.bg-surface')) el.remove();
        });
      });
    }

    /**
     * Delete event permanently
     */
    function deleteEvent(eventId) {
      const confirmDialog = document.createElement('div');
      confirmDialog.className = 'fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-[9999]';
      confirmDialog.innerHTML = `
        <div class="bg-surface rounded-2xl shadow-2xl p-8 max-w-md w-full mx-4 border border-outline">
          <div class="flex items-center gap-3 mb-6">
            <div class="w-12 h-12 rounded-full bg-error/20 flex items-center justify-center">
              <span class="material-symbols-outlined text-error text-xl" style="font-variation-settings: 'FILL' 1;">delete</span>
            </div>
            <div>
              <h2 class="text-lg font-bold text-error">Delete Event Permanently?</h2>
              <p class="text-sm text-on-surface-variant">This cannot be undone</p>
            </div>
          </div>
          
          <div class="bg-error/10 p-4 rounded-lg mb-6 text-sm border border-error/20">
            <p class="font-bold text-error mb-2">⚠️ WARNING - PERMANENT DELETION</p>
            <p class="text-on-surface-variant mb-3">Deleting this event will permanently remove:</p>
            <ul class="list-disc list-inside space-y-1 text-on-surface-variant">
              <li>All event details and information</li>
              <li>All attendance records for all students</li>
              <li>All session data and schedules</li>
              <li>All related files and documents</li>
            </ul>
            <p class="text-error font-bold mt-3">This action cannot be reversed!</p>
          </div>
          
          <div class="mb-4">
            <label class="flex items-center gap-2 cursor-pointer">
              <input type="checkbox" id="deleteConfirm" class="w-4 h-4 rounded border border-outline cursor-pointer">
              <span class="text-sm text-on-surface-variant">I understand that all data will be permanently deleted</span>
            </label>
          </div>
          
          <div class="flex gap-3">
            <button onclick="this.closest('.fixed').remove()" class="flex-1 px-4 py-2 bg-surface-container hover:bg-surface-container-high text-on-surface font-bold rounded-lg transition-colors">
              Cancel
            </button>
            <button onclick="confirmDeleteEvent('${eventId}')" id="confirmDeleteBtn" disabled class="flex-1 px-4 py-2 bg-error hover:bg-error text-on-error font-bold rounded-lg transition-colors opacity-50 cursor-not-allowed disabled:opacity-50" disabled>
              Delete All Data
            </button>
          </div>
        </div>
      `;
      
      document.body.appendChild(confirmDialog);
      
      // Enable/disable delete button based on checkbox
      const checkbox = confirmDialog.querySelector('#deleteConfirm');
      const deleteBtn = confirmDialog.querySelector('#confirmDeleteBtn');
      
      checkbox.addEventListener('change', function() {
        if (this.checked) {
          deleteBtn.disabled = false;
          deleteBtn.classList.remove('opacity-50', 'cursor-not-allowed');
        } else {
          deleteBtn.disabled = true;
          deleteBtn.classList.add('opacity-50', 'cursor-not-allowed');
        }
      });
    }

    /**
     * Confirm delete event
     */
    function confirmDeleteEvent(eventId) {
      const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
      
      const deleteBtn = document.querySelector('#confirmDeleteBtn');
      if (deleteBtn) {
        deleteBtn.disabled = true;
        deleteBtn.innerHTML = '<span style="display: inline-block; width: 16px; height: 16px; border: 2px solid currentColor; border-top-color: transparent; border-radius: 50%; animation: spin 0.6s linear infinite;"></span>';
      }
      
      fetch(`/event/${eventId}`, {
        method: 'DELETE',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': csrfToken
        },
        body: JSON.stringify({ event_id: eventId })
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          showToast('Event and all related data deleted permanently!', 'success');
          setTimeout(() => {
            location.reload();
          }, 1500);
        } else {
          showToast(data.message || 'Failed to delete event', 'error');
          if (deleteBtn) {
            deleteBtn.disabled = false;
            deleteBtn.innerHTML = 'Delete All Data';
          }
        }
        document.querySelectorAll('.fixed').forEach(el => {
          if (el.querySelector('.bg-surface')) el.remove();
        });
      })
      .catch(error => {
        console.error('Error:', error);
        showToast('An error occurred while deleting the event', 'error');
        if (deleteBtn) {
          deleteBtn.disabled = false;
          deleteBtn.innerHTML = 'Delete All Data';
        }
        document.querySelectorAll('.fixed').forEach(el => {
          if (el.querySelector('.bg-surface')) el.remove();
        });
      });
    }

    /**
     * Show toast notification
     */
    function showToast(message, type = 'success') {
      const bgColor = type === 'success' ? 'bg-secondary-fixed' : 'bg-error';
      const textColor = type === 'success' ? 'text-on-secondary-fixed' : 'text-on-error';
      const icon = type === 'success' ? '✓' : '✕';
      
      const toast = document.createElement('div');
      toast.className = `fixed top-6 right-6 ${bgColor} ${textColor} px-6 py-4 rounded-lg shadow-xl z-[10000] flex items-center gap-3 animate-fadeIn`;
      toast.innerHTML = `
        <span class="font-bold text-lg">${icon}</span>
        <span class="font-medium">${message}</span>
      `;
      document.body.appendChild(toast);
      
      setTimeout(() => {
        toast.style.opacity = '0';
        toast.style.transition = 'opacity 0.3s';
        setTimeout(() => toast.remove(), 300);
      }, 3000);
    }

    // Add CSS for loading spinner animation if not already present
    if (!document.querySelector('style[data-spinner]')) {
      const style = document.createElement('style');
      style.setAttribute('data-spinner', 'true');
      style.innerHTML = `
        @keyframes spin {
          to { transform: rotate(360deg); }
        }
        @keyframes fadeIn {
          from { opacity: 0; transform: translateY(-10px); }
          to { opacity: 1; transform: translateY(0); }
        }
        .animate-fadeIn {
          animation: fadeIn 0.3s ease-out;
        }
      `;
      document.head.appendChild(style);
    }
  </script>

</html>
<?php /**PATH C:\AttendanceEvent\resources\views/dashboard.blade.php ENDPATH**/ ?>