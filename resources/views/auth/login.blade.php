<!DOCTYPE html>
<html lang="id" class="h-full bg-slate-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk - Laporan Produksi Woundcare</title>
    
    <!-- Google Fonts: Inter & Outfit -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Outfit:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                        outfit: ['Outfit', 'sans-serif'],
                    },
                    colors: {
                        primary: {
                            50: '#f0f9ff',
                            100: '#e0f2fe',
                            200: '#bae6fd',
                            300: '#7dd3fc',
                            400: '#38bdf8',
                            500: '#0ea5e9',
                            600: '#0284c7',
                            700: '#0369a1',
                            800: '#075985',
                            900: '#0c4a6e',
                        },
                        office: {
                            50: '#f5f7fa',
                            100: '#e4e8f0',
                            200: '#cbd5e1',
                            300: '#94a3b8',
                            400: '#64748b',
                            500: '#475569',
                            600: '#334155',
                            750: '#1e293b',
                            800: '#1e293b',
                            900: '#0f172a',
                        }
                    }
                }
            }
        }
    </script>
</head>
<body class="min-h-full flex flex-col justify-center py-12 sm:px-6 lg:px-8 bg-office-900">
    <div class="sm:mx-auto sm:w-full sm:max-w-md text-center">
        <!-- Logo -->
        <div class="inline-flex w-14 h-14 rounded-2xl bg-gradient-to-tr from-primary-500 to-indigo-500 items-center justify-center text-white shadow-xl mb-4">
            <i data-lucide="activity" class="w-8 h-8"></i>
        </div>
        <h2 class="text-2xl font-extrabold font-outfit text-white tracking-tight">Woundcare</h2>
        <p class="mt-1 text-sm text-slate-400">Sistem Pencatatan Hasil Produksi & Verifikasi Gaji</p>
    </div>

    <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md">
        <div class="bg-white py-8 px-4 shadow-2xl rounded-2xl sm:px-10 border border-slate-700/10">
            
            <!-- Toast Notifications -->
            @if(session('success'))
            <div class="mb-5 p-3.5 rounded-xl bg-emerald-50 border border-emerald-250 text-emerald-800 text-xs font-semibold flex items-center gap-2">
                <i data-lucide="check-circle" class="w-4.5 h-4.5 text-emerald-600"></i>
                <span>{{ session('success') }}</span>
            </div>
            @endif

            @if($errors->any())
            <div class="mb-5 p-3.5 rounded-xl bg-rose-50 border border-rose-250 text-rose-800 text-xs flex flex-col gap-1">
                <div class="flex items-center gap-2 font-bold">
                    <i data-lucide="alert-circle" class="w-4.5 h-4.5 text-rose-650"></i>
                    <span>Ada Kendala Masuk</span>
                </div>
                <ul class="list-disc pl-5 mt-1 text-2xs space-y-0.5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            <!-- Login Options Tabs -->
            <div class="flex border-b border-slate-100 mb-6">
                <button type="button" onclick="switchTab('phone')" id="tab-phone" class="flex-1 pb-3 text-sm font-semibold text-slate-400 border-b-2 border-transparent hover:text-slate-600 focus:outline-none transition-all flex items-center justify-center gap-2">
                    <i data-lucide="phone" class="w-4 h-4"></i>
                    Nomor WhatsApp
                </button>
                <button type="button" onclick="switchTab('google')" id="tab-google" class="flex-1 pb-3 text-sm font-bold text-primary-600 border-b-2 border-primary-600 focus:outline-none transition-all flex items-center justify-center gap-2">
                    <i data-lucide="chrome" class="w-4 h-4"></i>
                    Akun Google
                </button>
            </div>

            <!-- WhatsApp Login Form -->
            <form id="form-phone" action="{{ route('login.phone') }}" method="POST" class="space-y-4 hidden" onsubmit="saveRememberedPhone()">
                @csrf
                <div class="space-y-1.5">
                    <label for="whatsapp" class="text-xs font-bold text-slate-655 block">Nomor WhatsApp Terdaftar</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-450">
                            <i data-lucide="phone" class="w-4.5 h-4.5"></i>
                        </span>
                        <input type="text" name="whatsapp" id="whatsapp" required placeholder="Contoh: 08123456789" value="{{ old('whatsapp') }}" class="pl-10 w-full rounded-xl border border-slate-350 py-3 px-4 text-sm bg-white text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all">
                    </div>
                    <p class="text-3xs text-slate-500 leading-normal">Gunakan nomor WhatsApp aktif Anda yang sudah terdaftar di sistem.</p>
                </div>

                <!-- Remember Me Checkbox -->
                <div class="flex items-center mt-2.5">
                    <input id="remember_me" name="remember_me" type="checkbox" class="h-4 w-4 text-primary-600 focus:ring-primary-500 border-slate-300 rounded cursor-pointer transition-all">
                    <label for="remember_me" class="ml-2 block text-xs text-slate-650 cursor-pointer select-none font-semibold">Ingat Nomor Saya</label>
                </div>

                <button type="submit" class="w-full flex items-center justify-center gap-2 px-4 py-3 bg-office-900 hover:bg-slate-800 text-white text-sm font-bold rounded-xl shadow-md transition-all active:scale-[0.98]">
                    <i data-lucide="log-in" class="w-4 h-4"></i>
                    <span>Masuk Aplikasi</span>
                </button>
            </form>

            <!-- Google Sign-in Option -->
            <div id="form-google" class="space-y-4">
                <p class="text-xs text-slate-555 text-center mb-4 leading-normal">Silakan masuk menggunakan akun Google Anda yang telah terhubung ke profil operator Anda.</p>
                
                <a href="{{ route('auth.google') }}" class="w-full flex items-center justify-center gap-3 px-4 py-3 border border-slate-300 rounded-xl shadow-sm bg-white text-sm font-bold text-slate-700 hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 transition-all active:scale-[0.98]">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" width="24" height="24" xmlns="http://www.w3.org/2000/svg">
                        <g transform="matrix(1, 0, 0, 1, 0, 0)">
                            <path d="M21.35,11.1H12v2.7h5.38C16.88,16.48,14.7,18,12,18c-3.31,0-6-2.69-6-6s2.69-6,6-6c1.66,0,3.14,0.67,4.24,1.76l2-2C16.42,3.92,14.34,3,12,3C7.03,3,3,7.03,3,12s4.03,9,9,9c4.97,0,9-4.03,9-9C21,11.66,20.89,11.19,21.35,11.1z" fill="#34A853"/>
                            <path d="M21.35,11.1H12v2.7h5.38C16.88,16.48,14.7,18,12,18c-3.31,0-6-2.69-6-6s2.69-6,6-6c1.66,0,3.14,0.67,4.24,1.76l2-2C16.42,3.92,14.34,3,12,3C7.03,3,3,7.03,3,12s4.03,9,9,9c4.97,0,9-4.03,9-9C21,11.66,20.89,11.19,21.35,11.1z" fill="#4285F4"/>
                            <path d="M21.35,11.1H12v2.7h5.38C16.88,16.48,14.7,18,12,18c-3.31,0-6-2.69-6-6s2.69-6,6-6c1.66,0,3.14,0.67,4.24,1.76l2-2C16.42,3.92,14.34,3,12,3C7.03,3,3,7.03,3,12s4.03,9,9,9c4.97,0,9-4.03,9-9C21,11.66,20.89,11.19,21.35,11.1z" fill="#FBBC05"/>
                            <path d="M21.35,11.1H12v2.7h5.38C16.88,16.48,14.7,18,12,18c-3.31,0-6-2.69-6-6s2.69-6,6-6c1.66,0,3.14,0.67,4.24,1.76l2-2C16.42,3.92,14.34,3,12,3C7.03,3,3,7.03,3,12s4.03,9,9,9c4.97,0,9-4.03,9-9C21,11.66,20.89,11.19,21.35,11.1z" fill="#EA4335"/>
                        </g>
                    </svg>
                    <span>Masuk dengan Google</span>
                </a>
            </div>

        </div>
    </div>

    <!-- Footer -->
    <div class="mt-8 text-center text-2xs text-slate-500">
        &copy; 2026 Woundcare. All rights reserved.
    </div>

    <script>
        // Init Lucide
        lucide.createIcons();

        function switchTab(type) {
            const tabPhone = document.getElementById('tab-phone');
            const tabGoogle = document.getElementById('tab-google');
            const formPhone = document.getElementById('form-phone');
            const formGoogle = document.getElementById('form-google');

            if (type === 'phone') {
                tabPhone.classList.add('text-primary-600', 'border-primary-600', 'font-bold');
                tabPhone.classList.remove('text-slate-400', 'border-transparent', 'font-semibold');
                tabGoogle.classList.add('text-slate-400', 'border-transparent', 'font-semibold');
                tabGoogle.classList.remove('text-primary-600', 'border-primary-600', 'font-bold');
                
                formPhone.classList.remove('hidden');
                formGoogle.classList.add('hidden');
            } else {
                tabGoogle.classList.add('text-primary-600', 'border-primary-600', 'font-bold');
                tabGoogle.classList.remove('text-slate-400', 'border-transparent', 'font-semibold');
                tabPhone.classList.add('text-slate-400', 'border-transparent', 'font-semibold');
                tabPhone.classList.remove('text-primary-600', 'border-primary-600', 'font-bold');
                
                formGoogle.classList.remove('hidden');
                formPhone.classList.add('hidden');
            }
        }

        // Remember phone number logic
        const phoneInput = document.getElementById('whatsapp');
        const rememberCheckbox = document.getElementById('remember_me');

        if (phoneInput && rememberCheckbox) {
            const rememberedPhone = localStorage.getItem('remembered_whatsapp');
            if (rememberedPhone) {
                phoneInput.value = rememberedPhone;
                rememberCheckbox.checked = true;
            }
        }

        function saveRememberedPhone() {
            if (phoneInput && rememberCheckbox) {
                if (rememberCheckbox.checked) {
                    localStorage.setItem('remembered_whatsapp', phoneInput.value);
                } else {
                    localStorage.removeItem('remembered_whatsapp');
                }
            }
        }

        // Default tab selection logic:
        // Default to Google, but switch to phone tab if there are errors or old input
        const hasErrors = @json($errors->any());
        const hasOldInput = @json(old('whatsapp') !== null);

        if (hasErrors || hasOldInput) {
            switchTab('phone');
        } else {
            switchTab('google');
        }
    </script>
</body>
</html>
