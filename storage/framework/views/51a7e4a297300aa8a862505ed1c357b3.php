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

            <form id="eventForm" method="POST" action="<?php echo e(route('schedule-event.store')); ?>" class="space-y-8">
              <?php echo csrf_field(); ?>

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

              <!-- Times Grid -->
              <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="space-y-2">
                  <label class="block text-sm font-bold text-on-surface" for="start_time">Start Time</label>
                  <input 
                    class="w-full bg-surface-container-low border-none focus:ring-2 focus:ring-primary/40 rounded-xl px-5 py-4 text-sm" 
                    id="start_time" 
                    name="start_time"
                    type="time"
                    required
                  />
                </div>
                <div class="space-y-2">
                  <label class="block text-sm font-bold text-on-surface" for="end_time">End Time</label>
                  <input 
                    class="w-full bg-surface-container-low border-none focus:ring-2 focus:ring-primary/40 rounded-xl px-5 py-4 text-sm" 
                    id="end_time" 
                    name="end_time"
                    type="time"
                    required
                  />
                </div>
              </div>

              <!-- Meta Grid -->
              <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="space-y-2">
                  <label class="block text-sm font-bold text-on-surface" for="sessions">Number of Sessions</label>
                  <input 
                    class="w-full bg-surface-container-low border-none focus:ring-2 focus:ring-primary/40 rounded-xl px-5 py-4 text-sm" 
                    id="sessions" 
                    name="sessions"
                    placeholder="1" 
                    type="number"
                    min="1"
                    required
                  />
                </div>
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
                  href="<?php echo e(route('dashboard')); ?>" 
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
      const startTime = document.getElementById('start_time').value;
      const endTime = document.getElementById('end_time').value;
      const location = document.getElementById('location').value;

      if (startDate && startTime) {
        document.getElementById('summaryDate').textContent = `${startDate} ${startTime} - ${endTime}`;
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
        updateSummary();
      });
      
      document.getElementById('end_date').addEventListener('change', function() {
        validateEndDate();
        updateSummary();
      });
    });

    // Add event listeners for form inputs
    ['start_time', 'end_time', 'location'].forEach(id => {
      document.getElementById(id).addEventListener('change', updateSummary);
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
          window.location.href = '<?php echo e(route("dashboard")); ?>';
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
<?php /**PATH C:\AttendanceEvent\resources\views/schedule-event.blade.php ENDPATH**/ ?>