<!DOCTYPE html>
<html lang="id" class="h-full bg-slate-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Portal Admin Produksi - Loka Medical Systems</title>
    
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
                            500: '#0ea5e9', // Sky blue
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
                            900: '#0f172a', // Deep corporate dark
                        }
                    }
                }
            }
        }
    </script>

    <style>
        body {
            font-family: 'Inter', sans-serif;
        }

        /* Custom scrollbar for history list */
        .custom-scroll::-webkit-scrollbar {
            width: 6px;
        }
        .custom-scroll::-webkit-scrollbar-track {
            background: transparent;
        }
        .custom-scroll::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 3px;
        }
        .custom-scroll::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }

        /* Radio Card transition */
        .radio-card {
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        }
    </style>
</head>
<body class="min-h-full flex flex-col text-slate-800">

    <!-- Navbar Header -->
    <header class="bg-office-900 text-white shadow-lg sticky top-0 z-30 shrink-0">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <!-- Branding -->
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-lg bg-gradient-to-tr from-primary-500 to-indigo-500 flex items-center justify-center text-white shadow-md">
                        <i data-lucide="activity" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <span class="text-sm font-semibold tracking-wider text-slate-400 block uppercase leading-none font-outfit">Loka Medical</span>
                        <h1 class="text-base font-extrabold font-outfit tracking-tight text-white leading-tight">Portal Admin Produksi</h1>
                    </div>
                </div>
                
                <!-- Status Info -->
                <div class="flex items-center gap-3">
                    <span class="hidden md:inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-slate-800 border border-slate-700 text-xs font-semibold text-slate-350">
                        <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                        Sistem Aktif
                    </span>
                    <span class="text-xs font-medium text-slate-400 bg-slate-800/50 px-2.5 py-1 rounded border border-slate-800">Mobile-Optimized</span>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content Panel -->
    <main class="flex-1 max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-6">
        
        <!-- Notifications / Toasts -->
        @if(session('success'))
        <div id="successToast" class="flex items-center justify-between p-4 mb-6 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 shadow-md animate-fade-in">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-lg bg-emerald-500/20 text-emerald-600 flex items-center justify-center shrink-0">
                    <i data-lucide="check-circle" class="w-5 h-5"></i>
                </div>
                <p class="text-sm font-semibold">{{ session('success') }}</p>
            </div>
            <button onclick="document.getElementById('successToast').remove()" class="p-1 hover:bg-emerald-100 rounded-lg text-emerald-600 transition-all shrink-0">
                <i data-lucide="x" class="w-4 h-4"></i>
            </button>
        </div>
        @endif

        @if($errors->any())
        <div id="errorToast" class="p-4 mb-6 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 shadow-md">
            <div class="flex items-center justify-between mb-2 border-b border-rose-200/50 pb-2">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-rose-500/20 text-rose-600 flex items-center justify-center shrink-0">
                        <i data-lucide="alert-circle" class="w-5 h-5"></i>
                    </div>
                    <p class="text-sm font-bold">Terjadi Kesalahan Input</p>
                </div>
                <button onclick="document.getElementById('errorToast').remove()" class="p-1 hover:bg-rose-100 rounded-lg text-rose-600 transition-all shrink-0">
                    <i data-lucide="x" class="w-4 h-4"></i>
                </button>
            </div>
            <ul class="list-disc pl-5 text-xs space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <!-- Grid Layout: Form and Logs/Stats -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
            
            <!-- Left Side: Interactive Input Form (Takes 7 cols on desktop) -->
            <div class="lg:col-span-7 bg-white rounded-xl shadow-sm border border-slate-200/80 overflow-hidden">
                
                <!-- Card Header -->
                <div class="bg-office-750 text-white p-5 flex items-center justify-between border-b border-slate-200">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-lg bg-primary-500/10 text-primary-400 flex items-center justify-center">
                            <i data-lucide="edit-3" class="w-4.5 h-4.5"></i>
                        </div>
                        <div>
                            <h2 class="text-base font-bold font-outfit text-white">Input Laporan Harian</h2>
                            <p class="text-2xs text-slate-400">Silakan isi seluruh parameter produksi berikut dengan benar</p>
                        </div>
                    </div>
                    <span class="text-3xs bg-rose-500/20 text-rose-450 border border-rose-500/35 font-bold px-2 py-0.5 rounded uppercase tracking-wider">
                        Wajib Diisi *
                    </span>
                </div>

                <!-- Form Body -->
                <form id="productionForm" action="{{ route('wound-reports.store') }}" method="POST" class="p-5 sm:p-6 space-y-5">
                    @csrf

                    <!-- SECTION 1: Waktu & Shift -->
                    <div class="bg-slate-50 rounded-xl p-4 border border-slate-200/60 space-y-4">
                        <div class="flex items-center gap-2 border-b border-slate-200/80 pb-2 mb-1">
                            <i data-lucide="clock" class="w-4 h-4 text-primary-600"></i>
                            <h3 class="text-xs font-bold uppercase tracking-wider text-slate-500 font-outfit">Detail Waktu & Shift</h3>
                        </div>

                        <!-- Date input wrapper -->
                        <div class="space-y-1.5">
                            <label class="text-xs font-bold text-slate-650 flex items-center gap-1.5">
                                Tanggal <span class="text-rose-500">*</span>
                                <span class="text-3xs font-medium text-slate-400 uppercase tracking-wider">(Date)</span>
                            </label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-450">
                                    <i data-lucide="calendar" class="w-4 h-4"></i>
                                </span>
                                <input type="date" name="tanggal" required class="pl-10 w-full rounded-lg border border-slate-300 py-2.5 px-3.5 text-sm bg-white text-slate-800 focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all cursor-pointer" value="{{ date('Y-m-d') }}">
                            </div>
                        </div>

                        <!-- Touch-friendly Shift Radio Cards -->
                        <div class="space-y-2">
                            <label class="text-xs font-bold text-slate-650 block">Shift Kerja <span class="text-rose-500">*</span></label>
                            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-2">
                                
                                <label class="relative flex items-center gap-3 p-3 rounded-lg border border-slate-300 bg-white cursor-pointer radio-card hover:border-slate-400 select-none">
                                    <input type="radio" name="shift" value="Shift 1" checked class="sr-only peer" onchange="updateRadioStyles()">
                                    <div class="w-4.5 h-4.5 rounded-full border-2 border-slate-400 flex items-center justify-center shrink-0 peer-checked:border-primary-600 peer-checked:bg-primary-600 transition-colors">
                                        <div class="w-2 h-2 rounded-full bg-white scale-0 peer-checked:scale-100 transition-transform"></div>
                                    </div>
                                    <div class="text-left leading-none">
                                        <span class="text-xs font-bold text-slate-800 block">Shift 1</span>
                                        <span class="text-3xs text-slate-450 mt-0.5 block">Pagi / Regular</span>
                                    </div>
                                    <div class="absolute inset-0 border border-transparent rounded-lg pointer-events-none peer-checked:border-primary-500 peer-checked:bg-primary-50/20 transition-all"></div>
                                </label>

                                <label class="relative flex items-center gap-3 p-3 rounded-lg border border-slate-300 bg-white cursor-pointer radio-card hover:border-slate-400 select-none">
                                    <input type="radio" name="shift" value="Shift 2" class="sr-only peer" onchange="updateRadioStyles()">
                                    <div class="w-4.5 h-4.5 rounded-full border-2 border-slate-400 flex items-center justify-center shrink-0 peer-checked:border-primary-600 peer-checked:bg-primary-600 transition-colors">
                                        <div class="w-2 h-2 rounded-full bg-white scale-0 peer-checked:scale-100 transition-transform"></div>
                                    </div>
                                    <div class="text-left leading-none">
                                        <span class="text-xs font-bold text-slate-800 block">Shift 2</span>
                                        <span class="text-3xs text-slate-450 mt-0.5 block">Siang / Regular</span>
                                    </div>
                                    <div class="absolute inset-0 border border-transparent rounded-lg pointer-events-none peer-checked:border-primary-500 peer-checked:bg-primary-50/20 transition-all"></div>
                                </label>

                                <label class="relative flex items-center gap-3 p-3 rounded-lg border border-slate-300 bg-white cursor-pointer radio-card hover:border-slate-400 select-none">
                                    <input type="radio" name="shift" value="Shift 3" class="sr-only peer" onchange="updateRadioStyles()">
                                    <div class="w-4.5 h-4.5 rounded-full border-2 border-slate-400 flex items-center justify-center shrink-0 peer-checked:border-primary-600 peer-checked:bg-primary-600 transition-colors">
                                        <div class="w-2 h-2 rounded-full bg-white scale-0 peer-checked:scale-100 transition-transform"></div>
                                    </div>
                                    <div class="text-left leading-none">
                                        <span class="text-xs font-bold text-slate-800 block">Shift 3</span>
                                        <span class="text-3xs text-slate-450 mt-0.5 block">Malam / Regular</span>
                                    </div>
                                    <div class="absolute inset-0 border border-transparent rounded-lg pointer-events-none peer-checked:border-primary-500 peer-checked:bg-primary-50/20 transition-all"></div>
                                </label>

                                <label class="relative flex items-center gap-3 p-3 rounded-lg border border-slate-300 bg-white cursor-pointer radio-card hover:border-slate-400 select-none sm:col-span-2 lg:col-span-1.5">
                                    <input type="radio" name="shift" value="Shift 1 (Longshift)" class="sr-only peer" onchange="updateRadioStyles()">
                                    <div class="w-4.5 h-4.5 rounded-full border-2 border-slate-400 flex items-center justify-center shrink-0 peer-checked:border-primary-600 peer-checked:bg-primary-600 transition-colors">
                                        <div class="w-2 h-2 rounded-full bg-white scale-0 peer-checked:scale-100 transition-transform"></div>
                                    </div>
                                    <div class="text-left leading-none">
                                        <span class="text-xs font-bold text-slate-800 block">Shift 1 (Long)</span>
                                        <span class="text-3xs text-slate-450 mt-0.5 block">Pagi / Lembur</span>
                                    </div>
                                    <div class="absolute inset-0 border border-transparent rounded-lg pointer-events-none peer-checked:border-primary-500 peer-checked:bg-primary-50/20 transition-all"></div>
                                </label>

                                <label class="relative flex items-center gap-3 p-3 rounded-lg border border-slate-300 bg-white cursor-pointer radio-card hover:border-slate-400 select-none sm:col-span-2 lg:col-span-1.5">
                                    <input type="radio" name="shift" value="Shift 2 (Longshift)" class="sr-only peer" onchange="updateRadioStyles()">
                                    <div class="w-4.5 h-4.5 rounded-full border-2 border-slate-400 flex items-center justify-center shrink-0 peer-checked:border-primary-600 peer-checked:bg-primary-600 transition-colors">
                                        <div class="w-2 h-2 rounded-full bg-white scale-0 peer-checked:scale-100 transition-transform"></div>
                                    </div>
                                    <div class="text-left leading-none">
                                        <span class="text-xs font-bold text-slate-800 block">Shift 2 (Long)</span>
                                        <span class="text-3xs text-slate-450 mt-0.5 block">Siang / Lembur</span>
                                    </div>
                                    <div class="absolute inset-0 border border-transparent rounded-lg pointer-events-none peer-checked:border-primary-500 peer-checked:bg-primary-50/20 transition-all"></div>
                                </label>

                            </div>
                        </div>

                    </div>

                    <!-- SECTION: Identitas Vendor & Operator -->
                    <div class="bg-slate-50 rounded-xl p-4 border border-slate-200/60 space-y-4">
                        <div class="flex items-center gap-2 border-b border-slate-200/80 pb-2 mb-1">
                            <i data-lucide="users" class="w-4 h-4 text-primary-600"></i>
                            <h3 class="text-xs font-bold uppercase tracking-wider text-slate-500 font-outfit">Identitas Vendor & Operator</h3>
                        </div>

                        <!-- PILIH VENDOR radio grid -->
                        <div class="space-y-2">
                            <label class="text-xs font-bold text-slate-650 block">Pilih Vendor <span class="text-rose-500">*</span></label>
                            <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
                                <label class="relative flex items-center gap-2.5 p-2.5 rounded-lg border border-slate-300 bg-white cursor-pointer radio-card hover:border-slate-400 select-none">
                                    <input type="radio" name="vendor" value="KWI" checked class="sr-only peer" onchange="updateRadioStyles(); toggleVendorOther(); updateOperatorOptions()">
                                    <div class="w-4 h-4 rounded-full border-2 border-slate-400 flex items-center justify-center shrink-0 peer-checked:border-primary-600 peer-checked:bg-primary-600 transition-colors">
                                        <div class="w-1.5 h-1.5 rounded-full bg-white scale-0 peer-checked:scale-100 transition-transform"></div>
                                    </div>
                                    <span class="text-xs font-bold text-slate-800">KWI</span>
                                    <div class="absolute inset-0 border border-transparent rounded-lg pointer-events-none peer-checked:border-primary-500 peer-checked:bg-primary-50/10 transition-all"></div>
                                </label>

                                <label class="relative flex items-center gap-2.5 p-2.5 rounded-lg border border-slate-300 bg-white cursor-pointer radio-card hover:border-slate-400 select-none">
                                    <input type="radio" name="vendor" value="MJA" class="sr-only peer" onchange="updateRadioStyles(); toggleVendorOther(); updateOperatorOptions()">
                                    <div class="w-4 h-4 rounded-full border-2 border-slate-400 flex items-center justify-center shrink-0 peer-checked:border-primary-600 peer-checked:bg-primary-600 transition-colors">
                                        <div class="w-1.5 h-1.5 rounded-full bg-white scale-0 peer-checked:scale-100 transition-transform"></div>
                                    </div>
                                    <span class="text-xs font-bold text-slate-800">MJA</span>
                                    <div class="absolute inset-0 border border-transparent rounded-lg pointer-events-none peer-checked:border-primary-500 peer-checked:bg-primary-50/10 transition-all"></div>
                                </label>

                                <label class="relative flex items-center gap-2.5 p-2.5 rounded-lg border border-slate-300 bg-white cursor-pointer radio-card hover:border-slate-400 select-none">
                                    <input type="radio" name="vendor" value="-" class="sr-only peer" onchange="updateRadioStyles(); toggleVendorOther(); updateOperatorOptions()">
                                    <div class="w-4 h-4 rounded-full border-2 border-slate-400 flex items-center justify-center shrink-0 peer-checked:border-primary-600 peer-checked:bg-primary-600 transition-colors">
                                        <div class="w-1.5 h-1.5 rounded-full bg-white scale-0 peer-checked:scale-100 transition-transform"></div>
                                    </div>
                                    <span class="text-xs font-bold text-slate-800">-</span>
                                    <div class="absolute inset-0 border border-transparent rounded-lg pointer-events-none peer-checked:border-primary-500 peer-checked:bg-primary-50/10 transition-all"></div>
                                </label>

                                <label class="relative flex items-center gap-2.5 p-2.5 rounded-lg border border-slate-300 bg-white cursor-pointer radio-card hover:border-slate-400 select-none">
                                    <input type="radio" name="vendor" value="AA" class="sr-only peer" onchange="updateRadioStyles(); toggleVendorOther(); updateOperatorOptions()">
                                    <div class="w-4 h-4 rounded-full border-2 border-slate-400 flex items-center justify-center shrink-0 peer-checked:border-primary-600 peer-checked:bg-primary-600 transition-colors">
                                        <div class="w-1.5 h-1.5 rounded-full bg-white scale-0 peer-checked:scale-100 transition-transform"></div>
                                    </div>
                                    <span class="text-xs font-bold text-slate-800">AA</span>
                                    <div class="absolute inset-0 border border-transparent rounded-lg pointer-events-none peer-checked:border-primary-500 peer-checked:bg-primary-50/10 transition-all"></div>
                                </label>

                                <label class="relative flex items-center gap-2.5 p-2.5 rounded-lg border border-slate-300 bg-white cursor-pointer radio-card hover:border-slate-400 select-none">
                                    <input type="radio" name="vendor" value="IPS" class="sr-only peer" onchange="updateRadioStyles(); toggleVendorOther(); updateOperatorOptions()">
                                    <div class="w-4 h-4 rounded-full border-2 border-slate-400 flex items-center justify-center shrink-0 peer-checked:border-primary-600 peer-checked:bg-primary-600 transition-colors">
                                        <div class="w-1.5 h-1.5 rounded-full bg-white scale-0 peer-checked:scale-100 transition-transform"></div>
                                    </div>
                                    <span class="text-xs font-bold text-slate-800">IPS</span>
                                    <div class="absolute inset-0 border border-transparent rounded-lg pointer-events-none peer-checked:border-primary-500 peer-checked:bg-primary-50/10 transition-all"></div>
                                </label>

                                <label class="relative flex items-center gap-2.5 p-2.5 rounded-lg border border-slate-300 bg-white cursor-pointer radio-card hover:border-slate-400 select-none">
                                    <input type="radio" name="vendor" value="JMI" class="sr-only peer" onchange="updateRadioStyles(); toggleVendorOther(); updateOperatorOptions()">
                                    <div class="w-4 h-4 rounded-full border-2 border-slate-400 flex items-center justify-center shrink-0 peer-checked:border-primary-600 peer-checked:bg-primary-600 transition-colors">
                                        <div class="w-1.5 h-1.5 rounded-full bg-white scale-0 peer-checked:scale-100 transition-transform"></div>
                                    </div>
                                    <span class="text-xs font-bold text-slate-800">JMI</span>
                                    <div class="absolute inset-0 border border-transparent rounded-lg pointer-events-none peer-checked:border-primary-500 peer-checked:bg-primary-50/10 transition-all"></div>
                                </label>

                                <label class="relative flex items-center gap-2.5 p-2.5 rounded-lg border border-slate-300 bg-white cursor-pointer radio-card hover:border-slate-400 select-none sm:col-span-2">
                                    <input type="radio" name="vendor" value="Other" class="sr-only peer" onchange="updateRadioStyles(); toggleVendorOther(); updateOperatorOptions()">
                                    <div class="w-4 h-4 rounded-full border-2 border-slate-400 flex items-center justify-center shrink-0 peer-checked:border-primary-600 peer-checked:bg-primary-600 transition-colors">
                                        <div class="w-1.5 h-1.5 rounded-full bg-white scale-0 peer-checked:scale-100 transition-transform"></div>
                                    </div>
                                    <span class="text-xs font-bold text-slate-800">Other:</span>
                                    <div class="absolute inset-0 border border-transparent rounded-lg pointer-events-none peer-checked:border-primary-500 peer-checked:bg-primary-50/10 transition-all"></div>
                                </label>
                            </div>
                        </div>

                        <!-- Vendor Other custom text field -->
                        <div id="vendorOtherContainer" class="hidden space-y-1.5">
                            <label class="text-xs font-semibold text-slate-500 block">Nama Vendor Kustom <span class="text-rose-500">*</span></label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-450">
                                    <i data-lucide="edit-2" class="w-4 h-4"></i>
                                </span>
                                <input type="text" id="vendor_other_text" placeholder="Tuliskan nama vendor lainnya..." class="pl-10 w-full rounded-lg border border-slate-300 py-2.5 px-3.5 text-sm bg-white text-slate-800 focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all">
                            </div>
                        </div>

                        <!-- NAMA OPERATOR dropdown -->
                        <div class="space-y-1.5">
                            <label class="text-xs font-bold text-slate-650 block">Nama Operator <span class="text-rose-500">*</span></label>
                            <select name="operator" id="operator_select" required class="w-full rounded-lg border border-slate-300 py-2.5 px-3 text-sm bg-white text-slate-800 focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all cursor-pointer">
                                <option value="" disabled selected>Choose</option>
                            </select>
                        </div>
                    </div>

                    <!-- SECTION 2: Spesifikasi & Jenis Produk -->
                    <div class="bg-slate-50 rounded-xl p-4 border border-slate-200/60 space-y-4">
                        <div class="flex items-center gap-2 border-b border-slate-200/80 pb-2 mb-1">
                            <i data-lucide="package" class="w-4 h-4 text-primary-600"></i>
                            <h3 class="text-xs font-bold uppercase tracking-wider text-slate-500 font-outfit">Spesifikasi Produk</h3>
                        </div>

                        <!-- Grid row: Pengerjaan & Jenis Produk -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div class="space-y-1.5">
                                <label class="text-xs font-bold text-slate-650 block">Pengerjaan <span class="text-rose-500">*</span></label>
                                <select name="pengerjaan" required class="w-full rounded-lg border border-slate-300 py-2.5 px-3 text-sm bg-white text-slate-800 focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all cursor-pointer">
                                    <option value="" disabled selected>Choose</option>
                                    <option value="OPERATOR">OPERATOR</option>
                                    <option value="PACKING">PACKING</option>
                                    <option value="ASSEMBLING">ASSEMBLING</option>
                                    <option value="GULUNG">GULUNG</option>
                                    <option value="GULUNG + PACKING">GULUNG + PACKING</option>
                                    <option value="POUCH + SEAL + PACKING">POUCH + SEAL + PACKING</option>
                                    <option value="POUCH + SEAL">POUCH + SEAL</option>
                                    <option value="POTONG">POTONG</option>
                                    <option value="GULUNG BB SAMBUNGAN">GULUNG BB SAMBUNGAN</option>
                                    <option value="SPLIT BB">SPLIT BB</option>
                                    <option value="PLONG">PLONG</option>
                                    <option value="SEAL">SEAL</option>
                                    <option value="POUCH + PACK">POUCH + PACK</option>
                                    <option value="MEMBERSIHKAN BB">MEMBERSIHKAN BB</option>
                                </select>
                            </div>

                            <div class="space-y-1.5">
                                <label class="text-xs font-bold text-slate-650 block">Jenis Produk <span class="text-rose-500">*</span></label>
                                <select name="jenis_produk" required class="w-full rounded-lg border border-slate-300 py-2.5 px-3 text-sm bg-white text-slate-800 focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all cursor-pointer">
                                    <option value="" disabled selected>Choose</option>
                                    <option value="ULTRAFIX">ULTRAFIX</option>
                                    <option value="PLESTERIN ROLL">PLESTERIN ROLL</option>
                                    <option value="ISOPLAST">ISOPLAST</option>
                                    <option value="ISOPORE">ISOPORE</option>
                                    <option value="ISOFIX">ISOFIX</option>
                                    <option value="ECG PAPER">ECG PAPER</option>
                                    <option value="PLESTERIN">PLESTERIN</option>
                                    <option value="STERIL POUCH">STERIL POUCH</option>
                                    <option value="EKAPLAST">EKAPLAST</option>
                                    <option value="DERMAFIX">DERMAFIX</option>
                                    <option value="HOTMELT">HOTMELT</option>
                                    <option value="SLITING">SLITING</option>
                                    <option value="POUCH">POUCH</option>
                                    <option value="FLEXO">FLEXO</option>
                                    <option value="RIWEND">RIWEND</option>
                                    <option value="K-ONE">K-ONE</option>
                                    <option value="SUPERFIX">SUPERFIX</option>
                                    <option value="C-DOT">C-DOT</option>
                                    <option value="SUPERFIX SENSITIVE">SUPERFIX SENSITIVE</option>
                                    <option value="SILICONE TAPE">SILICONE TAPE</option>
                                    <option value="LIPO FOAM">LIPO FOAM</option>
                                    <option value="MEDIFLEX">MEDIFLEX</option>
                                    <option value="DISPOSABLE MOUTHPIECE SPIROMETER">DISPOSABLE MOUTHPIECE SPIROMETER</option>
                                </select>
                            </div>
                        </div>

                        <!-- Produk Yang Dikerjakan -->
                        <div class="space-y-1.5">
                            <label class="text-xs font-bold text-slate-650 block">Produk yang Dikerjakan <span class="text-rose-500">*</span></label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-450">
                                    <i data-lucide="tag" class="w-4 h-4"></i>
                                </span>
                                <input type="text" name="produk_yang_dikerjakan" required placeholder="Tuliskan nama lengkap/seri produk" class="pl-10 w-full rounded-lg border border-slate-300 py-2.5 px-3.5 text-sm bg-white text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all">
                            </div>
                        </div>
                    </div>

                    <!-- SECTION 3: Output & Keterangan -->
                    <div class="bg-slate-50 rounded-xl p-4 border border-slate-200/60 space-y-4">
                        <div class="flex items-center gap-2 border-b border-slate-200/80 pb-2 mb-1">
                            <i data-lucide="activity" class="w-4 h-4 text-primary-600"></i>
                            <h3 class="text-xs font-bold uppercase tracking-wider text-slate-500 font-outfit">Output & Volume Hasil</h3>
                        </div>

                        <!-- Grid row: Hasil & Satuan -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div class="space-y-1.5">
                                <label class="text-xs font-bold text-slate-650 block">Hasil <span class="text-rose-500">*</span></label>
                                <div class="relative">
                                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-450">
                                        <i data-lucide="hash" class="w-4 h-4"></i>
                                    </span>
                                    <input type="number" name="hasil" required placeholder="0" min="0" class="pl-10 w-full rounded-lg border border-slate-300 py-2.5 px-3.5 text-sm bg-white text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all">
                                </div>
                            </div>

                            <div class="space-y-1.5">
                                <label class="text-xs font-bold text-slate-650 block">Satuan <span class="text-rose-500">*</span></label>
                                <select name="satuan" required class="w-full rounded-lg border border-slate-300 py-2.5 px-3 text-sm bg-white text-slate-800 focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all cursor-pointer">
                                    <option value="" disabled selected>Choose</option>
                                    <option value="PCS">PCS</option>
                                    <option value="ROLL">ROLL</option>
                                    <option value="BOX">BOX</option>
                                    <option value="DOS">DOS</option>
                                    <option value="TAX">TAX</option>
                                    <option value="M">M</option>
                                    <option value="RENCENG">RENCENG</option>
                                    <option value="AMPLOP">AMPLOP</option>
                                    <option value="PAC">PAC</option>
                                </select>
                            </div>
                        </div>

                        <!-- Keterangan -->
                        <div class="space-y-1.5">
                            <label class="text-xs font-bold text-slate-650 block">Keterangan Tambahan <span class="text-rose-500">*</span></label>
                            <div class="relative">
                                <span class="absolute top-3 left-3 pointer-events-none text-slate-450">
                                    <i data-lucide="file-text" class="w-4 h-4"></i>
                                </span>
                                <textarea name="keterangan" required rows="2" placeholder="Tuliskan catatan pengerjaan atau kendala jika ada" class="pl-10 w-full rounded-lg border border-slate-300 py-2.5 px-3.5 text-sm bg-white text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all resize-none"></textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Action buttons wrapper -->
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 border-t border-slate-100 pt-5">
                        <button type="button" onclick="clearForm()" class="order-2 sm:order-1 w-full sm:w-auto px-5 py-2.5 rounded-lg border border-slate-300 text-slate-500 font-semibold text-xs hover:bg-slate-50 hover:text-slate-700 transition-all active:scale-[0.98]">
                            Reset Formulir
                        </button>
                        <button type="submit" class="order-1 sm:order-2 w-full sm:w-auto px-6 py-2.5 rounded-lg bg-office-900 hover:bg-slate-800 text-white font-semibold text-xs shadow-md transition-all active:scale-[0.98] flex items-center justify-center gap-2">
                            <i data-lucide="check" class="w-4 h-4"></i> Simpan Laporan Kerja
                        </button>
                    </div>

                </form>

            </div>

            <!-- Right Side: Statistics & History Logs (Takes 5 cols on desktop) -->
            <div class="lg:col-span-5 space-y-6">
                
                <!-- Quick Metrics Widgets -->
                <div class="grid grid-cols-2 gap-4">
                    <!-- Widget 1: Total volume of products -->
                    <div class="bg-white p-4 rounded-xl border border-slate-200/80 shadow-sm relative overflow-hidden">
                        <div class="absolute -right-2 -bottom-2 w-14 h-14 text-slate-50">
                            <i data-lucide="package" class="w-full h-full opacity-10"></i>
                        </div>
                        <span class="text-3xs font-extrabold uppercase text-slate-400 tracking-wider block">Total Volume</span>
                        <h3 class="text-xl font-bold font-outfit text-slate-800 mt-1">
                            @php
                                $totalVolume = 0;
                                foreach($reports as $r) {
                                    $totalVolume += $r->hasil;
                                }
                                echo number_format($totalVolume);
                            @endphp
                            <span class="text-3xs font-medium text-slate-400">unit</span>
                        </h3>
                        <span class="text-3xs text-emerald-600 font-semibold flex items-center gap-0.5 mt-1">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                            Dari seluruh pengerjaan
                        </span>
                    </div>

                    <!-- Widget 2: Count of submissions -->
                    <div class="bg-white p-4 rounded-xl border border-slate-200/80 shadow-sm relative overflow-hidden">
                        <div class="absolute -right-2 -bottom-2 w-14 h-14 text-slate-50">
                            <i data-lucide="database" class="w-full h-full opacity-10"></i>
                        </div>
                        <span class="text-3xs font-extrabold uppercase text-slate-400 tracking-wider block">Total Laporan</span>
                        <h3 class="text-xl font-bold font-outfit text-slate-800 mt-1">
                            {{ count($reports) }}
                            <span class="text-3xs font-medium text-slate-400">log</span>
                        </h3>
                        <span class="text-3xs text-primary-600 font-semibold flex items-center gap-0.5 mt-1">
                            <span class="w-1.5 h-1.5 rounded-full bg-primary-500"></span>
                            Terdaftar di SQLite
                        </span>
                    </div>
                </div>

                <!-- Database Records List -->
                <div class="bg-white rounded-xl shadow-sm border border-slate-200/80 overflow-hidden">
                    
                    <!-- Box Header -->
                    <div class="p-4 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
                        <div class="flex items-center gap-2">
                            <i data-lucide="history" class="w-4 h-4 text-slate-400"></i>
                            <h3 class="text-xs font-bold uppercase tracking-wider text-slate-500 font-outfit">Daftar Riwayat Kerja</h3>
                        </div>
                        <a href="{{ route('dashboard') }}" class="text-3xs text-primary-600 hover:text-primary-700 font-bold flex items-center gap-0.5">
                            <i data-lucide="rotate-cw" class="w-2.5 h-2.5"></i> Refresh
                        </a>
                    </div>

                    <!-- List body -->
                    <div class="p-4 space-y-3 max-h-[580px] overflow-y-auto custom-scroll">
                        
                        @if(count($reports) == 0)
                        <!-- Empty list view -->
                        <div class="text-center py-10 space-y-2.5">
                            <div class="w-10 h-10 rounded-full bg-slate-50 border border-slate-200 flex items-center justify-center mx-auto text-slate-400">
                                <i data-lucide="folder-open" class="w-5 h-5"></i>
                            </div>
                            <div>
                                <h4 class="text-xs font-bold text-slate-650">Tidak ada riwayat kerja</h4>
                                <p class="text-3xs text-slate-400 mt-0.5 max-w-[200px] mx-auto">Mulai merekam log pengerjaan menggunakan form input.</p>
                            </div>
                        </div>
                        @else
                        
                        <!-- List entries -->
                        @foreach($reports as $report)
                        <div class="p-3.5 rounded-lg border border-slate-200/80 hover:border-slate-300 hover:bg-slate-50/30 transition-all bg-white relative group">
                            
                            <!-- Badges & actions row -->
                            <div class="flex items-start justify-between gap-3 mb-2">
                                <div class="flex flex-wrap items-center gap-1.5">
                                    <!-- Shift Badge with dynamic color -->
                                    @php
                                        $badgeColor = 'bg-slate-100 text-slate-700 border-slate-200';
                                        if ($report->shift == 'Shift 1') $badgeColor = 'bg-sky-50 text-sky-700 border-sky-200';
                                        elseif ($report->shift == 'Shift 2') $badgeColor = 'bg-emerald-50 text-emerald-700 border-emerald-200';
                                        elseif ($report->shift == 'Shift 3') $badgeColor = 'bg-purple-50 text-purple-700 border-purple-200';
                                        elseif (str_contains($report->shift, 'Longshift')) $badgeColor = 'bg-amber-50 text-amber-700 border-amber-200';
                                    @endphp
                                    <span class="px-2 py-0.5 rounded text-3xs font-bold border {{ $badgeColor }} uppercase tracking-wider">
                                        {{ $report->shift }}
                                    </span>
                                    
                                    <!-- Vendor Badge -->
                                    <span class="px-2 py-0.5 rounded bg-primary-50 text-primary-750 border border-primary-100 text-3xs font-bold uppercase tracking-wide">
                                        Vendor: {{ $report->vendor }}
                                    </span>

                                    <!-- Work Mode Badge -->
                                    <span class="px-2 py-0.5 rounded bg-slate-105 text-slate-600 border border-slate-200 text-3xs font-semibold uppercase tracking-wider">
                                        {{ $report->pengerjaan }}
                                    </span>
                                </div>

                                <!-- Delete Trigger -->
                                <form action="{{ route('wound-reports.destroy', $report->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus data laporan ini?')" class="opacity-0 group-hover:opacity-100 focus-within:opacity-100 transition-opacity absolute right-2.5 top-2.5 sm:relative sm:right-0 sm:top-0 shrink-0">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-1 rounded bg-slate-100 hover:bg-rose-50 text-slate-400 hover:text-rose-600 transition-all" title="Hapus Log">
                                        <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                    </button>
                                </form>
                            </div>

                            <!-- Core info -->
                            <div class="space-y-1">
                                <h4 class="text-xs font-bold text-slate-800 flex flex-wrap items-center gap-1">
                                    {{ $report->produk_yang_dikerjakan }}
                                    <span class="text-3xs text-slate-400 font-normal">({{ $report->jenis_produk }})</span>
                                </h4>
                                
                                <div class="text-3xs text-slate-600 flex items-center gap-1 py-0.5">
                                    <i data-lucide="user" class="w-3 h-3 text-slate-400"></i>
                                    <span>Operator: <strong>{{ $report->operator }}</strong></span>
                                </div>

                                <div class="flex items-center justify-between gap-4 text-3xs text-slate-500">
                                    <div class="flex items-center gap-1">
                                        <span class="font-bold text-slate-700">Hasil:</span>
                                        <span class="text-xs font-extrabold text-slate-800">{{ $report->hasil }}</span>
                                        <span class="font-medium text-slate-450">{{ $report->satuan }}</span>
                                    </div>
                                    <span class="font-mono text-slate-400 flex items-center gap-0.5">
                                        <i data-lucide="calendar" class="w-2.5 h-2.5"></i>
                                        {{ \Carbon\Carbon::parse($report->tanggal)->format('d/m/Y') }}
                                    </span>
                                </div>

                                @if($report->keterangan)
                                <div class="mt-2 text-3xs text-slate-500 bg-slate-50 border border-slate-100 rounded p-1.5 italic font-outfit">
                                    "{{ $report->keterangan }}"
                                </div>
                                @endif
                            </div>

                        </div>
                        @endforeach
                        
                        @endif

                    </div>

                </div>

            </div>

        </div>

    </main>

    <!-- App Footer -->
    <footer class="bg-slate-900 border-t border-slate-800 text-slate-400 py-6 mt-12 shrink-0">
        <div class="max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 text-center sm:flex sm:items-center sm:justify-between">
            <span class="text-2xs block sm:inline">&copy; 2026 Loka Medical Systems - Laporan Produksi.</span>
            <span class="text-3xs text-slate-500 block sm:inline mt-1 sm:mt-0 uppercase tracking-wider font-semibold font-outfit">Sistem Administrasi Profesional</span>
        </div>
    </footer>

    <script>
        // Init Lucide
        lucide.createIcons();

        const operatorLists = {
            'KWI': [
                "ADITYA ANANDA PUTRI PRATAMA",
                "AIDA NUR AVIDA",
                "AJI PAMUNGKAS",
                "AKHMAD ARIF PUJIONO",
                "ALIATUN NIKMAH",
                "ALIYAH NURUR ROKHIMAH",
                "ANAS TASYA PITRI A",
                "BAGUS SETIAWAN",
                "BUNGA VIKA KHARISMA",
                "CATRINE AMANDA BUDIARTI",
                "CHERIL VEBRI ANINDI",
                "DERIS SETIA DWI JULIAN",
                "DEVINA RAHMA",
                "DIANA LILIK JUWARNI",
                "DIFANI APRILIA BERLIANTI",
                "EGA NUR CAHYANA",
                "ELI SUSANTI",
                "ERIKO IFANDI",
                "FITA INDRIANI",
                "GITA AGUSTIN",
                "HANNA AYU PERTIWI",
                "HARUN AL RASYID",
                "HASAN MUKMIN ABDUNNASIR",
                "IMROATUL MUFIDAH",
                "INDRA SUNDARI",
                "IRDADIAN WAHYUNI",
                "KUSNUL AFIFA",
                "LINTANG DWIANING PUTRI",
                "LISTRIANA HENY",
                "LUMATUL ABIDAH",
                "LUTHFIYAH APTA INDRIATI",
                "M AGUNG SBASTIAN",
                "M FERI KURNIAWAN",
                "M NUR BAYU ANDRIANI PUTRA",
                "MAULANA RYAMZARD RYACUDU",
                "MIFTACHUN NIKMAH",
                "MILA INDAH CAHYANTI",
                "MOCHAMMAD RIZAL AINUR ROFIK",
                "MOH NUZULUL RAMADHANI",
                "MOHAMAD FARUL",
                "MUCHAMMAD FAISAL ZAINUR ROZI",
                "MUHAMMAD ADE MAULANA",
                "MUHAMMAD ALI FAIZIN",
                "MUHAMMAD IMAM BAIHAKI KWI",
                "MUSONNINUR NISAK MEI",
                "NANI FITRIA",
                "NAWAF ADAMAZIZ",
                "NUR FAUZAN ROHMATULLOH",
                "PUTRI DIANA",
                "RIFKA AGUSTINA IFFAHYANTI",
                "RISKI ADI SETIAWAN",
                "RISTA  DWI PURWANTI",
                "RITA ARIYANTI",
                "ROHMATIN MAULIDA",
                "SEPTIAN ANDANI FAUZI",
                "SHABRINA ZULFIA RIZKA",
                "SINTA NOFITASARI",
                "SITI NURMA LIKATUS A.",
                "SRI WAHYUNI KWI",
                "SULTON ARIF",
                "SUPIYAH",
                "TRI AYU WILUJENG",
                "UMI FARIDA PUSPITA NINGRUM",
                "ZAINAL DINOVA",
                "MOCHAMAD SAIFUL AMIN",
                "ANGGUN CITRA LESTARI",
                "ANIS MUNTHOHIROH",
                "FIRMANUDIN YUGA PUTRA",
                "BILAL RAHMAD PANDEWO",
                "SINTA WANDARI",
                "SIROJUL ANWAR",
                "M ISKANDAR ALI",
                "RICO MORENO ARDIANSYAH",
                "FIRDHA NUR CAHYANI",
                "RENY HANAFIAH",
                "MOHAMMAD KHOIRUL HUDA",
                "YUSUF SANDI BIMAWAN",
                "ROBIATUL ADAWIYAH",
                "RINI HARIATI",
                "ILHAM KHOIRUN ABDILLAH",
                "MUHAMMAD DWIKY WAHYUDI",
                "IMROATUS SHOLIHAH",
                "NUR LAILATUL ROHMA",
                "NOVIANTI PARLIANDINI",
                "ABILIA SISKA SAPUTRI",
                "FRISKA SELYNA ASTIAJI",
                "KHARISMA MAHARANI PUTRI",
                "INDAH AYU PRATIWI",
                "YULIANA HERAWATI",
                "FANE SAFIORENTA PUTRI",
                "AL'AINA'UL MARDHIYAH",
                "AISYAH MAR'ATUN SHOLICHAH",
                "ALYA FITRI RHAMADHANI",
                "ACHMAD BAIEAD ABDILLA"
            ],
            'MJA': [
                "AANG ABDULLOH SUKUR R",
                "ACHMAD EKO FIRMANDIANSYAH",
                "ADE NUR AINI",
                "ADEN IRGI HADIANTO",
                "AGUNG PRAYOGI",
                "AHMAD FAISAL",
                "AHMAD FAUZAN",
                "AKHMAD AKHIRU ZIKI ZAKARIA",
                "ALENA SABILLA LESTARI",
                "ALICIA PRENEPI Y",
                "AMELIA AZZAHRO",
                "ANTON HIDAYATUR ROKHIM",
                "APRILIA NUR CHAMIDAH",
                "ARIS DWI ARIANTO",
                "ARMAN ASHARI",
                "ATIK IRNAYATI",
                "AURA DIAS FAIZA",
                "CICAH MULYA FATMAWATI",
                "DAFFA AKTUR PRATAMA",
                "DAVID HADI PRASETYO",
                "DINI MEISYAROH",
                "DODIK IRFANUDIN",
                "EDO ISMAKA",
                "ELA ADELIA",
                "ELMA",
                "ELSA BUDI ARDHANIA",
                "ELY MULYANI",
                "FAISAL FAHRI",
                "FEBYMIA ARIFATUNASIKHA",
                "FENTI NOFITA SARI",
                "FIDIAN ARIS ANDIKA",
                "FIRDA ARIANTI",
                "HADI SUPRAYITNO",
                "HAMIDATUN NISA'",
                "HUSAIN JAUHARI ABDUL",
                "IDA PURWANTI",
                "IKA HANDAYANI",
                "ILMA RUFIANA",
                "ILMA SARI",
                "INAYATUL ULA",
                "INDAH ALISIA",
                "INDAH SULISTYORINI",
                "LULUK ROZAKOH",
                "LUSI NUR ANGGRANI",
                "M ALI BAIDHOWI",
                "M IKHSAN DAVID MAULANA",
                "M WILLIAM FARHANI",
                "MAY WIDIYA SAHARANI",
                "MOCHAMMAD SYAHRUL MUFARID",
                "MOHAMMAD ANDI PRAYOGO",
                "MOHAMMAD NAUVAL",
                "MOKHAMAD ILHAM ZAKARIYA",
                "MUHAMMAD ANDY CHOIRUDIN",
                "MUHAMMAD DANANG SAPUTRA",
                "MUHAMMAD EFENDI",
                "MUHAMMAD SETIO MAULANA",
                "MUHHAMAD FADIL ALFIANTO",
                "MURTIANINGSIH",
                "NADIA NURDIANA PUTRI",
                "NADIA TASNIM",
                "NATASHYA SILVANIA ANDRIANA PUTRI",
                "NOVI EKA KUMALASARI",
                "NUR MANFAUNAH",
                "NURUL HASANAH",
                "NURUL KHATIMAH",
                "PRATIWI WIDYA SUSANTI",
                "PUTRI NOR FADHILAH",
                "QORRI AINA FATIMAH",
                "RADITYA ANGGA ANSORI",
                "RESTU BHAKTI HARTANTO",
                "REVA AMELIA PUTRI",
                "RINA LUSIANA",
                "ROHMATUL K",
                "ROZAQ GHANY AWALLUDIN",
                "RYAN ARI SETYAWAN",
                "SALVIA LESTARI",
                "SHINTA AYU LESTARI",
                "SITI NUR CHOLIZA",
                "SRI WULANDARI",
                "TIARA VIRZINIA",
                "TRI ILDA APRILIA",
                "VERA WIDYA WATI",
                "VIRA MEIDINA",
                "WAHYUNI RESTUNINGSIH",
                "WAHYU ADHY PRAJA",
                "REVA AMELIA PUTRI",
                "NADIN APRILIA PUTRI",
                "ANGGI HARYANTI",
                "HANI FAIZAH",
                "AMILIA PUTRI DEWI LISTYONO",
                "MOCHTAR ARIFIN",
                "ESTIA RINENGSEH",
                "TIRTA GIO RAMADHAN",
                "MUHAMMAD ZAINUL ARIFIN",
                "REVA MAULIDA",
                "APRILIA WULANDARI",
                "AHMAD AJI PRATAMA",
                "DWI AJENG ANGGRAINA",
                "ANGGUN KHALIMATUS SA'DIYAH",
                "ZELLO RIZKY ABIATI",
                "AHMAD RAFI FIVEDI ARIF",
                "SHOFIYATUS SA'ADAH",
                "YULIA ERLINA DEWI",
                "NANDA FAUZIA NURMALASARI",
                "RARA PUTRI IZZATUL ILMI",
                "LESTARI AYU NENGTIAS",
                "HAFIZH SUGARINAWATI",
                "MUSFIATI",
                "SHAHIRA AZULAIKA",
                "SINDA NEFIKA ARIANI"
            ],
            'AA': [
                "AFISYA PARADISA",
                "ANDINDA SOFIATUR ROFICHO",
                "ANDRI ARDIANTO",
                "ARFIAN SAHRU R",
                "BUDI CAHYO TRI ATMOJO",
                "DEBI SETYAWAN",
                "DIENI NURUL KHAQQI EKA R",
                "FOURIZAL EKA FACHRUDIN",
                "HABIBATUL NUR INTAN",
                "HELMI RAFIF",
                "KASIH SEPTIA RAHMADANIA",
                "KHUSNUL KHOTIMAH BARU",
                "LYANA BUNGA PUTRI ARDITA",
                "M BAGUS MUHAIMIN",
                "MIA JULIANTI",
                "MOCH FELIX ALMAZ FAZAH",
                "MOCHAMMAD ADI PRAWIRO",
                "MOH. RUSDAN ROSYID",
                "MOHAMAD BIMA MA'ARIF",
                "MUHAMMAD ARBI SYAH NURIL",
                "MUHAMMNAD FARID NUR MAHFUDI",
                "NATALIA TRISTIN HAKIM",
                "NICKO BACHTIAR",
                "NUR ADINDA",
                "ONGKI WIDIANTO",
                "PASHA OKTAVIA",
                "RAGIL PRASETYO",
                "RETA PUTRI MEIDINA",
                "REZA ADITYA HILMY",
                "RISKA DWI SINTAWATI",
                "SALMAN AL FARISI",
                "SEPTIANSYAH ISWANTO",
                "SINTA AYU RISQI RAMADHANI",
                "WILDA JANUAR ASTIAJI",
                "YUSUF ARDIANSYAH",
                "SRI YANTI",
                "NURMALA HIDHAYANTI",
                "JUMAININGSIH",
                "KHOLIDAH KUSNIATIN",
                "MUHAMMAD YUSUF HANDI TIA",
                "DINA SABILA",
                "ELSATRI PUJIYATI",
                "LIA NOFITA SARI",
                "VIDA YULIATI",
                "KUSRINI",
                "ALFIN RIJKI",
                "ZHENI OKTA VIOLA",
                "MUHAMMAD AHWAN MUKAROM",
                "MUCHAMAD FAISAL NUL CKACKIM",
                "MUHAMMAD LEO BAGUS SATRIA",
                "M ARFIAN ARIF",
                "MIA ARI WIDYANINGRUM",
                "MOKHAMAD ABDULLAH BASOFI",
                "SRI WINDARTIK",
                "NAILATUS SA'ADAH",
                "AHMAD AINUR ROSYID",
                "ANGGUN SAFIRA",
                "ANIS NOFITASARI",
                "AMIROTUL MU'ALIMAH",
                "SRIS NOVITA EMILIAWATI",
                "YUDHA FATHUR RAHMA",
                "ANNISA SALSABILA",
                "IMA WILDATUS SHOLIKHAH",
                "NURUL ISTIQOMAH",
                "NADIRA ARIANTI",
                "NUR LAILI PERMATASARI",
                "GALANG YUWANA ARRAFI",
                "FERA HESTINA NATALIA",
                "DEWI EMALIA ANGGRAINI",
                "IMATUL JANNAH",
                "ARFINDA ZAHRA",
                "REZA NURAINI",
                "MILFA ARLIFI",
                "NUR HIDAYATUL AULIA",
                "CANTIKA ENDJELY",
                "SHOFY NAILATUL MUNAWAROH"
            ],
            'IPS': [
                "ANNISA MAHAROTI ALFITRI",
                "DEA NUR RAHMA S",
                "EKA RAHAYU PUTRI A T",
                "FANDHI TRI SETYA",
                "FRINA FEBRIANI",
                "MILA NUR HIDAYAH",
                "NUR ROHMATIN",
                "RIDHO RISMAYA",
                "ROBIM JOSWANDA",
                "SELFIE RAMADHANI",
                "UYUN MASRUROH",
                "FILDZAHANAADITYA YONARA",
                "LULUK MUSFITA SARI",
                "KIKI AGUSTIN INTAVIS",
                "SASTA FADILA WARDANI",
                "FARADYA AMINATUZ  Z.",
                "ASMAUL HUSNAh",
                "MUHAMMAD DAFA WARDANA",
                "ETI SETYOWATI",
                "AYU SYAFRIDA",
                "GITARIA WIJIANTI",
                "MIFTAHUR ROHMAT AMIRUDDIN ZAKARIA",
                "NOVIA AGUSTINA",
                "MAUDIY PUTRI APRILLIA",
                "DARREL HYANGI MAHARANI",
                "LUTFI WULAN SARI",
                "SITI NUR ROHMAH",
                "DITA DWI ARIMBI",
                "NANANG WIJAYA",
                "HAFIS INDRA SAYOGA",
                "BAGUS DWI CAHYONO",
                "FERDIANSYAH PRAMUDYA",
                "MUHAMMAD ALFIRDAUS",
                "NANDHA AUDIA SLAMET",
                "MUHAMMAD LAZUARDI PRASETYO",
                "FIONANTA RAHMA ULA YUSTIAR",
                "AZZOYA PUTRI ASYARI",
                "FERNANDA APRILIA",
                "RIFQI ARIYANTO",
                "ARIS SULIANTO",
                "LAILATUL DWI ANISAH",
                "LAYLI NUR FITRIANA",
                "DEBY AMELIA",
                "RISDAYANTI ELVITA RANI",
                "NANIK FARIDA",
                "ERIKA",
                "CHURIL NUZULIATIL AFIFAH",
                "EKA RIWAYANTI NINGSIH",
                "SHEPTIA AYU FIRDAYANTI",
                "MARCELLA MARTA ABHINAYA",
                "ZILLA NUR ANGGRAINI",
                "FENY DWI ARIYANTI",
                "PUTRI WIDIANA",
                "SINTIA DARMA PUTRI",
                "SRI INDARWATI",
                "SYINBRAN FAJAR FATMA SARI",
                "FRISCA FEBY ANABELA",
                "AGUSTRIYA LESTARI",
                "SYARIFAH CHAMAMI",
                "IMAS PAMBAYU"
            ],
            'JMI': [
                "ACKEMAD HARIANTO",
                "LIANA SISWANTI",
                "MUCHAMAD NUR AFANDI",
                "PANJI RAHMAT DARMAWAN"
            ],
            '-': [
                "-",
                "Opsi 2"
            ]
        };

        // Combine all unique operators for the 'Other' vendor option
        const allOperatorsList = [];
        for (const key in operatorLists) {
            if (key !== '-') {
                allOperatorsList.push(...operatorLists[key]);
            }
        }
        operatorLists['Other'] = [...new Set(allOperatorsList)].sort();

        // Update the select options dynamically based on the checked vendor
        function updateOperatorOptions() {
            const activeVendorRadio = document.querySelector('input[name="vendor"]:checked');
            const vendorValue = activeVendorRadio ? activeVendorRadio.value : 'KWI';
            
            // Map selected vendor to the correct data list (or fallback to 'Other' if a custom vendor is typed)
            const vendorKey = operatorLists.hasOwnProperty(vendorValue) ? vendorValue : 'Other';
            const operators = operatorLists[vendorKey] || [];
            
            const selectEl = document.getElementById('operator_select');
            if (!selectEl) return;
            
            const currentValue = selectEl.value;
            
            // Clear existing options, keep placeholder
            selectEl.innerHTML = '<option value="" disabled selected>Choose</option>';
            
            // Populate new operator options
            operators.forEach(name => {
                const option = document.createElement('option');
                option.value = name;
                option.textContent = name;
                selectEl.appendChild(option);
            });
            
            // Restore selection if operator exists in the new vendor's list
            if (currentValue && operators.includes(currentValue)) {
                selectEl.value = currentValue;
            }
        }

        // Highlight selected radio cards with dynamic styling
        function updateRadioStyles() {
            const cards = document.querySelectorAll('.radio-card');
            cards.forEach(card => {
                const input = card.querySelector('input');
                const borderDiv = card.querySelector('.absolute');
                if (input.checked) {
                    card.classList.remove('border-slate-300');
                    card.classList.add('border-primary-500');
                } else {
                    card.classList.remove('border-primary-500');
                    card.classList.add('border-slate-300');
                }
            });
        }

        // Toggle custom text field for "Other" vendor
        function toggleVendorOther() {
            const otherRadio = document.querySelector('input[name="vendor"][value="Other"]');
            const otherContainer = document.getElementById('vendorOtherContainer');
            const otherInput = document.getElementById('vendor_other_text');
            
            if (otherRadio && otherRadio.checked) {
                otherContainer.classList.remove('hidden');
                otherInput.required = true;
            } else {
                otherContainer.classList.add('hidden');
                otherInput.required = false;
                otherInput.value = "";
            }
        }
        
        // Initial setup for radio stylings, vendor states, and dynamic operator options
        updateRadioStyles();
        toggleVendorOther();
        updateOperatorOptions();

        // Intercept form submit to override "Other" radio value with custom text
        const form = document.getElementById("productionForm");
        form.addEventListener("submit", function(e) {
            const activeVendor = form.querySelector('input[name="vendor"]:checked');
            if (activeVendor && activeVendor.value === 'Other') {
                const customVendorText = document.getElementById('vendor_other_text').value.trim();
                if (customVendorText) {
                    activeVendor.value = customVendorText;
                } else {
                    alert('Silakan tuliskan nama vendor kustom terlebih dahulu.');
                    e.preventDefault();
                }
            }
        });

        // Clear Form fields
        function clearForm() {
            if (confirm("Apakah Anda yakin ingin menyetel ulang seluruh isian formulir?")) {
                // Clear text, number & textareas
                form.querySelectorAll('input[type="text"], input[type="number"], textarea').forEach(input => {
                    input.value = "";
                });
                
                // Reset select dropdowns
                form.querySelectorAll('select').forEach(select => {
                    select.selectedIndex = 0;
                });
                
                // Reset shift radios (default first checked)
                const shiftRadios = form.querySelectorAll('input[name="shift"]');
                shiftRadios.forEach((radio, index) => {
                    radio.checked = (index === 0);
                });

                // Reset vendor radios (default first checked)
                const vendorRadios = form.querySelectorAll('input[name="vendor"]');
                vendorRadios.forEach((radio, index) => {
                    radio.checked = (index === 0);
                });
                
                // Reset date to today
                const dateInput = form.querySelector('input[type="date"]');
                if (dateInput) {
                    const today = new Date().toISOString().split('T')[0];
                    dateInput.value = today;
                }
                
                updateRadioStyles();
                toggleVendorOther();
                updateOperatorOptions();
            }
        }
    </script>
</body>
</html>
