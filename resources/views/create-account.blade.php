<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <meta name="csrf-token" content="{{ csrf_token() }}"/>
    <title>Account Management | STI Admin Portal</title>
    
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
              "primary-container": "#0056b3",
              "on-surface-variant": "#424752",
              "on-secondary": "#ffffff",
              "tertiary-fixed": "#d6e3ff",
              "primary-fixed": "#d7e2ff",
              "primary-fixed-dim": "#acc7ff",
              "on-error": "#ffffff",
              "primary": "#003f87"
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
      };
    </script>
    
    <style>
        .material-symbols-outlined {
            font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
        }
        body { font-family: 'Inter', sans-serif; }
        h1, h2, h3, .headline { font-family: 'Work Sans', sans-serif; }

        /* Toast animations */
        @keyframes slideInFromTop {
            from {
                transform: translateY(-1rem);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        @keyframes slideOutToTop {
            from {
                transform: translateY(0);
                opacity: 1;
            }
            to {
                transform: translateY(-1rem);
                opacity: 0;
            }
        }

        .animate-in.slide-in-from-top-4 {
            animation: slideInFromTop 0.3s ease-out;
        }

        .animate-out.slide-out-to-top-4 {
            animation: slideOutToTop 0.3s ease-out;
        }
    </style>
</head>

<body class="bg-surface text-on-surface min-h-screen flex flex-col">
    <!-- Universal Header Component -->
    <x-header />

    <!-- Toast Notification -->
    <div id="toastContainer" class="fixed top-6 right-6 z-[60] max-w-sm">
        <!-- Success Toast -->
        @if(session('success'))
        <div id="successToast" class="bg-white rounded-lg shadow-lg border-l-4 border-green-500 p-4 mb-3 flex items-start gap-3 animate-in slide-in-from-top-4 duration-300">
            <span class="material-symbols-outlined text-green-500 flex-shrink-0">check_circle</span>
            <div class="flex-1">
                <p class="font-semibold text-gray-900">Success</p>
                <p class="text-sm text-gray-600">{{ session('success') }}</p>
            </div>
            <button class="text-gray-400 hover:text-gray-600 transition-colors" onclick="closeToast('successToast')">
                <span class="material-symbols-outlined text-lg">close</span>
            </button>
        </div>
        @endif

        <!-- Error Toast -->
        @if($errors->any())
        <div id="errorToast" class="bg-white rounded-lg shadow-lg border-l-4 border-red-500 p-4 mb-3 flex items-start gap-3 animate-in slide-in-from-top-4 duration-300">
            <span class="material-symbols-outlined text-red-500 flex-shrink-0">error</span>
            <div class="flex-1">
                <p class="font-semibold text-gray-900">Error</p>
                <ul class="text-sm text-gray-600 list-disc list-inside">
                    @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            <button class="text-gray-400 hover:text-gray-600 transition-colors" onclick="closeToast('errorToast')">
                <span class="material-symbols-outlined text-lg">close</span>
            </button>
        </div>
        @endif
    </div>

    <!-- Main Content Canvas -->
    <main class="flex-grow flex items-center justify-center px-4 sm:px-6 py-6 sm:py-12">
        <div class="w-full max-w-4xl">
            <!-- Accounts Management Header -->
            <div class="mb-8 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                <div>
                    <h1 class="text-2xl sm:text-3xl lg:text-4xl font-black text-primary tracking-tight mb-1 sm:mb-2">Account Management</h1>
                    <p class="text-on-surface-variant text-sm sm:text-base lg:text-lg">Manage scanner personnel accounts for STI Balagtas</p>
                </div>
                <button id="createAccountBtn" class="flex items-center gap-2 bg-primary text-on-primary px-4 sm:px-6 py-3 rounded-lg font-semibold hover:bg-primary-container active:scale-95 transition-all shadow-lg shadow-primary/20 whitespace-nowrap">
                    <span class="material-symbols-outlined text-lg sm:text-xl">person_add</span>
                    <span class="hidden sm:inline">Create Account</span>
                    <span class="sm:hidden">Add</span>
                </button>
            </div>

            <!-- Accounts Table -->
            <div class="bg-surface-container-lowest rounded-xl shadow-sm border border-outline-variant overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm sm:text-base">
                        <thead class="bg-surface-container border-b border-outline-variant">
                            <tr>
                                <th class="px-3 sm:px-6 py-3 sm:py-4 text-left text-xs sm:text-sm font-bold text-on-surface uppercase">Email</th>
                                <th class="px-3 sm:px-6 py-3 sm:py-4 text-left text-xs sm:text-sm font-bold text-on-surface uppercase">Role</th>
                                <th class="px-3 sm:px-6 py-3 sm:py-4 text-left text-xs sm:text-sm font-bold text-on-surface uppercase">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-outline-variant/20">
                            @forelse($users as $user)
                            <tr class="hover:bg-surface-container-low transition-colors">
                                <td class="px-3 sm:px-6 py-3 sm:py-4 text-xs sm:text-sm text-on-surface break-all sm:break-normal">{{ $user->email }}</td>
                                <td class="px-3 sm:px-6 py-3 sm:py-4 text-xs sm:text-sm text-on-surface-variant">{{ $user->role === 'admin' ? 'Administrator' : 'Scanner Personnel' }}</td>
                                <td class="px-3 sm:px-6 py-3 sm:py-4 text-xs sm:text-sm">
                                    <button class="editAccountBtn text-primary hover:text-primary-container font-semibold transition-colors whitespace-nowrap" data-email="{{ $user->email }}" data-role="{{ $user->role }}">Edit</button>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="3" class="px-3 sm:px-6 py-3 sm:py-4 text-center text-xs sm:text-sm text-on-surface-variant">No accounts found. Create one to get started.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>

    <!-- Create Account Modal -->
    <div id="createAccountModal" class="hidden fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4">
        <div class="bg-surface-container-lowest rounded-xl shadow-lg p-6 sm:p-10 w-full max-w-md max-h-[90vh] overflow-y-auto">
            <!-- Close Button -->
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-xl sm:text-2xl font-bold text-primary">Scanner Personnel</h2>
                <button id="closeModalBtn" class="p-1 hover:bg-surface-container rounded-lg transition-colors text-on-surface-variant">
                    <span class="material-symbols-outlined text-2xl">close</span>
                </button>
            </div>

            <!-- Modal Content -->
            <p class="text-on-surface-variant text-xs sm:text-sm font-medium mb-6">Provision a new academic access account</p>

            <!-- Divider -->
            <div class="h-1 w-12 bg-secondary-container rounded-full mb-6"></div>

            <form class="space-y-6" method="POST" action="{{ route('create-account.store') }}" id="createAccountForm">
                @csrf

                <!-- Input: Email -->
                <div class="space-y-2">
                    <label class="block text-xs font-bold uppercase tracking-widest text-on-surface-variant ml-1" for="email">Personnel Email</label>
                    <div class="relative group">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                            <span class="material-symbols-outlined text-on-surface-variant text-lg group-focus-within:text-primary transition-colors">mail</span>
                        </div>
                        <input 
                            class="block w-full pl-11 pr-4 py-3 sm:py-4 bg-surface-container-high border-none rounded-lg text-on-surface placeholder:text-outline focus:ring-2 focus:ring-primary/40 transition-all font-body text-sm @error('email') ring-2 ring-error @enderror" 
                            id="email" 
                            name="email"
                            placeholder="name.000000@balagtas.sti.edu.ph" 
                            type="email"
                            required
                            value="{{ old('email') }}"
                        />
                    </div>
                    @error('email')
                        <p class="text-error text-xs font-semibold mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Input: Role -->
                <div class="space-y-2">
                    <label class="block text-xs font-bold uppercase tracking-widest text-on-surface-variant ml-1" for="role">Account Role</label>
                    <div class="relative group">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                            <span class="material-symbols-outlined text-on-surface-variant text-lg group-focus-within:text-primary transition-colors">person_outline</span>
                        </div>
                        <select 
                            class="block w-full pl-11 pr-4 py-3 sm:py-4 bg-surface-container-high border-none rounded-lg text-on-surface focus:ring-2 focus:ring-primary/40 transition-all font-body text-sm @error('role') ring-2 ring-error @enderror" 
                            id="role" 
                            name="role"
                            required
                        >
                            <option value="">Select a role</option>
                            <option value="admin" {{ old('role') === 'admin' ? 'selected' : '' }}>Administrator</option>
                            <option value="scanner" {{ old('role') === 'scanner' ? 'selected' : '' }}>Scanner Personnel</option>
                        </select>
                    </div>
                    @error('role')
                        <p class="text-error text-xs font-semibold mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Input: Password -->
                <div class="space-y-2">
                    <label class="block text-xs font-bold uppercase tracking-widest text-on-surface-variant ml-1" for="password">Access Password</label>
                    <div class="relative group">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                            <span class="material-symbols-outlined text-on-surface-variant text-lg group-focus-within:text-primary transition-colors">lock</span>
                        </div>
                        <input 
                            class="block w-full pl-11 pr-14 py-3 sm:py-4 bg-surface-container-high border-none rounded-lg text-on-surface placeholder:text-outline focus:ring-2 focus:ring-primary/40 transition-all font-body text-sm @error('password') ring-2 ring-error @enderror" 
                            id="password" 
                            name="password"
                            placeholder="••••••••••••" 
                            type="password"
                            required
                        />
                        <button 
                            class="absolute inset-y-0 right-0 pr-4 flex items-center text-on-surface-variant hover:text-primary transition-colors" 
                            id="togglePassword"
                            type="button"
                        >
                            <span class="material-symbols-outlined" id="passwordIcon">visibility</span>
                        </button>
                    </div>
                    @error('password')
                        <p class="text-error text-xs font-semibold mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- CTA Section -->
                <div class="pt-4">
                    <button 
                        class="w-full bg-primary text-on-primary font-bold py-3 sm:py-4 rounded-lg flex items-center justify-center space-x-2 shadow-lg shadow-primary/20 hover:bg-primary-container active:scale-95 transition-all group text-sm sm:text-base" 
                        type="submit"
                    >
                        <span>Create Account</span>
                        <span class="material-symbols-outlined text-xl group-hover:translate-x-1 transition-transform hidden sm:inline">chevron_right</span>
                    </button>
                </div>

                <!-- Terms Text -->
                <p class="text-center text-xs text-on-surface-variant leading-relaxed">
                    By creating this account, you authorize the personnel to perform scanning operations under the STI Balagtas Academic Management guidelines.
                </p>
            </form>
        </div>
    </div>

    <!-- Edit Account Modal -->
    <div id="editAccountModal" class="hidden fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4">
        <div class="bg-surface-container-lowest rounded-xl shadow-lg p-6 sm:p-10 w-full max-w-md max-h-[90vh] overflow-y-auto">
            <!-- Close Button -->
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-xl sm:text-2xl font-bold text-primary">Edit Account</h2>
                <button id="closeEditModalBtn" class="p-1 hover:bg-surface-container rounded-lg transition-colors text-on-surface-variant">
                    <span class="material-symbols-outlined text-2xl">close</span>
                </button>
            </div>

            <!-- Modal Content -->
            <p class="text-on-surface-variant text-xs sm:text-sm font-medium mb-6">Update account details and security settings</p>

            <!-- Divider -->
            <div class="h-1 w-12 bg-secondary-container rounded-full mb-6"></div>

            <form class="space-y-6" id="editAccountForm" method="POST">
                @csrf
                @method('PUT')

                <!-- Display: Email -->
                <div class="space-y-2">
                    <label class="block text-xs font-bold uppercase tracking-widest text-on-surface-variant ml-1">Email</label>
                    <div class="block w-full pl-4 pr-4 py-3 sm:py-4 bg-surface-container-high border-none rounded-lg text-on-surface font-body text-sm" id="emailDisplay">
                        admin@balagtas.sti.edu.ph
                    </div>
                </div>

                <!-- Display: Role -->
                <div class="space-y-2">
                    <label class="block text-xs font-bold uppercase tracking-widest text-on-surface-variant ml-1">Role</label>
                    <div class="block w-full pl-4 pr-4 py-3 sm:py-4 bg-surface-container-high border-none rounded-lg text-on-surface font-body text-sm" id="roleDisplay">
                        Administrator
                    </div>
                </div>

                <!-- Input: New Password -->
                <div class="space-y-2">
                    <label class="block text-xs font-bold uppercase tracking-widest text-on-surface-variant ml-1" for="newPassword">Change Password (Leave blank to keep current)</label>
                    <div class="relative group">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                            <span class="material-symbols-outlined text-on-surface-variant text-lg group-focus-within:text-primary transition-colors">lock</span>
                        </div>
                        <input 
                            class="block w-full pl-11 pr-14 py-3 sm:py-4 bg-surface-container-high border-none rounded-lg text-on-surface placeholder:text-outline focus:ring-2 focus:ring-primary/40 transition-all font-body text-sm" 
                            id="newPassword" 
                            name="password"
                            placeholder="••••••••••••" 
                            type="password"
                        />
                        <button 
                            class="absolute inset-y-0 right-0 pr-4 flex items-center text-on-surface-variant hover:text-primary transition-colors" 
                            id="toggleNewPassword"
                            type="button"
                        >
                            <span class="material-symbols-outlined" id="newPasswordIcon">visibility</span>
                        </button>
                    </div>
                </div>

                <!-- CTA Section -->
                <div class="pt-4">
                    <button 
                        class="w-full bg-primary text-on-primary font-bold py-3 sm:py-4 rounded-lg flex items-center justify-center space-x-2 shadow-lg shadow-primary/20 hover:bg-primary-container active:scale-95 transition-all group text-sm sm:text-base" 
                        type="submit"
                    >
                        <span>Save Changes</span>
                        <span class="material-symbols-outlined text-xl group-hover:translate-x-1 transition-transform hidden sm:inline">check</span>
                    </button>
                </div>
            </form>

            <!-- Divider -->
            <div class="h-1 w-12 bg-outline-variant/30 rounded-full my-6"></div>

            <!-- Delete Account Section -->
            <div class="space-y-3">
                <p class="text-xs font-semibold text-error uppercase tracking-widest">Danger Zone</p>
                <p class="text-xs sm:text-sm text-on-surface-variant leading-relaxed">Deleting an account is <span class="font-semibold text-error">permanent</span> and cannot be undone. All associated data will be removed.</p>
                <button 
                    id="deleteAccountBtn"
                    class="w-full bg-error/10 text-error font-bold py-2 sm:py-3 rounded-lg flex items-center justify-center space-x-2 hover:bg-error/20 active:scale-95 transition-all group border border-error/30 text-sm sm:text-base" 
                    type="button"
                >
                    <span class="material-symbols-outlined text-lg">delete_outline</span>
                    <span>Delete Account</span>
                </button>
            </div>
        </div>
    </div>

    <!-- Delete Account Confirmation Modal -->
    <div id="deleteConfirmModal" class="hidden fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4">
        <div class="bg-surface-container-lowest rounded-xl shadow-lg p-6 sm:p-10 w-full max-w-md">
            <!-- Header -->
            <div class="flex items-center gap-3 mb-6">
                <span class="material-symbols-outlined text-3xl sm:text-4xl text-error">warning</span>
                <div>
                    <h2 class="text-xl sm:text-2xl font-bold text-error">Delete Account?</h2>
                </div>
            </div>

            <!-- Content -->
            <p class="text-on-surface-variant text-sm mb-2">You are about to delete the account:</p>
            <p class="text-on-surface font-semibold text-base sm:text-lg mb-6 break-all" id="deleteEmailConfirm">admin@balagtas.sti.edu.ph</p>

            <p class="text-on-surface-variant text-xs sm:text-sm mb-6 leading-relaxed">
                <span class="font-semibold text-error">This action is permanent and cannot be undone.</span> All data associated with this account will be deleted immediately.
            </p>

            <!-- Actions -->
            <div class="flex flex-col sm:flex-row gap-3">
                <button 
                    id="cancelDeleteBtn"
                    class="flex-1 bg-surface-container text-on-surface font-bold py-2 sm:py-3 rounded-lg hover:bg-surface-container-high active:scale-95 transition-all text-sm sm:text-base" 
                    type="button"
                >
                    Cancel
                </button>
                <button 
                    id="confirmDeleteBtn"
                    class="flex-1 bg-error text-on-error font-bold py-2 sm:py-3 rounded-lg hover:bg-error/90 active:scale-95 transition-all flex items-center justify-center space-x-2 text-sm sm:text-base" 
                    type="button"
                >
                    <span class="material-symbols-outlined text-lg">delete_outline</span>
                    <span>Delete</span>
                </button>
            </div>
        </div>
    </div>

    <script>
        // Toast notification system
        function closeToast(toastId) {
            const toast = document.getElementById(toastId);
            if (toast) {
                toast.classList.add('animate-out', 'slide-out-to-top-4', 'duration-300');
                setTimeout(() => {
                    toast.remove();
                }, 300);
            }
        }

        // Auto-close success toast after 5 seconds
        window.addEventListener('DOMContentLoaded', function() {
            const successToast = document.getElementById('successToast');
            if (successToast) {
                setTimeout(() => {
                    closeToast('successToast');
                }, 5000);
            }
        });

        document.addEventListener('DOMContentLoaded', function() {
            // Create Account Modal
            const createAccountBtn = document.getElementById('createAccountBtn');
            const createAccountModal = document.getElementById('createAccountModal');
            const closeModalBtn = document.getElementById('closeModalBtn');
            const togglePasswordBtn = document.getElementById('togglePassword');
            const passwordInput = document.getElementById('password');
            const passwordIcon = document.getElementById('passwordIcon');

            // Open modal
            createAccountBtn.addEventListener('click', function() {
                createAccountModal.classList.remove('hidden');
            });

            // Close modal
            closeModalBtn.addEventListener('click', function() {
                createAccountModal.classList.add('hidden');
            });

            // Close modal when clicking outside
            createAccountModal.addEventListener('click', function(e) {
                if (e.target === createAccountModal) {
                    createAccountModal.classList.add('hidden');
                }
            });

            // Toggle password visibility
            togglePasswordBtn.addEventListener('click', function(e) {
                e.preventDefault();
                const isPassword = passwordInput.type === 'password';
                passwordInput.type = isPassword ? 'text' : 'password';
                passwordIcon.textContent = isPassword ? 'visibility_off' : 'visibility';
            });

            // Handle create account form submission
            const createAccountForm = document.getElementById('createAccountForm');
            if (createAccountForm) {
                createAccountForm.addEventListener('submit', function() {
                    // Close modal after form submission
                    setTimeout(() => {
                        createAccountModal.classList.add('hidden');
                        createAccountForm.reset();
                    }, 100);
                });
            }

            // Reset form when modal is closed
            closeModalBtn.addEventListener('click', function() {
                createAccountForm.reset();
            });

            // Edit Account Modal
            const editAccountModal = document.getElementById('editAccountModal');
            const closeEditModalBtn = document.getElementById('closeEditModalBtn');
            const editAccountButtons = document.querySelectorAll('.editAccountBtn');
            const toggleNewPasswordBtn = document.getElementById('toggleNewPassword');
            const newPasswordInput = document.getElementById('newPassword');
            const newPasswordIcon = document.getElementById('newPasswordIcon');
            const deleteAccountBtn = document.getElementById('deleteAccountBtn');
            const deleteConfirmModal = document.getElementById('deleteConfirmModal');
            const cancelDeleteBtn = document.getElementById('cancelDeleteBtn');
            const confirmDeleteBtn = document.getElementById('confirmDeleteBtn');
            const emailDisplay = document.getElementById('emailDisplay');
            const roleDisplay = document.getElementById('roleDisplay');
            const deleteEmailConfirm = document.getElementById('deleteEmailConfirm');

            let currentUserEmail = null;
            let currentUserRole = null;

            // Open edit modal
            editAccountButtons.forEach(button => {
                button.addEventListener('click', function() {
                    currentUserEmail = this.getAttribute('data-email');
                    currentUserRole = this.getAttribute('data-role');

                    // Display user info
                    emailDisplay.textContent = currentUserEmail;
                    roleDisplay.textContent = currentUserRole === 'admin' ? 'Administrator' : 'Scanner Personnel';

                    // Set form action (you'll need to update this with actual route)
                    const editAccountForm = document.getElementById('editAccountForm');
                    editAccountForm.action = `/accounts/${currentUserEmail}`;

                    editAccountModal.classList.remove('hidden');
                    newPasswordInput.value = '';
                    newPasswordIcon.textContent = 'visibility';
                });
            });

            // Close edit modal
            closeEditModalBtn.addEventListener('click', function() {
                editAccountModal.classList.add('hidden');
            });

            // Close edit modal when clicking outside
            editAccountModal.addEventListener('click', function(e) {
                if (e.target === editAccountModal) {
                    editAccountModal.classList.add('hidden');
                }
            });

            // Toggle new password visibility
            toggleNewPasswordBtn.addEventListener('click', function(e) {
                e.preventDefault();
                const isPassword = newPasswordInput.type === 'password';
                newPasswordInput.type = isPassword ? 'text' : 'password';
                newPasswordIcon.textContent = isPassword ? 'visibility_off' : 'visibility';
            });

            // Delete account button
            deleteAccountBtn.addEventListener('click', function() {
                deleteEmailConfirm.textContent = currentUserEmail;
                deleteConfirmModal.classList.remove('hidden');
            });

            // Cancel delete
            cancelDeleteBtn.addEventListener('click', function() {
                deleteConfirmModal.classList.add('hidden');
            });

            // Close delete modal when clicking outside
            deleteConfirmModal.addEventListener('click', function(e) {
                if (e.target === deleteConfirmModal) {
                    deleteConfirmModal.classList.add('hidden');
                }
            });

            // Confirm delete
            confirmDeleteBtn.addEventListener('click', function() {
                // Create and submit delete form
                const deleteForm = document.createElement('form');
                deleteForm.method = 'POST';
                deleteForm.action = `/accounts/${currentUserEmail}`;
                
                const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                
                deleteForm.innerHTML = `
                    <input type="hidden" name="_token" value="${csrfToken}">
                    <input type="hidden" name="_method" value="DELETE">
                `;
                
                document.body.appendChild(deleteForm);
                deleteForm.submit();
            });
        });
    </script>
</body>
</html>
