<!-- Shared Header Component -->
<header class="bg-white border-b border-outline-variant/10 sticky top-0 z-50">
  <nav class="max-w-[1440px] mx-auto flex items-center justify-between px-4 sm:px-6 lg:px-10 h-16 sm:h-[72px] w-full">
    <!-- Logo Section -->
    <div class="flex items-center gap-2 sm:gap-4 flex-shrink-0">
      <img alt="STI Balagtas Logo" class="h-8 sm:h-10 w-auto" src="<?php echo e(asset('STI-LOGO.png')); ?>">
      <span class="text-lg sm:text-2xl font-black text-blue-900 dark:text-blue-100 tracking-tighter font-headline hidden sm:inline">STI Balagtas</span>
    </div>

    <!-- Desktop Navigation -->
    <div class="hidden lg:flex items-center gap-10">
      <div class="flex gap-8">
        <?php if(Route::currentRouteName() === 'dashboard'): ?>
          <a href="<?php echo e(route('dashboard')); ?>" class="text-blue-900 dark:text-blue-200 border-b-2 border-blue-900 dark:border-blue-400 pb-1 font-bold font-headline text-sm tracking-tight transition-all duration-300">Events</a>
        <?php else: ?>
          <a href="<?php echo e(route('dashboard')); ?>" class="text-on-surface-variant hover:text-primary transition-colors font-headline text-sm font-bold tracking-tight">Events</a>
        <?php endif; ?>
      </div>
      <?php if(auth()->user() && auth()->user()->role === 'admin'): ?>
        <?php if(Route::currentRouteName() === 'schedule-event'): ?>
          <a class="text-blue-900 dark:text-blue-200 border-b-2 border-blue-900 dark:border-blue-400 pb-1 font-bold font-headline text-sm tracking-tight transition-all duration-300">Schedule Event</a>
        <?php else: ?>
          <a href="<?php echo e(route('schedule-event')); ?>" class="text-on-surface-variant hover:text-primary transition-colors font-headline text-sm font-bold tracking-tight">Schedule Event</a>
        <?php endif; ?>
        <a href="<?php echo e(route('create-account')); ?>" class="text-on-surface-variant hover:text-primary transition-colors font-headline text-sm font-bold tracking-tight">Accounts</a>
      <?php endif; ?>
    </div>

    <!-- Desktop User Account Dropdown -->
    <div class="hidden lg:flex relative items-center gap-3" id="settingsDropdown">
      <span class="text-on-surface font-medium text-sm"><?php echo e(auth()->user()->name ?? 'User'); ?></span>
      <button id="settingsBtn" class="p-2 hover:bg-surface-container-low rounded-lg transition-colors text-on-surface-variant hover:text-primary">
        <span class="material-symbols-outlined text-2xl">expand_more</span>
      </button>

      <!-- Dropdown Menu -->
      <div id="settingsMenu" class="hidden absolute right-0 mt-2 w-48 bg-surface-container-lowest border border-outline-variant/20 rounded-lg shadow-lg z-50">
        <form method="POST" action="<?php echo e(route('logout')); ?>" class="block">
          <?php echo csrf_field(); ?>
          <button type="submit" class="w-full flex items-center gap-3 px-4 py-3 hover:bg-surface-container transition-colors text-on-surface font-medium text-sm text-left hover:text-error">
            <span class="material-symbols-outlined text-lg">logout</span>
            <span>Logout</span>
          </button>
        </form>
      </div>
    </div>

    <!-- Mobile Menu Button -->
    <button id="mobileMenuBtn" class="lg:hidden p-2 hover:bg-surface-container-low rounded-lg transition-colors text-on-surface-variant hover:text-primary">
      <span class="material-symbols-outlined text-2xl">menu</span>
    </button>
  </nav>

  <!-- Mobile Navigation Menu -->
  <div id="mobileMenu" class="hidden lg:hidden border-t border-outline-variant/10 bg-white dark:bg-surface">
    <div class="px-4 py-4 space-y-4">
      <!-- Mobile Navigation Items -->
      <div class="space-y-3">
        <?php if(Route::currentRouteName() === 'dashboard'): ?>
          <a href="<?php echo e(route('dashboard')); ?>" class="block text-blue-900 dark:text-blue-200 font-bold font-headline text-sm tracking-tight transition-all duration-300 pb-2 border-b-2 border-blue-900 dark:border-blue-400">Events</a>
        <?php else: ?>
          <a href="<?php echo e(route('dashboard')); ?>" class="block text-on-surface-variant hover:text-primary transition-colors font-headline text-sm font-bold tracking-tight">Events</a>
        <?php endif; ?>

        <?php if(auth()->user() && auth()->user()->role === 'admin'): ?>
          <?php if(Route::currentRouteName() === 'schedule-event'): ?>
            <a href="<?php echo e(route('schedule-event')); ?>" class="block text-blue-900 dark:text-blue-200 font-bold font-headline text-sm tracking-tight transition-all duration-300 pb-2 border-b-2 border-blue-900 dark:border-blue-400">Schedule Event</a>
          <?php else: ?>
            <a href="<?php echo e(route('schedule-event')); ?>" class="block text-on-surface-variant hover:text-primary transition-colors font-headline text-sm font-bold tracking-tight">Schedule Event</a>
          <?php endif; ?>
          <a href="<?php echo e(route('create-account')); ?>" class="block text-on-surface-variant hover:text-primary transition-colors font-headline text-sm font-bold tracking-tight">Accounts</a>
        <?php endif; ?>
      </div>

      <!-- Mobile User Menu -->
      <div class="border-t border-outline-variant/10 pt-4">
        <div class="flex items-center gap-3 mb-3">
          <span class="text-on-surface font-medium text-sm"><?php echo e(auth()->user()->name ?? 'User'); ?></span>
        </div>
        <form method="POST" action="<?php echo e(route('logout')); ?>" class="block">
          <?php echo csrf_field(); ?>
          <button type="submit" class="w-full flex items-center gap-3 px-3 py-2 hover:bg-surface-container transition-colors text-on-surface font-medium text-sm text-left hover:text-error rounded-lg">
            <span class="material-symbols-outlined text-lg">logout</span>
            <span>Logout</span>
          </button>
        </form>
      </div>
    </div>
  </div>
