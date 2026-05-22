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
<body class="min-h-full flex flex-col justify-center py-10 px-4 sm:px-6 lg:px-8 bg-gradient-to-tr from-slate-950 via-slate-900 to-indigo-950">
    <div class="sm:mx-auto sm:w-full sm:max-w-md text-center">
        <!-- Logo -->
        <div class="inline-flex w-14 h-14 rounded-2xl bg-gradient-to-tr from-primary-500 to-indigo-500 items-center justify-center text-white shadow-xl mb-4">
            <i data-lucide="activity" class="w-8 h-8"></i>
        </div>
        <h2 class="text-2xl font-extrabold font-outfit text-white tracking-tight">Woundcare</h2>
        <p class="mt-1 text-sm text-slate-400">Sistem Pencatatan Hasil Produksi & Verifikasi Gaji</p>
    </div>

    <div class="mt-6 sm:mx-auto sm:w-full sm:max-w-md">
        <div class="bg-white py-7 px-6 sm:py-8 sm:px-10 shadow-2xl rounded-3xl border border-slate-200/60">
            
            <!-- Toast Notifications -->
            @if(session('success'))
            <div class="mb-5 p-3.5 rounded-xl bg-emerald-50 border border-emerald-250 text-emerald-800 text-xs font-semibold flex items-center gap-2">
                <i data-lucide="check-circle" class="w-4.5 h-4.5 text-emerald-600"></i>
                <span>{{ session('success') }}</span>
            </div>
            @endif

            <!-- Client-side Dynamic Errors -->
            <div id="client-error-container" class="hidden mb-5 p-3.5 rounded-xl bg-rose-50 border border-rose-250 text-rose-800 text-xs flex flex-col gap-1">
                <div class="flex items-center gap-2 font-bold">
                    <i data-lucide="alert-circle" class="w-4.5 h-4.5 text-rose-650"></i>
                    <span id="client-error-title">Ada Kendala Masuk</span>
                </div>
                <p id="client-error-msg" class="text-2xs text-slate-600 pl-6.5 leading-relaxed"></p>
            </div>

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
            <div id="tab-container" class="flex border-b border-slate-100 mb-6">
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
            <div id="form-phone" class="space-y-4 hidden">
                <!-- Phone Input Block -->
                <div id="phone-input-block" class="space-y-4">
                    <div class="space-y-1.5">
                        <label for="whatsapp" class="text-xs font-bold text-slate-500 uppercase tracking-wider block">Nomor WhatsApp Terdaftar</label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-450">
                                <i data-lucide="phone" class="w-4.5 h-4.5"></i>
                            </span>
                            <input type="text" name="whatsapp" id="whatsapp" required inputmode="numeric" pattern="[0-9]*" oninput="this.value = this.value.replace(/[^0-9]/g, '')" placeholder="Contoh: 08123456789" value="{{ old('whatsapp') }}" class="pl-10 w-full rounded-xl border border-slate-350 py-3.5 px-4 text-sm bg-white text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all">
                        </div>
                        <p class="text-[11px] text-slate-400 leading-normal mt-1.5">Gunakan nomor WhatsApp aktif Anda yang sudah terdaftar di sistem.</p>
                    </div>

                    <!-- Remember Me Checkbox -->
                    <div class="flex items-center gap-2 py-1">
                        <input id="remember_me" name="remember_me" type="checkbox" class="h-4 w-4 text-primary-600 focus:ring-primary-500 border-slate-300 rounded cursor-pointer transition-all">
                        <label for="remember_me" class="block text-xs text-slate-500 cursor-pointer select-none font-semibold">Ingat Nomor Saya</label>
                    </div>

                    <button type="button" id="btn-send-magic" onclick="requestMagicLink()" class="w-full flex items-center justify-center gap-2 px-4 py-3 bg-office-900 hover:bg-slate-800 text-white text-sm font-bold rounded-xl shadow-md transition-all active:scale-[0.98]">
                        <i data-lucide="send" class="w-4 h-4"></i>
                        <span>Kirim Link Masuk</span>
                    </button>
                </div>

                <!-- Magic Link Sent Verification Block -->
                <div id="magic-sent-block" class="hidden text-center space-y-4 py-3.5">
                    <div class="flex justify-center">
                        <div class="relative flex items-center justify-center w-14 h-14 rounded-full bg-emerald-50 text-emerald-600">
                            <span class="absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-20 animate-ping"></span>
                            <i data-lucide="message-square" class="w-7 h-7 text-emerald-600 animate-bounce"></i>
                        </div>
                    </div>
                    <div class="space-y-1.5 px-2">
                        <h4 class="text-sm font-bold text-slate-850">Link Masuk Terkirim!</h4>
                        <p class="text-xs text-slate-555 leading-relaxed">
                            Kami telah mengirimkan link masuk aman ke nomor WhatsApp <strong id="sent-phone-display" class="text-slate-800"></strong>.
                        </p>
                        <p class="text-[11px] text-slate-400 leading-relaxed max-w-xs mx-auto">
                            Silakan buka aplikasi WhatsApp Anda, klik link masuk tersebut, dan Anda akan otomatis masuk ke dashboard ini.
                        </p>
                    </div>

                    <div class="pt-2 flex flex-col gap-2">
                        <button type="button" id="btn-resend-magic" onclick="requestMagicLink()" disabled class="w-full flex items-center justify-center gap-2 px-4 py-2.5 border border-slate-300 rounded-xl bg-white text-xs font-semibold text-slate-500 hover:bg-slate-50 disabled:opacity-50 disabled:cursor-not-allowed transition-all">
                            <i data-lucide="refresh-cw" class="w-3.5 h-3.5"></i>
                            <span id="resend-text">Kirim Ulang Link</span>
                        </button>
                        <button type="button" onclick="resetMagicLinkBlock()" class="w-full text-[11px] font-semibold text-slate-550 hover:text-slate-700 hover:underline py-1">
                            Ubah Nomor WhatsApp
                        </button>
                    </div>
                </div>
            </div>

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

            <!-- Emergency PIN Login Form -->
            <div id="form-pin" class="space-y-4 hidden">
                <form action="{{ route('login.emergency-pin') }}" method="POST" class="space-y-4">
                    @csrf
                    
                    <div class="space-y-2">
                        <label for="operator_id" class="text-xs font-bold text-slate-600 block">Pilih Nama Anda <span class="text-rose-500">*</span></label>
                        
                        <!-- Vendor Filter Tabs inside PIN Form -->
                        <div class="flex border border-slate-200 bg-slate-50/50 p-1 rounded-xl gap-1 mb-2 overflow-x-auto scrollbar-none">
                            <button type="button" onclick="filterVendor('ALL')" id="vfilter-ALL" class="vfilter-btn py-1.5 px-3 text-[10px] font-bold rounded-lg transition-all shrink-0 bg-white text-slate-800 shadow-sm border border-slate-100">
                                Semua
                            </button>
                            @foreach(['AA', 'IPS', 'JMI', 'KWI', 'MJA'] as $vName)
                                <button type="button" onclick="filterVendor('{{ $vName }}')" id="vfilter-{{ $vName }}" class="vfilter-btn py-1.5 px-3 text-[10px] font-bold rounded-lg transition-all shrink-0 text-slate-550 hover:text-slate-700 hover:bg-slate-100">
                                    {{ $vName }}
                                </button>
                            @endforeach
                        </div>

                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-450 z-10">
                                <i data-lucide="user" class="w-4.5 h-4.5"></i>
                            </span>
                            <select name="operator_id" id="operator_id" required class="pl-10 w-full rounded-xl border border-slate-300 py-3.5 px-4 text-sm bg-white text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all appearance-none cursor-pointer">
                                <option value="" disabled selected>-- Pilih Nama Karyawan --</option>
                                @foreach($staffs as $staff)
                                    <option value="{{ $staff->id }}" data-vendor="{{ $staff->vendor }}" @if(old('operator_id') == $staff->id) selected @endif>
                                        {{ $staff->name }} ({{ $staff->vendor }})
                                    </option>
                                @endforeach
                            </select>
                            <span class="absolute inset-y-0 right-0 pr-3.5 flex items-center pointer-events-none text-slate-450">
                                <i data-lucide="chevron-down" class="w-4 h-4"></i>
                            </span>
                        </div>
                    </div>

                    <div class="space-y-1.5">
                        <label for="pin" class="text-xs font-bold text-slate-600 block">PIN Darurat 6-Digit <span class="text-rose-500">*</span></label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-450">
                                <i data-lucide="key" class="w-4.5 h-4.5"></i>
                            </span>
                            <input type="text" name="pin" id="pin" required maxlength="6" inputmode="numeric" pattern="[0-9]*" oninput="this.value = this.value.replace(/[^0-9]/g, '')" placeholder="Contoh: 123456" class="pl-10 w-full rounded-xl border border-slate-300 py-3 px-4 text-sm bg-white text-slate-800 placeholder-slate-400 font-mono tracking-widest text-center focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all">
                        </div>
                        <p class="text-[10px] text-slate-500 leading-normal">
                            *Jika Anda lupa membawa HP, silakan minta PIN Darurat Sementara secara fisik kepada Koordinator Anda di lapangan. PIN berlaku selama 20 menit.
                        </p>
                    </div>

                    <button type="submit" class="w-full flex items-center justify-center gap-2 px-4 py-3 bg-office-900 hover:bg-slate-800 text-white text-sm font-bold rounded-xl shadow-md transition-all active:scale-[0.98]">
                        <i data-lucide="log-in" class="w-4.5 h-4.5"></i>
                        <span>Masuk Sistem</span>
                    </button>
                </form>

                <div class="text-center mt-4">
                    <button type="button" onclick="hidePinForm()" class="text-xs font-semibold text-slate-500 hover:text-primary-650 hover:underline transition-colors flex items-center justify-center gap-1.5 mx-auto">
                        <i data-lucide="arrow-left" class="w-3.5 h-3.5"></i>
                        <span>Kembali ke Login Utama</span>
                    </button>
                </div>
            </div>

            <!-- Forgot Phone Link -->
            <div id="forgot-phone-link-container" class="mt-6 pt-4 border-t border-slate-100 text-center">
                <button type="button" onclick="showPinForm()" class="text-xs font-semibold text-slate-500 hover:text-primary-600 hover:underline transition-colors flex items-center justify-center gap-1.5 mx-auto">
                    <i data-lucide="help-circle" class="w-3.5 h-3.5 text-slate-400"></i>
                    <span>Lupa bawa HP? Masuk via PIN Darurat</span>
                </button>
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

        // Vendor Option Filtering Logic for Native select
        let allStaffOptions = [];

        document.addEventListener('DOMContentLoaded', () => {
            const selectEl = document.getElementById('operator_id');
            if (selectEl) {
                const options = selectEl.querySelectorAll('option');
                options.forEach(opt => {
                    if (opt.value === '') return;
                    allStaffOptions.push({
                        id: opt.value,
                        name: opt.textContent.trim(),
                        vendor: opt.getAttribute('data-vendor')
                    });
                });
            }
        });

        function filterVendor(vendor) {
            const selectEl = document.getElementById('operator_id');
            if (!selectEl) return;

            // Highlight active vendor filter button
            const buttons = document.querySelectorAll('.vfilter-btn');
            buttons.forEach(btn => {
                if (btn.id === `vfilter-${vendor}`) {
                    btn.classList.add('bg-white', 'text-slate-800', 'shadow-sm', 'border', 'border-slate-100');
                    btn.classList.remove('text-slate-550', 'hover:text-slate-700', 'hover:bg-slate-100');
                } else {
                    btn.classList.remove('bg-white', 'text-slate-800', 'shadow-sm', 'border', 'border-slate-100');
                    btn.classList.add('text-slate-550', 'hover:text-slate-700', 'hover:bg-slate-100');
                }
            });

            // Save currently selected value to restore it if it's still present in the filtered list
            const currentSelectedValue = selectEl.value;

            // Clear select
            selectEl.innerHTML = '<option value="" disabled selected>-- Pilih Nama Karyawan --</option>';

            // Re-populate options
            const filteredOptions = allStaffOptions.filter(opt => vendor === 'ALL' || opt.vendor === vendor);
            
            filteredOptions.forEach(opt => {
                const newOpt = document.createElement('option');
                newOpt.value = opt.id;
                newOpt.textContent = opt.name;
                newOpt.setAttribute('data-vendor', opt.vendor);
                if (opt.id === currentSelectedValue) {
                    newOpt.selected = true;
                }
                selectEl.appendChild(newOpt);
            });
        }

        let activeTab = 'google';

        function switchTab(type) {
            activeTab = type;
            const tabs = {
                'phone': { btn: document.getElementById('tab-phone'), form: document.getElementById('form-phone') },
                'google': { btn: document.getElementById('tab-google'), form: document.getElementById('form-google') }
            };

            Object.keys(tabs).forEach(key => {
                const item = tabs[key];
                if (!item.btn || !item.form) return;

                if (key === type) {
                    item.btn.classList.add('text-primary-600', 'border-primary-600', 'font-bold');
                    item.btn.classList.remove('text-slate-400', 'border-transparent', 'font-semibold');
                    item.form.classList.remove('hidden');
                } else {
                    item.btn.classList.add('text-slate-400', 'border-transparent', 'font-semibold');
                    item.btn.classList.remove('text-primary-600', 'border-primary-600', 'font-bold');
                    item.form.classList.add('hidden');
                }
            });

            // Make sure PIN form and tabs show correctly
            document.getElementById('form-pin').classList.add('hidden');
            document.getElementById('tab-container').classList.remove('hidden');
            document.getElementById('forgot-phone-link-container').classList.remove('hidden');
        }

        function showPinForm() {
            // Hide normal tabs and forgot phone link container
            document.getElementById('tab-container').classList.add('hidden');
            document.getElementById('forgot-phone-link-container').classList.add('hidden');
            
            // Hide main forms
            document.getElementById('form-phone').classList.add('hidden');
            document.getElementById('form-google').classList.add('hidden');
            
            // Show PIN form
            document.getElementById('form-pin').classList.remove('hidden');
        }

        function hidePinForm() {
            // Show normal tabs and forgot phone link container
            document.getElementById('tab-container').classList.remove('hidden');
            document.getElementById('forgot-phone-link-container').classList.remove('hidden');
            
            // Restore active tab
            switchTab(activeTab);
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

        // Magic Link Logic
        let cooldownTimer = null;
        let cooldownSeconds = 0;

        function showClientError(msg) {
            const container = document.getElementById('client-error-container');
            const msgEl = document.getElementById('client-error-msg');
            if (container && msgEl) {
                msgEl.innerText = msg;
                container.classList.remove('hidden');
                container.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            }
        }

        function hideClientError() {
            const container = document.getElementById('client-error-container');
            if (container) {
                container.classList.add('hidden');
            }
        }

        function requestMagicLink() {
            hideClientError();

            const phone = phoneInput ? phoneInput.value.trim() : '';

            if (!phone) {
                showClientError('Silakan masukkan nomor WhatsApp Anda.');
                return;
            }

            // Save phone if remember me is checked
            saveRememberedPhone();

            const btnSend = document.getElementById('btn-send-magic');
            const btnResend = document.getElementById('btn-resend-magic');
            
            // Show loading
            if (btnSend) {
                btnSend.disabled = true;
                btnSend.innerHTML = '<i data-lucide="loader-2" class="w-4 h-4 animate-spin shrink-0"></i> <span>Mengirim Link...</span>';
                lucide.createIcons();
            }
            if (btnResend) {
                btnResend.disabled = true;
                btnResend.innerHTML = '<i data-lucide="loader-2" class="w-3.5 h-3.5 animate-spin shrink-0"></i> <span>Mengirim...</span>';
                lucide.createIcons();
            }

            fetch('/login/magic-link/send', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ whatsapp: phone })
            })
            .then(response => response.json().then(data => ({ status: response.status, body: data })))
            .then(res => {
                if (res.status === 200) {
                    // Success
                    document.getElementById('phone-input-block').classList.add('hidden');
                    document.getElementById('magic-sent-block').classList.remove('hidden');
                    document.getElementById('sent-phone-display').innerText = phone;

                    // Start cooldown
                    startResendCooldown();
                } else {
                    // Error
                    showClientError(res.body.message || 'Gagal mengirim link masuk. Silakan coba kembali.');
                    resetButtons();
                }
            })
            .catch(err => {
                showClientError('Terjadi kesalahan jaringan. Silakan hubungi admin.');
                resetButtons();
            });
        }

        function resetButtons() {
            const btnSend = document.getElementById('btn-send-magic');
            if (btnSend) {
                btnSend.disabled = false;
                btnSend.innerHTML = '<i data-lucide="send" class="w-4 h-4"></i> <span>Kirim Link Masuk</span>';
            }
            const btnResend = document.getElementById('btn-resend-magic');
            if (btnResend) {
                btnResend.disabled = cooldownSeconds > 0;
                btnResend.innerHTML = '<i data-lucide="refresh-cw" class="w-3.5 h-3.5"></i> <span id="resend-text">Kirim Ulang Link</span>';
            }
            lucide.createIcons();
        }

        function resetMagicLinkBlock() {
            document.getElementById('phone-input-block').classList.remove('hidden');
            document.getElementById('magic-sent-block').classList.add('hidden');
            resetButtons();
            
            // Stop cooldown timer
            if (cooldownTimer) {
                clearInterval(cooldownTimer);
                cooldownSeconds = 0;
            }
        }

        function startResendCooldown() {
            cooldownSeconds = 60;
            const btnResend = document.getElementById('btn-resend-magic');
            const resendText = document.getElementById('resend-text');

            if (cooldownTimer) clearInterval(cooldownTimer);

            if (btnResend) btnResend.disabled = true;

            cooldownTimer = setInterval(() => {
                cooldownSeconds--;
                if (cooldownSeconds <= 0) {
                    clearInterval(cooldownTimer);
                    if (btnResend) btnResend.disabled = false;
                    if (resendText) resendText.innerText = 'Kirim Ulang Link';
                } else {
                    if (resendText) resendText.innerText = `Kirim Ulang Link (${cooldownSeconds}s)`;
                }
            }, 1000);
        }

        // Default tab selection logic:
        // Default to Google, but switch to phone tab if there are errors or old input
        const hasErrors = @json($errors->any());
        const hasOldInput = @json(old('whatsapp') !== null);
        const hasOldPin = @json(old('pin') !== null || old('operator_id') !== null);

        if (hasOldPin) {
            showPinForm();
        } else if (hasErrors || hasOldInput) {
            switchTab('phone');
        } else {
            switchTab('google');
        }
    </script>
</body>
</html>
