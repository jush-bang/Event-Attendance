<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <title>Schedule New Event | STI Balagtas Admin</title>

    <!-- Fonts -->
    <link href="https://fonts.googleapis.com" rel="preconnect" />
    <link crossorigin="" href="https://fonts.gstatic.com" rel="preconnect" />
    <link href="https://fonts.googleapis.com/css2?family=Work+Sans:wght@400;600;700;800;900&family=Inter:wght@400;500;600&display=swap" rel="stylesheet" />
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

    <!-- Custom Styles -->
    <style>
      body { font-family: 'Inter', sans-serif; background-color: #f9f9fb; color: #1a1c1d; }
      .font-headline { font-family: 'Work Sans', sans-serif; }
      .material-symbols-outlined { font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24; }
    </style>
  </head>

  <body class="bg-surface text-on-surface">
    <!-- Shared Header Component -->
    <x-header />

    <!-- Title Section -->
    <div class="w-full bg-surface px-10 pt-10 pb-6">
      <div class="max-w-7xl mx-auto flex flex-col gap-3">
        <h1 class="text-4xl font-black tracking-tight text-primary font-headline">Schedule New Event</h1>
        <div class="h-1 w-20 bg-secondary-container rounded-full mt-2"></div>
      </div>
    </div>

    <!-- Main Content Canvas -->
    <main class="max-w-7xl mx-auto px-10 py-4 pb-20">
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 items-start">
        <!-- Left Column: Event Details Form -->
        <div class="lg:col-span-2">
          <section class="bg-white rounded-xl p-10 shadow-sm border border-outline-variant/10">
            <div class="flex items-center gap-4 mb-10">
              <div class="p-3 bg-primary/5 rounded-xl">
                <span class="material-symbols-outlined text-primary text-2xl" style="font-variation-settings: 'FILL' 1;">calendar_add_on</span>
              </div>
              <h2 class="text-2xl font-bold text-primary font-headline">Event Details</h2>
            </div>

            <form id="eventForm" method="POST" action="{{ route('schedule-event.store') }}" class="space-y-8">
              @csrf

              <!-- Event Title -->
              <div class="space-y-2">
                <label class="block text-sm font-bold text-on-surface" for="event_title">Event Title</label>
                <input 
                  class="w-full bg-surface-container-low border-none focus:ring-2 focus:ring-primary/40 rounded-xl px-5 py-4 placeholder:text-on-surface-variant/40 text-sm" 
                  id="event_title" 
                  name="event_title"
                  placeholder="e.g., Annual Sports Fest 2024" 
                  type="text"
                  required
                />
              </div>

              <!-- Dates Grid -->
              <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="space-y-2">
                  <label class="block text-sm font-bold text-on-surface" for="start_date">Start Date</label>
                  <input 
                    class="w-full bg-surface-container-low border-none focus:ring-2 focus:ring-primary/40 rounded-xl px-5 py-4 text-sm transition-all" 
                    id="start_date" 
                    name="start_date"
                    type="date"
                    required
                  />
                  <p id="startDateError" class="text-xs text-error font-semibold hidden">Start date cannot be in the past</p>
                </div>
                <div class="space-y-2">
                  <label class="block text-sm font-bold text-on-surface" for="end_date">End Date</label>
                  <input 
                    class="w-full bg-surface-container-low border-none focus:ring-2 focus:ring-primary/40 rounded-xl px-5 py-4 text-sm transition-all" 
                    id="end_date" 
                    name="end_date"
                    type="date"
                    required
                  />
                  <p id="endDateError" class="text-xs text-error font-semibold hidden">End date must be after start date</p>
                </div>
              </div>

              <!-- Session schedule is defined per session below -->

              <!-- Meta Grid -->
              <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="space-y-2">
                  <label class="block text-sm font-bold text-on-surface" for="location">Room / Venue</label>
                  <input 
                    class="w-full bg-surface-container-low border-none focus:ring-2 focus:ring-primary/40 rounded-xl px-5 py-4 text-sm" 
                    id="location" 
                    name="location"
                    placeholder="Main Auditorium, Hall B, etc." 
                    type="text"
                    required
                  />
                </div>
              </div>

              <!-- Day-by-Day Sessions Configuration -->
              <div id="sessionsContainer" class="space-y-6">
                <!-- Sessions for each day will be generated here -->
              </div>

              <!-- Checkbox Container -->
              <div class="bg-surface-container-low/50 p-6 rounded-xl border border-outline-variant/10">
                <label class="flex items-center gap-4 cursor-pointer group">
                  <input 
                    id="require_action_prompts"
                    name="require_action_prompts" 
                    type="checkbox" 
                    class="h-6 w-6 rounded border-outline-variant text-primary focus:ring-primary/30"
                  />
                  <span class="text-base font-medium text-on-surface-variant group-hover:text-on-surface transition-colors">Require in and out during event (RFID Tracking)</span>
                </label>
              </div>
            </form>
          </section>
        </div>

        <!-- Right Column: Schedule Overview -->
        <aside class="space-y-6 lg:sticky lg:top-28">
          <div class="bg-white rounded-xl shadow-lg border border-outline-variant/10 overflow-hidden">
            <div class="p-8 bg-primary text-on-primary">
              <h3 class="font-bold text-xl mb-1 font-headline">Schedule Overview</h3>
              <p class="text-sm opacity-70">Review your event summary before saving</p>
            </div>
            <div class="p-8 space-y-8">
              <div class="space-y-6">
                <div class="flex gap-5 items-start">
                  <div class="w-12 h-12 rounded-xl bg-surface-container-low flex items-center justify-center flex-shrink-0">
                    <span class="material-symbols-outlined text-primary" style="font-variation-settings: 'FILL' 1;">schedule</span>
                  </div>
                  <div>
                    <p class="text-[10px] uppercase tracking-widest font-black text-on-surface-variant/60 mb-1">Date &amp; Time</p>
                    <p class="text-base font-bold text-on-surface" id="summaryDate">Pending Selection</p>
                  </div>
                </div>
                <div class="flex gap-5 items-start">
                  <div class="w-12 h-12 rounded-xl bg-surface-container-low flex items-center justify-center flex-shrink-0">
                    <span class="material-symbols-outlined text-primary" style="font-variation-settings: 'FILL' 1;">location_on</span>
                  </div>
                  <div>
                    <p class="text-[10px] uppercase tracking-widest font-black text-on-surface-variant/60 mb-1">Venue</p>
                    <p class="text-base font-bold text-on-surface" id="summaryVenue">Not Specified</p>
                  </div>
                </div>
              </div>
              <div class="pt-6 border-t border-outline-variant/10 space-y-3">
                <button 
                  type="submit" 
                  form="eventForm" 
                  class="w-full bg-primary-container hover:bg-primary text-on-primary font-bold py-5 rounded-xl shadow-md transition-all active:scale-95 flex items-center justify-center gap-3 border-2 border-secondary-container font-headline"
                >
                  <span class="material-symbols-outlined">save</span>
                  Save Event
                </button>
                <a 
                  href="{{ route('dashboard') }}" 
                  class="w-full bg-surface-container-low hover:bg-surface-container-high text-on-surface font-bold py-5 rounded-xl transition-all active:scale-95 text-center block font-headline"
                >
                  Cancel
                </a>
              </div>
            </div>
          </div>

          <!-- Info Box -->
          <div class="bg-primary/5 p-6 rounded-xl border-l-4 border-primary flex gap-4">
            <span class="material-symbols-outlined text-primary flex-shrink-0" style="font-variation-settings: 'FILL' 1;">info</span>
            <p class="text-sm text-on-surface-variant leading-relaxed">
              Events saved here will appear immediately on the admin dashboard and student mobile applications. Make sure to double-check the venue availability.
            </p>
          </div>
        </aside>
      </div>
    </main>

    <!-- Footer -->
    <footer class="py-12 px-10 border-t border-outline-variant/10 text-center">
      <p class="text-sm text-on-surface-variant opacity-60">© 2024 STI Balagtas Administrative System. All Rights Reserved.</p>
    </footer>
  </body>

  <!-- JavaScript for functionality -->
  <script>
    // Calculate number of days between two dates
    function calculateDays(startDate, endDate) {
      const start = new Date(startDate);
      const end = new Date(endDate);
      const diffTime = Math.abs(end - start);
      const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24)) + 1;
      return diffDays;
    }

    // Generate day-by-day session configuration inputs
    function getExistingSessionValues() {
      const values = {};
      document.querySelectorAll('[data-day][data-session]').forEach(input => {
        const day = input.dataset.day;
        const session = input.dataset.session;
        if (!values[day]) {
          values[day] = {};
        }
        if (!values[day][session]) {
          values[day][session] = { start: '', end: '' };
        }

        if (input.name.includes('day_session_start_time')) {
          values[day][session].start = input.value;
        }

        if (input.name.includes('day_session_end_time')) {
          values[day][session].end = input.value;
        }
      });
      return values;
    }

    function generateSessionInputs() {
      const startDate = document.getElementById('start_date').value;
      const endDate = document.getElementById('end_date').value;
      const container = document.getElementById('sessionsContainer');
      const existingValues = getExistingSessionValues();

      if (!startDate || !endDate) {
        container.innerHTML = '';
        return;
      }

      const numDays = calculateDays(startDate, endDate);
      const start = new Date(startDate);
      let html = '<div class="space-y-4">';
      html += '<h3 class="text-lg font-bold text-on-surface">Sessions Per Day</h3>';

      for (let i = 0; i < numDays; i++) {
        const currentDay = new Date(start);
        currentDay.setDate(currentDay.getDate() + i);
        const dayName = currentDay.toLocaleDateString('en-US', { weekday: 'long', month: 'short', day: 'numeric' });
        const dayNum = i + 1;
        const existingCountInput = document.querySelector(`[name="day_sessions[${dayNum}]"]`);
        const sessionCount = existingCountInput ? Math.max(1, parseInt(existingCountInput.value) || 1) : 1;

        html += `
          <div class="bg-surface-container-low/50 p-6 rounded-xl border border-outline-variant/20">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-4">
              <div>
                <h4 class="font-bold text-on-surface">Day ${dayNum} - ${dayName}</h4>
                <p class="text-xs text-on-surface-variant">Define each session time separately</p>
              </div>
              <div class="w-full sm:w-40">
                <label class="block text-sm font-bold text-on-surface" for="day_sessions_${dayNum}">Sessions</label>
                <input
                  id="day_sessions_${dayNum}"
                  type="number"
                  name="day_sessions[${dayNum}]"
                  value="${sessionCount}"
                  min="1"
                  class="w-full bg-surface-container-low border-none focus:ring-2 focus:ring-primary/40 rounded-lg px-4 py-3 text-sm"
                  data-day="${dayNum}"
                />
              </div>
            </div>
            <div class="space-y-4" id="day-${dayNum}-sessions">
        `;

        for (let session = 1; session <= sessionCount; session++) {
          const stored = existingValues[dayNum] && existingValues[dayNum][session] ? existingValues[dayNum][session] : { start: '', end: '' };
          html += `
            <div class="bg-white border border-outline-variant/10 rounded-2xl p-4 shadow-sm">
              <div class="flex items-center justify-between gap-4 mb-4">
                <span class="font-semibold text-on-surface">Session ${session}</span>
                <span class="text-xs uppercase tracking-[0.2em] text-on-surface-variant">Day ${dayNum}</span>
              </div>
              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="space-y-2">
                  <label class="block text-sm font-bold text-on-surface">Start Time</label>
                  <input
                    type="time"
                    name="day_session_start_time[${dayNum}][${session}]"
                    value="${stored.start}"
                    required
                    class="w-full bg-surface-container-low border-none focus:ring-2 focus:ring-primary/40 rounded-lg px-4 py-3 text-sm"
                    data-day="${dayNum}"
                    data-session="${session}"
                  />
                </div>
                <div class="space-y-2">
                  <label class="block text-sm font-bold text-on-surface">End Time</label>
                  <input
                    type="time"
                    name="day_session_end_time[${dayNum}][${session}]"
                    value="${stored.end}"
                    required
                    class="w-full bg-surface-container-low border-none focus:ring-2 focus:ring-primary/40 rounded-lg px-4 py-3 text-sm"
                    data-day="${dayNum}"
                    data-session="${session}"
                  />
                </div>
              </div>
            </div>
          `;
        }

        html += '</div></div>';
      }

      html += '</div>';
      container.innerHTML = html;

      attachSessionInputListeners();
    }

    function attachSessionInputListeners() {
      document.querySelectorAll('[name^="day_sessions"], [name^="day_session_start_time"], [name^="day_session_end_time"]').forEach(input => {
        input.addEventListener('change', function() {
          if (this.name.startsWith('day_sessions')) {
            generateSessionInputs();
          }
          updateSummary();
        });
      });
    }

    // Set minimum dates to today for date inputs
    function initializeDateValidation() {
      const today = new Date();
      const todayString = today.toISOString().split('T')[0];
      
      const startDateInput = document.getElementById('start_date');
      const endDateInput = document.getElementById('end_date');
      
      // Set min attribute to today
      startDateInput.min = todayString;
      endDateInput.min = todayString;
    }

    // Validate start date (cannot be in the past)
    function validateStartDate() {
      const startDateInput = document.getElementById('start_date');
      const startDateError = document.getElementById('startDateError');
      const today = new Date();
      const todayString = today.toISOString().split('T')[0];
      
      if (startDateInput.value && startDateInput.value < todayString) {
        startDateInput.classList.add('ring-2', 'ring-error', 'bg-error/5');
        startDateError.classList.remove('hidden');
        return false;
      } else {
        startDateInput.classList.remove('ring-2', 'ring-error', 'bg-error/5');
        startDateError.classList.add('hidden');
        return true;
      }
    }

    // Validate end date (cannot be before start date)
    function validateEndDate() {
      const startDateInput = document.getElementById('start_date');
      const endDateInput = document.getElementById('end_date');
      const endDateError = document.getElementById('endDateError');
      
      if (startDateInput.value && endDateInput.value && endDateInput.value < startDateInput.value) {
        endDateInput.classList.add('ring-2', 'ring-error', 'bg-error/5');
        endDateError.classList.remove('hidden');
        return false;
      } else {
        endDateInput.classList.remove('ring-2', 'ring-error', 'bg-error/5');
        endDateError.classList.add('hidden');
        return true;
      }
    }

    // Update summary from form
    function updateSummary() {
      const startDate = document.getElementById('start_date').value;
      const location = document.getElementById('location').value;
      const numDays = calculateDays(startDate, document.getElementById('end_date').value);
      
      let totalSessions = 0;
      let daysInfo = [];

      for (let day = 1; day <= numDays; day++) {
        const sessionsInput = document.querySelector(`[name="day_sessions[${day}]"]`);
        if (!sessionsInput) {
          continue;
        }

        const sessions = parseInt(sessionsInput.value) || 0;
        totalSessions += sessions;

        const sessionDetails = [];
        for (let session = 1; session <= sessions; session++) {
          const startTimeInput = document.querySelector(`[name="day_session_start_time[${day}][${session}]"]`);
          const endTimeInput = document.querySelector(`[name="day_session_end_time[${day}][${session}]"]`);
          if (startTimeInput && endTimeInput && startTimeInput.value && endTimeInput.value) {
            sessionDetails.push(`${startTimeInput.value} - ${endTimeInput.value}`);
          }
        }

        if (sessions > 0) {
          const sessionText = sessionDetails.length > 0 ? sessionDetails.join(', ') : 'Times pending';
          daysInfo.push(`Day ${day}: ${sessions} session${sessions > 1 ? 's' : ''} (${sessionText})`);
        }
      }

      if (startDate && totalSessions > 0) {
        document.getElementById('summaryDate').textContent = daysInfo.length > 0 ? daysInfo.join(' | ') : `${totalSessions} total session${totalSessions > 1 ? 's' : ''}`;
      } else {
        document.getElementById('summaryDate').textContent = 'Pending Selection';
      }
      
      document.getElementById('summaryVenue').textContent = location || 'Not Specified';
    }

    // Initialize date validation on page load
    document.addEventListener('DOMContentLoaded', function() {
      initializeDateValidation();
      
      // Add event listeners for date inputs
      document.getElementById('start_date').addEventListener('change', function() {
        validateStartDate();
        validateEndDate();
        generateSessionInputs();
        updateSummary();
      });
      
      document.getElementById('end_date').addEventListener('change', function() {
        validateEndDate();
        generateSessionInputs();
        updateSummary();
      });
      
      document.getElementById('location').addEventListener('change', updateSummary);

      // Generate initial session layout on page load if dates are present
      generateSessionInputs();
      updateSummary();
    });

    // Form submission
    document.getElementById('eventForm').addEventListener('submit', function(e) {
      e.preventDefault();
      
      // Validate dates before submission
      if (!validateStartDate() || !validateEndDate()) {
        alert('Please fix the date errors before submitting.');
        return;
      }
      
      // Create FormData object
      const formData = new FormData(this);
      
      // Handle checkbox value explicitly
      const requireActionCheckbox = document.getElementById('require_action_prompts');
      const checkboxValue = requireActionCheckbox.checked ? '1' : '0';
      formData.set('require_action_prompts', checkboxValue);
      
      console.log('[DEBUG] Checkbox checked:', requireActionCheckbox.checked);
      console.log('[DEBUG] Sending require_action_prompts:', checkboxValue);

      // Submit the form using fetch
      fetch(this.getAttribute('action'), {
        method: 'POST',
        body: formData,
        headers: {
          'X-Requested-With': 'XMLHttpRequest',
        }
      })
      .then(response => {
        if (response.ok) {
          window.location.href = '{{ route("dashboard") }}';
        } else if (response.status === 422) {
          return response.json().then(data => {
            let errors = '';
            if (data.errors) {
              errors = Object.values(data.errors).flat().join('\n');
            }
            alert('Validation error:\n' + errors);
          });
        } else {
          throw new Error('Server error');
        }
      })
      .catch(error => {
        console.error('Error:', error);
        console.error('Error message:', error.message);
        alert('An error occurred while saving the event: ' + (error?.message || 'Unknown error'));
      });
    });
  </script>

</html>