</header>

<script>
  document.addEventListener('DOMContentLoaded', function() {
    // Desktop dropdown menu
    const settingsBtn = document.getElementById('settingsBtn');
    const settingsMenu = document.getElementById('settingsMenu');
    const settingsDropdown = document.getElementById('settingsDropdown');

    // Toggle dropdown on button click
    if (settingsBtn) {
      settingsBtn.addEventListener('click', function(e) {
        e.stopPropagation();
        settingsMenu.classList.toggle('hidden');
      });
    }

    // Close dropdown when clicking outside
    document.addEventListener('click', function(e) {
      if (settingsDropdown && !settingsDropdown.contains(e.target)) {
        if (settingsMenu) {
          settingsMenu.classList.add('hidden');
        }
      }
    });

    // Mobile menu toggle
    const mobileMenuBtn = document.getElementById('mobileMenuBtn');
    const mobileMenu = document.getElementById('mobileMenu');

    if (mobileMenuBtn) {
      mobileMenuBtn.addEventListener('click', function() {
        mobileMenu.classList.toggle('hidden');
      });
    }

    // Close mobile menu when clicking on a link
    const mobileMenuLinks = mobileMenu ? mobileMenu.querySelectorAll('a, form button') : [];
    mobileMenuLinks.forEach(link => {
      link.addEventListener('click', function() {
        mobileMenu.classList.add('hidden');
      });
    });
  });
</script>
<?php /**PATH C:\AttendanceEvent\resources\views/components/header.blade.php ENDPATH**/ ?>