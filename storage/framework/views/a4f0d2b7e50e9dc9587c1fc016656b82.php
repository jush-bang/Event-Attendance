<!DOCTYPE html>
<html class="light" lang="en">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title>Login | EventSync Management System</title>
    
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Work+Sans:wght@300;400;600;700;800;900&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet"/>
    
    <!-- Icons -->
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
    
    <!-- Tailwind -->
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    
    <script id="tailwind-config">
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    "colors": {
                        "surface-container-high": "#e8e8ea",
                        "on-surface-variant": "#424752",
                        "secondary-fixed": "#ffe16d",
                        "on-error": "#ffffff",
                        "surface-container-lowest": "#ffffff",
                        "surface-container": "#eeeef0",
                        "primary": "#003f87",
                        "primary-fixed": "#d7e2ff",
                        "inverse-surface": "#2f3132",
                        "primary-container": "#0056b3",
                        "on-secondary-fixed": "#221b00",
                        "on-primary-fixed": "#001a40",
                        "error": "#ba1a1a",
                        "on-background": "#1a1c1d",
                        "surface": "#f9f9fb",
                        "on-primary-fixed-variant": "#004491",
                        "surface-dim": "#d9dadc",
                        "tertiary-fixed": "#d6e3ff",
                        "surface-container-low": "#f3f3f5",
                        "on-secondary-fixed-variant": "#544600",
                        "tertiary": "#1e4173",
                        "surface-variant": "#e2e2e4",
                        "tertiary-container": "#38598c",
                        "surface-bright": "#f9f9fb",
                        "on-secondary-container": "#6e5c00",
                        "tertiary-fixed-dim": "#a9c7ff",
                        "inverse-primary": "#acc7ff",
                        "primary-fixed-dim": "#acc7ff",
                        "outline": "#727784",
                        "on-tertiary-fixed-variant": "#244779",
                        "on-tertiary-fixed": "#001b3d",
                        "secondary-container": "#fcd400",
                        "on-surface": "#1a1c1d",
                        "on-primary-container": "#bbd0ff",
                        "surface-container-highest": "#e2e2e4",
                        "secondary-fixed-dim": "#e9c400",
                        "on-tertiary": "#ffffff",
                        "on-primary": "#ffffff",
                        "on-error-container": "#93000a",
                        "on-tertiary-container": "#b8d0ff",
                        "background": "#f9f9fb",
                        "secondary": "#705d00",
                        "error-container": "#ffdad6",
                        "on-secondary": "#ffffff",
                        "inverse-on-surface": "#f0f0f2",
                        "surface-tint": "#115cb9",
                        "outline-variant": "#c2c6d4"
                    },
                    "borderRadius": {
                        "DEFAULT": "0.125rem",
                        "lg": "0.25rem",
                        "xl": "0.5rem",
                        "full": "0.75rem"
                    },
                    "fontFamily": {
                        "headline": ["Work Sans"],
                        "body": ["Inter"],
                        "label": ["Inter"]
                    }
                },
            },
        }
    </script>
    
    <style>
        .material-symbols-outlined {
            font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
        }
        body { font-family: 'Inter', sans-serif; }
        h1, h2, h3 { font-family: 'Work Sans', sans-serif; }
    </style>
</head>
<body class="bg-surface text-on-surface min-h-screen flex flex-col items-center justify-center overflow-hidden">
    <!-- Background Layer -->
    <div class="fixed inset-0 z-0">
        <img class="w-full h-full object-cover filter blur-[2px] scale-105" data-alt="Modern architectural photography of a university building facade with sharp angles and glass windows in cool morning light" src="https://lh3.googleusercontent.com/aida-public/AB6AXuDaMIR1FA7lSkH1biw25iqZfCzEzbV5AoSN9SPIf09xKngbsDBCI7qvvDXSRUD79txBvoBCV6f7N52O9WplcvdCWuRdbKbZuagsxuuFe-no0VXvpdK-u-FEWDPZSiwbj97AThLN2w7n7_0-vFi2iphyxC_7wWhIIVouJbYkOBbkmOlxnrd7wdswXmgSJ0hf85SuU_KrsziFAgnaTwFM3WIjtNv94ngvEBA3KAfR00eti9nzlZv0gMFGRQj_6r2bbwcoICa5Mp7ZCOc"/>
        <div class="absolute inset-0 bg-primary/40 mix-blend-multiply"></div>
        <div class="absolute inset-0 bg-gradient-to-tr from-primary via-primary/20 to-transparent"></div>
    </div>
    
    <!-- Main Content Canvas -->
    <main class="relative z-10 w-full max-w-[1440px] px-6 flex flex-col items-center justify-center min-h-screen">
        <!-- Login Card -->
        <div class="w-full max-w-md bg-white/90 dark:bg-slate-900/90 backdrop-blur-2xl rounded-xl shadow-[0_24px_48px_rgba(0,63,135,0.15)] overflow-hidden">
            <!-- Card Header/Brand -->
            <div class="pt-12 pb-8 px-10 text-center">
                <h1 class="text-2xl font-black text-primary tracking-tight uppercase leading-tight">
                    Event Attendance<br/>Management System
                </h1>
            </div>
            
            <!-- Card Body / Form -->
            <form class="px-10 pb-12 space-y-6" method="POST" action="/login">
                <?php echo csrf_field(); ?>

                <!-- Error Messages -->
                <?php if($errors->any()): ?>
                    <div class="bg-error-container/10 border border-error-container/20 rounded-lg p-4">
                        <div class="flex items-center space-x-2">
                            <span class="material-symbols-outlined text-error">error</span>
                            <div class="text-sm font-medium text-on-error-container">
                                <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <p><?php echo e($error); ?></p>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Success Messages -->
                <?php if(session('status')): ?>
                    <div class="bg-secondary-container/10 border border-secondary-container/20 rounded-lg p-4">
                        <div class="flex items-center space-x-2">
                            <span class="material-symbols-outlined text-secondary-container">check_circle</span>
                            <p class="text-sm font-medium text-on-secondary-container"><?php echo e(session('status')); ?></p>
                        </div>
                    </div>
                <?php endif; ?>

                <div class="space-y-4">
                    <!-- Email Input -->
                    <div class="space-y-1.5">
                        <label class="text-xs font-bold uppercase tracking-widest text-primary/60 px-1" for="email">Admin Email</label>
                        <div class="relative group">
                            <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-primary/40 transition-colors group-focus-within:text-primary">mail</span>
                            <input class="w-full pl-12 pr-4 py-4 bg-surface-container-low border-none rounded-lg focus:ring-2 focus:ring-primary/20 focus:bg-white transition-all outline-none font-medium text-on-surface placeholder:text-outline-variant/60" id="email" name="email" placeholder="@balagtas.sti.edu.ph" type="email" value="<?php echo e(old('email')); ?>" required/>
                        </div>
                    </div>
                    
                    <!-- Password Input -->
                    <div class="space-y-1.5">
                        <div class="flex justify-between items-end px-1">
                            <label class="text-xs font-bold uppercase tracking-widest text-primary/60" for="password">Password</label>
                        </div>
                        <div class="relative group">
                            <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-primary/40 transition-colors group-focus-within:text-primary">lock</span>
                            <input class="w-full pl-12 pr-4 py-4 bg-surface-container-low border-none rounded-lg focus:ring-2 focus:ring-primary/20 focus:bg-white transition-all outline-none font-medium text-on-surface placeholder:text-outline-variant/60" id="password" name="password" placeholder="••••••••" type="password" required/>
                        </div>
                    </div>
                </div>
                
                <!-- Action Button -->
                <button class="group relative w-full bg-primary hover:bg-primary-container text-white py-4 px-6 rounded-lg font-bold text-sm uppercase tracking-widest transition-all duration-300 shadow-lg shadow-primary/20 active:scale-[0.98]" type="submit">
                    <span class="relative z-10">Sign In</span>
                    <div class="absolute inset-0 bg-secondary-container opacity-0 group-hover:opacity-10 transition-opacity rounded-lg"></div>
                    <!-- Subtle Gold Underline Glow -->
                    <div class="absolute bottom-0 left-1/2 -translate-x-1/2 w-1/2 h-[2px] bg-secondary-container opacity-0 group-hover:opacity-100 group-hover:w-full transition-all duration-500"></div>
                </button>
                
                <!-- Footer Context -->
                <div class="pt-4 text-center">
                    <p class="text-[11px] text-on-surface-variant/60 font-medium tracking-wide">Admin Portal — Authorized personnel access only.</p>
                </div>
            </form>
        </div>
    </main>
    
    <!-- Bottom Left Brand Info -->
    <div class="absolute bottom-8 left-8 z-20 text-white/50 text-[10px] font-medium tracking-[0.15em] uppercase hidden md:block">STI COLLEGE BALAGTAS EVENT ATTENDANCE MANAGEMENT SYSTEM</div>
</body>
</html>
<?php /**PATH C:\AttendanceEvent\resources\views/login.blade.php ENDPATH**/ ?>