<!DOCTYPE html>
<html lang="id" class="h-full bg-slate-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Dasbor Produksi - Woundcare</title>
    
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
    
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }
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
        .radio-card {
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        }
    </style>
</head>
<body class="min-h-full flex flex-col text-slate-800">

    <!-- Header Navbar -->
    <header class="bg-office-900 text-white shadow-lg sticky top-0 z-30 shrink-0">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <!-- Branding -->
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-lg bg-gradient-to-tr from-primary-500 to-indigo-500 flex items-center justify-center text-white shadow-md">
                        <i data-lucide="activity" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <span class="text-sm font-semibold tracking-wider text-slate-400 block uppercase leading-none font-outfit">Woundcare</span>
                        <h1 class="text-base font-extrabold font-outfit tracking-tight text-white leading-tight">Woundcare Dashboard</h1>
                    </div>
                </div>
                
                <!-- Logged In Operator & Logout -->
                <div class="flex items-center gap-4">
                    @if(session('operator_role') === 'karyawan')
                    <div id="countdown-header" class="flex items-center gap-1.5 bg-slate-800/80 px-2.5 py-1.5 rounded-xl border border-slate-700/50 text-[10px] sm:text-[11px] font-semibold shrink-0">
                        <i id="countdown-icon" data-lucide="timer" class="w-3.5 h-3.5 text-amber-400 animate-pulse"></i>
                        <span id="countdown-text" class="text-slate-350">Memuat...</span>
                    </div>
                    @endif

                    <div class="hidden sm:flex flex-col text-right">
                        <span class="text-xs font-bold text-white">{{ session('operator_name') }}</span>
                        <span class="text-2xs text-slate-450 uppercase tracking-widest font-semibold">
                            {{ session('operator_vendor') }} &bull; {{ session('operator_role') == 'coordinator' ? 'Koordinator' : 'Karyawan' }}
                        </span>
                    </div>
                    <form action="{{ route('logout') }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-rose-600/90 hover:bg-rose-600 text-white text-xs font-bold transition-all shadow-md active:scale-95">
                            <i data-lucide="log-out" class="w-3.5 h-3.5"></i>
                            <span>Keluar</span>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Container -->
    <main class="flex-1 max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-6">
        
        <!-- Notifications / Toasts -->
        @if(session('success'))
        <div id="successToast" class="flex items-center justify-between p-4 mb-6 rounded-xl bg-emerald-50 border border-emerald-250 text-emerald-800 shadow-md">
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
        <div id="errorToast" class="p-4 mb-6 rounded-xl bg-rose-50 border border-rose-250 text-rose-800 shadow-md">
            <div class="flex items-center justify-between mb-2 border-b border-rose-200/50 pb-2">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-rose-500/20 text-rose-600 flex items-center justify-center shrink-0">
                        <i data-lucide="alert-circle" class="w-5 h-5"></i>
                    </div>
                    <p class="text-sm font-bold">Terjadi Kendala</p>
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

        <!-- DUAL-ROLE CONTENT SWITCH -->
        @if(session('operator_role') === 'coordinator')
            
            <!-- ========================================== -->
            <!-- COORDINATOR / ADMIN DASHBOARD VIEW         -->
            <!-- ========================================== -->
            
            <!-- Stats Row -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                <!-- Widget 1: Total Volume -->
                <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm relative overflow-hidden">
                    <span class="text-3xs font-extrabold uppercase text-slate-400 tracking-wider block">Total Volume Hari Ini</span>
                    <h3 class="text-2xl font-bold font-outfit text-slate-800 mt-1">
                        {{ number_format($reports->sum('hasil')) }}
                        <span class="text-2xs font-medium text-slate-400">PCS / unit</span>
                    </h3>
                    <span class="text-3xs text-slate-450 block mt-1">Dari total data yang terfilter</span>
                </div>

                <!-- Widget 2: Pending Approval -->
                <div class="bg-amber-50/50 p-4 rounded-xl border border-amber-200 shadow-sm relative overflow-hidden">
                    <span class="text-3xs font-extrabold uppercase text-amber-600 tracking-wider block">Menunggu Persetujuan</span>
                    <h3 class="text-2xl font-bold font-outfit text-amber-800 mt-1">
                        {{ $reports->where('status', 'pending')->count() }}
                        <span class="text-2xs font-medium text-amber-500">laporan</span>
                    </h3>
                    <span class="text-3xs text-amber-600 block mt-1">Harus diverifikasi oleh Mba Ella / Hana</span>
                </div>

                <!-- Widget 3: Approved -->
                <div class="bg-emerald-50/40 p-4 rounded-xl border border-emerald-200 shadow-sm relative overflow-hidden">
                    <span class="text-3xs font-extrabold uppercase text-emerald-600 tracking-wider block">Telah Disetujui</span>
                    <h3 class="text-2xl font-bold font-outfit text-emerald-800 mt-1">
                        {{ $reports->where('status', 'approved')->count() }}
                        <span class="text-2xs font-medium text-emerald-500">laporan</span>
                    </h3>
                    <span class="text-3xs text-emerald-600 block mt-1">Laporan terverifikasi & masuk rekapan</span>
                </div>

                <!-- Widget 4: Rejected -->
                <div class="bg-rose-50/45 p-4 rounded-xl border border-rose-200 shadow-sm relative overflow-hidden">
                    <span class="text-3xs font-extrabold uppercase text-rose-600 tracking-wider block">Ditolak / Perlu Revisi</span>
                    <h3 class="text-2xl font-bold font-outfit text-rose-800 mt-1">
                        {{ $reports->where('status', 'rejected')->count() }}
                        <span class="text-2xs font-medium text-rose-500">laporan</span>
                    </h3>
                    <span class="text-3xs text-rose-600 block mt-1">Laporan dikembalikan ke Operator</span>
                </div>
            </div>

            <!-- Filters Dashboard -->
            <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-4 mb-6">
                <form action="{{ route('dashboard') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-4 gap-4 items-end">
                    <div>
                        <label class="text-xs font-bold text-slate-500 block mb-1">Status Laporan</label>
                        <select name="status" class="w-full rounded-lg border border-slate-350 bg-white py-2 px-2.5 text-xs text-slate-800 focus:outline-none focus:ring-1 focus:ring-primary-500">
                            <option value="">Semua Status</option>
                            <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending (Menunggu)</option>
                            <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approved (Disetujui)</option>
                            <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Rejected (Ditolak)</option>
                        </select>
                    </div>

                    <div>
                        <label class="text-xs font-bold text-slate-500 block mb-1">Nama Operator</label>
                        <select name="operator_filter" class="w-full rounded-lg border border-slate-350 bg-white py-2 px-2.5 text-xs text-slate-800 focus:outline-none focus:ring-1 focus:ring-primary-500">
                            <option value="">Semua Operator</option>
                            @foreach($allOperators as $op)
                                <option value="{{ $op->id }}" {{ request('operator_filter') == $op->id ? 'selected' : '' }}>{{ $op->name }} ({{ $op->vendor }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="text-xs font-bold text-slate-500 block mb-1">Pencarian Kata Kunci</label>
                        <div class="relative">
                            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari produk, keterangan..." class="w-full rounded-lg border border-slate-350 bg-white py-2 pl-7 pr-2.5 text-xs text-slate-800 focus:outline-none focus:ring-1 focus:ring-primary-500">
                            <i data-lucide="search" class="absolute left-2.5 top-2.5 w-3.5 h-3.5 text-slate-400"></i>
                        </div>
                    </div>

                    <div class="flex gap-2">
                        <button type="submit" class="w-full py-2 px-4 rounded-lg bg-primary-600 hover:bg-primary-700 text-white font-bold text-xs shadow-md transition-all">
                            Filter Data
                        </button>
                        <a href="{{ route('dashboard') }}" class="py-2 px-3 rounded-lg border border-slate-300 hover:bg-slate-50 text-slate-500 font-bold text-xs flex items-center justify-center">
                            Reset
                        </a>
                    </div>
                </form>
            </div>

            <!-- Verification Queue List -->
            <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="bg-office-750 text-white p-4 flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <i data-lucide="clipboard-list" class="w-5 h-5 text-primary-400"></i>
                        <h2 class="font-bold font-outfit text-white">Panel Verifikasi Laporan Kerja Karyawan</h2>
                    </div>
                    <span class="text-2xs font-medium text-slate-300">Menampilkan {{ $reports->count() }} data</span>
                </div>

                <div class="divide-y divide-slate-100">
                    @forelse($reports as $report)
                    <div class="p-4 sm:p-5 hover:bg-slate-50/50 transition-all flex flex-col md:flex-row md:items-center justify-between gap-4">
                        
                        <!-- Report Info -->
                        <div class="space-y-2 flex-1">
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="px-2 py-0.5 rounded text-3xs font-extrabold uppercase border {{ $report->shift == 'Shift 1' ? 'bg-sky-50 text-sky-700 border-sky-200' : ($report->shift == 'Shift 2' ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-purple-50 text-purple-700 border-purple-200') }}">
                                    {{ $report->shift }}
                                </span>
                                <span class="px-2 py-0.5 rounded bg-primary-50 text-primary-750 border border-primary-100 text-3xs font-extrabold uppercase">
                                    {{ $report->vendor }}
                                </span>
                                
                                <!-- Status Badge -->
                                @if($report->status == 'approved')
                                    <span class="px-2 py-0.5 rounded bg-emerald-500/10 text-emerald-600 text-3xs font-bold border border-emerald-500/20 flex items-center gap-1">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Approved
                                    </span>
                                @elseif($report->status == 'rejected')
                                    <span class="px-2 py-0.5 rounded bg-rose-500/10 text-rose-600 text-3xs font-bold border border-rose-500/20 flex items-center gap-1">
                                        <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span> Rejected
                                    </span>
                                @else
                                    <span class="px-2 py-0.5 rounded bg-amber-500/10 text-amber-600 text-3xs font-bold border border-amber-500/20 flex items-center gap-1">
                                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span> Pending
                                    </span>
                                @endif
                            </div>

                            <h3 class="text-sm font-bold text-slate-800">
                                {{ $report->produk_yang_dikerjakan }} 
                                <span class="text-xs text-slate-400 font-normal">({{ $report->jenis_produk }} - {{ $report->pengerjaan }})</span>
                            </h3>

                            <div class="grid grid-cols-2 sm:grid-cols-4 gap-x-4 gap-y-1 text-xs text-slate-500">
                                <div>Operator: <strong class="text-slate-700">{{ $report->operator }}</strong></div>
                                <div>Tanggal Kerja: <strong class="text-slate-700">{{ \Carbon\Carbon::parse($report->tanggal)->format('d-m-Y') }}</strong></div>
                                <div>Volume Hasil: <strong class="text-slate-800 text-sm font-extrabold">{{ number_format($report->hasil) }}</strong> {{ $report->satuan }}</div>
                                <div>Waktu Kirim: <strong class="text-slate-700">{{ $report->created_at->format('H:i') }} Wib</strong></div>
                            </div>

                            @if($report->keterangan)
                                <p class="text-xs text-slate-500 italic bg-slate-50 border border-slate-200/50 rounded-lg p-2 max-w-2xl font-outfit">
                                    "{{ $report->keterangan }}"
                                </p>
                            @endif

                            @if($report->status == 'rejected' && $report->catatan_revisi)
                                <div class="text-xs text-rose-700 bg-rose-50 border border-rose-100 rounded-lg p-2.5 flex items-start gap-2">
                                    <i data-lucide="message-square" class="w-4 h-4 mt-0.5 text-rose-500 shrink-0"></i>
                                    <div>
                                        <span class="font-bold">Alasan Penolakan:</span> {{ $report->catatan_revisi }}
                                    </div>
                                </div>
                            @endif
                        </div>

                        <!-- Actions (Approve/Reject) -->
                        <div class="flex flex-wrap md:flex-col items-stretch justify-end gap-2 shrink-0 w-full md:w-44">
                            @if($report->status === 'pending')
                                <form action="{{ route('wound-reports.approve', $report->id) }}" method="POST" class="w-full">
                                    @csrf
                                    <button type="submit" class="w-full flex items-center justify-center gap-1.5 py-2 px-3 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold shadow-md transition-all">
                                        <i data-lucide="check" class="w-4 h-4"></i>
                                        <span>Setujui Laporan</span>
                                    </button>
                                </form>

                                <button onclick="openRejectDialog({{ $report->id }}, '{{ $report->operator }}', '{{ $report->produk_yang_dikerjakan }}')" class="w-full flex items-center justify-center gap-1.5 py-2 px-3 rounded-lg bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold shadow-md transition-all">
                                    <i data-lucide="x" class="w-4 h-4"></i>
                                    <span>Tolak / Minta Revisi</span>
                                </button>
                            @endif

                            <!-- Delete Option -->
                            <form action="{{ route('wound-reports.destroy', $report->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus data laporan ini secara permanen dari database?')" class="w-full">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="w-full flex items-center justify-center gap-1.5 py-1.5 px-3 rounded-lg border border-slate-300 hover:bg-rose-50 hover:text-rose-600 text-slate-500 text-xs font-semibold transition-all">
                                    <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                    <span>Hapus Permanen</span>
                                </button>
                            </form>
                        </div>

                    </div>
                    @empty
                    <div class="text-center py-16 space-y-3 text-slate-400">
                        <div class="w-12 h-12 rounded-full bg-slate-100 flex items-center justify-center mx-auto text-slate-400 border border-slate-200">
                            <i data-lucide="folder-open" class="w-6 h-6"></i>
                        </div>
                        <div>
                            <h3 class="text-sm font-bold text-slate-700">Tidak ada laporan ditemukan</h3>
                            <p class="text-xs text-slate-500 mt-1">Coba sesuaikan filter pencarian atau tunggu laporan masuk dari operator.</p>
                        </div>
                    </div>
                    @endforelse
                </div>
            </div>

            <!-- Inline Reject Dialog Modal overlay (Hidden initially) -->
            <div id="rejectModal" class="fixed inset-0 z-50 bg-slate-900/60 flex items-center justify-center p-4 hidden">
                <div class="bg-white rounded-xl shadow-2xl border border-slate-200 max-w-md w-full overflow-hidden animate-scale-up">
                    <div class="bg-rose-900 text-white p-4 flex items-center gap-2">
                        <i data-lucide="alert-triangle" class="w-5 h-5 text-rose-450"></i>
                        <h3 class="font-bold font-outfit">Tolak Laporan & Minta Revisi</h3>
                    </div>
                    <form id="rejectForm" method="POST" class="p-5 space-y-4">
                        @csrf
                        <div class="text-xs text-slate-600">
                            <p>Anda menolak laporan dari: <strong id="rejectOperatorName" class="text-slate-800"></strong></p>
                            <p class="mt-0.5">Produk: <span id="rejectProductName" class="italic"></span></p>
                        </div>
                        
                        <div class="space-y-1.5">
                            <label for="catatan_revisi" class="text-xs font-bold text-slate-700 block">Tuliskan Catatan Perbaikan/Kesalahan <span class="text-rose-500">*</span></label>
                            <textarea id="catatan_revisi" name="catatan_revisi" required rows="3" placeholder="Contoh: Salah ketik hasil, seharusnya 35 PCS bukan 3500. Silakan input ulang." class="w-full rounded-lg border border-slate-350 p-2.5 text-xs text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-rose-500/20 focus:border-rose-500 transition-all resize-none"></textarea>
                        </div>

                        <div class="flex justify-end gap-2.5 pt-2">
                            <button type="button" onclick="closeRejectDialog()" class="px-4 py-2 rounded-lg border border-slate-300 text-slate-500 font-semibold text-xs hover:bg-slate-50 transition-all">
                                Batal
                            </button>
                            <button type="submit" class="px-4 py-2 rounded-lg bg-rose-600 hover:bg-rose-700 text-white font-bold text-xs shadow-md transition-all">
                                Tolak & Kirim Koreksi
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <script>
                function openRejectDialog(reportId, operatorName, productName) {
                    const modal = document.getElementById('rejectModal');
                    const form = document.getElementById('rejectForm');
                    
                    form.action = `/wound-reports/${reportId}/reject`;
                    document.getElementById('rejectOperatorName').textContent = operatorName;
                    document.getElementById('rejectProductName').textContent = productName;
                    
                    modal.classList.remove('hidden');
                }

                function closeRejectDialog() {
                    document.getElementById('rejectModal').classList.add('hidden');
                    document.getElementById('catatan_revisi').value = '';
                }
            </script>

        @else
            
            <!-- ========================================== -->
            <!-- STAFF / OPERATOR DASHBOARD VIEW            -->
            <!-- ========================================== -->
            
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
                
                <!-- Left Side: Interactive Input Form -->
                <div class="lg:col-span-7 bg-white rounded-xl shadow-sm border border-slate-200/80 overflow-hidden">
                    
                    <!-- Edit Mode Banner -->
                    <div id="editBanner" class="bg-blue-600 text-white px-5 py-3 flex items-center justify-between border-b border-blue-700 hidden">
                        <div class="flex items-center gap-2">
                            <i data-lucide="info" class="w-4 h-4"></i>
                            <span class="text-xs font-bold">Mode Revisi: Memperbaiki laporan <span id="editReportName" class="underline"></span></span>
                        </div>
                        <button type="button" onclick="cancelEdit()" class="text-xs font-bold hover:underline bg-blue-700 px-2 py-0.5 rounded">
                            Batal Edit
                        </button>
                    </div>

                    <!-- Card Header -->
                    <div class="bg-office-750 text-white p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-200">
                        <div class="flex items-center gap-2.5 min-w-0">
                            <div class="w-8 h-8 rounded-lg bg-primary-500/10 text-primary-400 flex items-center justify-center shrink-0">
                                <i data-lucide="edit-3" class="w-4.5 h-4.5"></i>
                            </div>
                            <div class="min-w-0">
                                <h2 class="text-base font-bold font-outfit text-white">Form Laporan Hasil Produksi</h2>
                                <p class="text-2xs text-slate-400">Pengisian data diverifikasi atas nama: <strong class="text-white">{{ session('operator_name') }} ({{ session('operator_vendor') }})</strong></p>
                            </div>
                        </div>
                        <span class="self-start sm:self-auto text-3xs bg-rose-500/20 text-rose-450 border border-rose-500/35 font-bold px-2 py-0.5 rounded uppercase tracking-wider shrink-0 whitespace-nowrap">
                            Wajib *
                        </span>
                    </div>

                    <!-- Form Body -->
                    <form id="productionForm" action="{{ route('wound-reports.store') }}" method="POST" class="p-5 sm:p-6 space-y-5">
                        @csrf
                        
                        <!-- Hidden ID for edit mode -->
                        <input type="hidden" name="report_id" id="report_id" value="">

                        <!-- SECTION 1: Waktu & Shift -->
                        <div class="bg-slate-50 rounded-xl p-4 border border-slate-200/60 space-y-4">
                            <div class="flex items-center gap-2 border-b border-slate-200/80 pb-2 mb-1">
                                <i data-lucide="clock" class="w-4 h-4 text-primary-600"></i>
                                <h3 class="text-xs font-bold uppercase tracking-wider text-slate-500 font-outfit">Shift & Waktu Pengerjaan</h3>
                            </div>

                            <!-- Countdown & Cooldown Info Banner -->
                            @if(session('operator_role') === 'karyawan')
                            <div class="bg-white border border-slate-200 rounded-lg p-3.5 space-y-3 shadow-sm">
                                <div class="flex items-center gap-3 cursor-pointer select-none" onclick="toggleScheduleCollapse()">
                                    <div class="w-8 h-8 rounded-lg bg-amber-100 text-amber-600 flex items-center justify-center shrink-0" id="countdown-form-icon-bg">
                                        <i id="countdown-form-icon" data-lucide="timer" class="w-4 h-4 text-amber-600 animate-pulse"></i>
                                    </div>
                                    <div class="text-left flex-1 min-w-0">
                                        <span class="text-[10px] uppercase tracking-wider text-slate-400 font-bold block">Status Batas Pelaporan</span>
                                        <span id="countdown-form-text" class="text-xs font-semibold text-slate-700 block mt-0.5">Memuat sisa waktu...</span>
                                    </div>
                                    <!-- Toggle Collapse Chevron -->
                                    <div class="text-slate-400 hover:text-slate-600 transition-colors p-1">
                                        <i id="schedule-chevron" data-lucide="chevron-down" class="w-4 h-4 transition-transform duration-200"></i>
                                    </div>
                                </div>
                                <div id="schedule-details" class="hidden border-t border-slate-200/60 pt-2.5">
                                    <span class="text-[10px] uppercase font-bold text-slate-400 block mb-1.5">Jadwal Pengisian Laporan:</span>
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 text-[11px]">
                                        @foreach($shiftWindows as $sName => $sInfo)
                                        <div class="flex items-center justify-between bg-slate-50 px-2.5 py-1.5 rounded border border-slate-200/60">
                                            <span class="font-medium text-slate-700">{{ $sName }}</span>
                                            <span class="text-primary-600 font-bold">Batas: {{ $sInfo['window'] }}</span>
                                        </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                            @endif

                            <!-- Date input wrapper -->
                            <div class="space-y-1.5">
                                <label class="text-xs font-bold text-slate-650 flex items-center gap-1.5">
                                    Tanggal Kerja <span class="text-rose-500">*</span>
                                </label>
                                <div class="relative">
                                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-450">
                                        <i data-lucide="calendar" class="w-4 h-4"></i>
                                    </span>
                                    <input type="date" name="tanggal" id="tanggal" required class="pl-10 w-full rounded-lg border border-slate-300 py-2.5 px-3.5 text-sm bg-white text-slate-800 focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all cursor-pointer" value="{{ date('Y-m-d') }}">
                                </div>
                            </div>

                            <!-- Shift Radio Cards -->
                            <div class="space-y-2">
                                <label class="text-xs font-bold text-slate-650 block">Pilih Shift Kerja <span class="text-rose-500">*</span></label>
                                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-2">
                                    
                                    @foreach($shiftWindows as $sName => $sInfo)
                                    <label class="relative flex items-center gap-2.5 py-2 px-2.5 rounded-lg border border-slate-300 bg-white cursor-pointer radio-card hover:border-slate-400 select-none">
                                        <input type="radio" name="shift" value="{{ $sName }}" {{ $loop->first ? 'checked' : '' }} class="sr-only peer" onchange="updateRadioStyles()">
                                        <div class="w-4 h-4 rounded-full border-2 border-slate-400 flex items-center justify-center shrink-0 peer-checked:border-primary-600 peer-checked:bg-primary-600 transition-colors">
                                            <div class="w-1.5 h-1.5 rounded-full bg-white scale-0 peer-checked:scale-100 transition-transform"></div>
                                        </div>
                                        <div class="text-left leading-none flex-1 flex items-center justify-between gap-1">
                                            <div class="space-y-0.5">
                                                <span class="text-xs font-bold text-slate-800 block">{{ $sName }}</span>
                                                <span class="text-[10px] text-slate-400 block">Jam: {{ $sInfo['work'] }}</span>
                                            </div>
                                            <!-- Tooltip Info Icon -->
                                            <div class="relative inline-block group shrink-0">
                                                <span class="text-slate-400 hover:text-primary-600 transition-colors cursor-help p-0.5">
                                                    <i data-lucide="info" class="w-3.5 h-3.5"></i>
                                                </span>
                                                <!-- Tooltip text -->
                                                <div class="absolute bottom-full right-0 mb-1.5 hidden group-hover:block bg-slate-900 text-white text-[10px] py-1 px-2 rounded shadow-lg whitespace-nowrap z-20 font-normal">
                                                    Batas Input: {{ $sInfo['window'] }}
                                                    <div class="absolute top-full right-2 -mt-1 border-4 border-transparent border-t-slate-900"></div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="absolute inset-0 border border-transparent rounded-lg pointer-events-none peer-checked:border-primary-500 peer-checked:bg-primary-50/20 transition-all"></div>
                                    </label>
                                    @endforeach

                                </div>
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
                                    <select name="pengerjaan" id="pengerjaan" required class="w-full rounded-lg border border-slate-300 py-2.5 px-3 text-sm bg-white text-slate-800 focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all cursor-pointer">
                                        <option value="" disabled selected>Choose</option>
                                        @foreach($pengerjaans as $item)
                                            <option value="{{ $item->name }}">{{ $item->name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="space-y-1.5">
                                    <label class="text-xs font-bold text-slate-650 block">Jenis Produk <span class="text-rose-500">*</span></label>
                                    <select name="jenis_produk" id="jenis_produk" required class="w-full rounded-lg border border-slate-300 py-2.5 px-3 text-sm bg-white text-slate-800 focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all cursor-pointer">
                                        <option value="" disabled selected>Choose</option>
                                        @foreach($jenisProduks as $item)
                                            <option value="{{ $item->name }}">{{ $item->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <!-- Produk Yang Dikerjakan -->
                            <div class="space-y-1.5">
                                <label class="text-xs font-bold text-slate-650 block">Nama Produk yang Dikerjakan <span class="text-rose-500">*</span></label>
                                <div class="relative">
                                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-450">
                                        <i data-lucide="tag" class="w-4 h-4"></i>
                                    </span>
                                    <input type="text" name="produk_yang_dikerjakan" id="produk_yang_dikerjakan" required placeholder="Contoh: PLESTERIN ROLL 10x5 cm" class="pl-10 w-full rounded-lg border border-slate-300 py-2.5 px-3.5 text-sm bg-white text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all">
                                </div>
                            </div>
                        </div>

                        <!-- SECTION 3: Output & Keterangan -->
                        <div class="bg-slate-50 rounded-xl p-4 border border-slate-200/60 space-y-4">
                            <div class="flex items-center gap-2 border-b border-slate-200/80 pb-2 mb-1">
                                <i data-lucide="activity" class="w-4 h-4 text-primary-600"></i>
                                <h3 class="text-xs font-bold uppercase tracking-wider text-slate-500 font-outfit">Volume Hasil Kerja</h3>
                            </div>

                            <!-- Grid row: Hasil & Satuan -->
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div class="space-y-1.5">
                                    <label class="text-xs font-bold text-slate-650 block">Hasil <span class="text-rose-500">*</span></label>
                                    <div class="relative">
                                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-450">
                                            <i data-lucide="hash" class="w-4 h-4"></i>
                                        </span>
                                        <input type="number" name="hasil" id="hasil" required placeholder="0" min="0" class="pl-10 w-full rounded-lg border border-slate-300 py-2.5 px-3.5 text-sm bg-white text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all">
                                    </div>
                                </div>

                                <div class="space-y-1.5">
                                    <label class="text-xs font-bold text-slate-650 block">Satuan <span class="text-rose-500">*</span></label>
                                    <select name="satuan" id="satuan" required class="w-full rounded-lg border border-slate-300 py-2.5 px-3 text-sm bg-white text-slate-800 focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all cursor-pointer">
                                        <option value="" disabled selected>Choose</option>
                                        @foreach($satuans as $item)
                                            <option value="{{ $item->name }}">{{ $item->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <!-- Keterangan -->
                            <div class="space-y-1.5">
                                <label class="text-xs font-bold text-slate-650 block">Keterangan Tambahan / Kendala Kerja <span class="text-rose-500">*</span></label>
                                <div class="relative">
                                    <span class="absolute top-3 left-3 pointer-events-none text-slate-450">
                                        <i data-lucide="file-text" class="w-4 h-4"></i>
                                    </span>
                                    <textarea name="keterangan" id="keterangan" required rows="2" placeholder="Tuliskan catatan kerja atau kendala (jika tidak ada kendala, tulis 'Lancar' atau '-')" class="pl-10 w-full rounded-lg border border-slate-300 py-2.5 px-3.5 text-sm bg-white text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all resize-none"></textarea>
                                </div>
                            </div>
                        </div>

                        <!-- Action buttons wrapper -->
                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 border-t border-slate-100 pt-5">
                            <button type="button" onclick="clearForm()" class="order-2 sm:order-1 w-full sm:w-auto px-5 py-2.5 rounded-lg border border-slate-300 text-slate-500 font-semibold text-xs hover:bg-slate-50 hover:text-slate-700 transition-all active:scale-[0.98]">
                                Reset Formulir
                            </button>
                            <button type="submit" class="order-1 sm:order-2 w-full sm:w-auto px-6 py-2.5 rounded-lg bg-office-900 hover:bg-slate-800 text-white font-semibold text-xs shadow-md transition-all active:scale-[0.98] flex items-center justify-center gap-2">
                                <i data-lucide="send" class="w-4 h-4"></i> Ajukan Laporan
                            </button>
                        </div>

                    </form>
                </div>

                <!-- Right Side: Personal History List -->
                <div class="lg:col-span-5 bg-white rounded-xl shadow-sm border border-slate-200/80 overflow-hidden">
                    <div class="p-4 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
                        <div class="flex items-center gap-2">
                            <i data-lucide="history" class="w-4 h-4 text-slate-400"></i>
                            <h3 class="text-xs font-bold uppercase tracking-wider text-slate-500 font-outfit">Riwayat Laporan Saya</h3>
                        </div>
                        <a href="{{ route('dashboard') }}" class="text-3xs text-primary-600 hover:text-primary-700 font-bold flex items-center gap-0.5">
                            <i data-lucide="rotate-cw" class="w-2.5 h-2.5"></i> Refresh
                        </a>
                    </div>

                    <div class="p-4 space-y-3 max-h-[600px] overflow-y-auto custom-scroll">
                        @forelse($reports as $report)
                        <div class="p-3.5 rounded-lg border border-slate-200 hover:border-slate-300 transition-all bg-white relative group">
                            
                            <!-- Badges & Action Toolbar -->
                            <div class="flex items-start justify-between gap-3 mb-2">
                                <div class="flex flex-wrap items-center gap-1.5">
                                    <span class="px-2 py-0.5 rounded text-3xs font-extrabold border border-slate-200 bg-slate-50 text-slate-700 uppercase">
                                        {{ $report->shift }}
                                    </span>
                                    
                                    <!-- Dynamic status indicator -->
                                    @if($report->status == 'approved')
                                        <span class="px-2 py-0.5 rounded bg-emerald-50 text-emerald-700 border border-emerald-200 text-3xs font-bold uppercase">
                                            Disetujui
                                        </span>
                                    @elseif($report->status == 'rejected')
                                        <span class="px-2 py-0.5 rounded bg-rose-50 text-rose-700 border border-rose-200 text-3xs font-bold uppercase animate-pulse">
                                            Perlu Revisi
                                        </span>
                                    @else
                                        <span class="px-2 py-0.5 rounded bg-amber-50 text-amber-700 border border-amber-200 text-3xs font-bold uppercase">
                                            Menunggu
                                        </span>
                                    @endif
                                </div>

                                <!-- Action Buttons for Pending/Rejected reports -->
                                <div class="flex items-center gap-1.5">
                                    @if($report->status === 'rejected')
                                        <button onclick="editReport({{ json_encode($report) }})" class="p-1 rounded bg-blue-50 text-blue-600 hover:bg-blue-100 transition-all text-3xs font-bold flex items-center gap-1">
                                            <i data-lucide="edit-2" class="w-3 h-3"></i>
                                            <span>Revisi</span>
                                        </button>
                                    @endif

                                    @if($report->status !== 'approved')
                                        <form action="{{ route('wound-reports.destroy', $report->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus data laporan ini?')" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="p-1 rounded bg-slate-100 text-slate-400 hover:bg-rose-50 hover:text-rose-600 transition-all" title="Hapus Laporan">
                                                <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </div>

                            <!-- Core info -->
                            <div class="space-y-1">
                                <h4 class="text-xs font-bold text-slate-800">
                                    {{ $report->produk_yang_dikerjakan }}
                                    <span class="text-3xs text-slate-400 font-normal">({{ $report->jenis_produk }} - {{ $report->pengerjaan }})</span>
                                </h4>

                                <div class="flex items-center justify-between text-3xs text-slate-500 pt-1">
                                    <div>
                                        <span class="font-bold text-slate-700">Hasil:</span>
                                        <span class="text-xs font-extrabold text-slate-800">{{ number_format($report->hasil) }}</span>
                                        <span class="font-medium text-slate-450">{{ $report->satuan }}</span>
                                    </div>
                                    <span class="font-mono text-slate-400">
                                        {{ \Carbon\Carbon::parse($report->tanggal)->format('d/m/Y') }}
                                    </span>
                                </div>

                                @if($report->keterangan)
                                <div class="mt-2 text-3xs text-slate-500 bg-slate-50 border border-slate-100 rounded p-1.5 italic font-outfit">
                                    "{{ $report->keterangan }}"
                                </div>
                                @endif

                                @if($report->status === 'rejected' && $report->catatan_revisi)
                                <div class="mt-2 p-2 rounded-lg bg-rose-50 border border-rose-100 text-3xs text-rose-800">
                                    <span class="font-bold flex items-center gap-1 text-rose-700 mb-0.5">
                                        <i data-lucide="alert-circle" class="w-3 h-3 text-rose-600"></i>
                                        Catatan Koordinator:
                                    </span>
                                    {{ $report->catatan_revisi }}
                                </div>
                                @endif
                            </div>

                        </div>
                        @empty
                        <div class="text-center py-10 space-y-2.5">
                            <div class="w-10 h-10 rounded-full bg-slate-50 border border-slate-200 flex items-center justify-center mx-auto text-slate-400">
                                <i data-lucide="folder-open" class="w-5 h-5"></i>
                            </div>
                            <div>
                                <h4 class="text-xs font-bold text-slate-650">Tidak ada riwayat kerja</h4>
                                <p class="text-3xs text-slate-400 mt-0.5">Laporan pengerjaan Anda akan muncul di sini setelah diajukan.</p>
                            </div>
                        </div>
                        @endforelse
                    </div>
                </div>

            </div>

            <script>
                // Highlight active radio card
                function updateRadioStyles() {
                    const cards = document.querySelectorAll('.radio-card');
                    cards.forEach(card => {
                        const input = card.querySelector('input');
                        if (input.checked) {
                            card.classList.remove('border-slate-300');
                            card.classList.add('border-primary-500', 'bg-primary-50/5');
                        } else {
                            card.classList.remove('border-primary-500', 'bg-primary-50/5');
                            card.classList.add('border-slate-300');
                        }
                    });
                }

                // Toggle shift schedule details collapse
                function toggleScheduleCollapse() {
                    const details = document.getElementById('schedule-details');
                    const chevron = document.getElementById('schedule-chevron');
                    if (!details || !chevron) return;
                    
                    if (details.classList.contains('hidden')) {
                        details.classList.remove('hidden');
                        chevron.classList.add('rotate-180');
                    } else {
                        details.classList.add('hidden');
                        chevron.classList.remove('rotate-180');
                    }
                }

                // Initial setup
                updateRadioStyles();

                // Countdown timer for Karyawan
                const countdownText = document.getElementById('countdown-text');
                const countdownFormText = document.getElementById('countdown-form-text');
                if (countdownText || countdownFormText) {
                    let serverTime = new Date({{ now('Asia/Jakarta')->timestamp * 1000 }});
                    
                    function updateCountdown() {
                        serverTime.setSeconds(serverTime.getSeconds() + 1);

                        const now = serverTime;
                        const currentHour = now.getHours();
                        const currentMin = now.getMinutes();
                        const currentTimeStr = `${String(currentHour).padStart(2, '0')}:${String(currentMin).padStart(2, '0')}`;

                        // Define windows
                        const windows = [
                            { name: 'Shift 3 / Lembur 2', start: '04:00', end: '07:00' },
                            { name: 'Shift 1', start: '12:00', end: '15:00' },
                            { name: 'Lembur Shift 1', start: '16:00', end: '19:00' },
                            { name: 'Shift 2', start: '20:00', end: '23:00' }
                        ];

                        // 1. Check if any window is currently active
                        let activeWindow = null;
                        for (const w of windows) {
                            if (currentTimeStr >= w.start && currentTimeStr < w.end) {
                                activeWindow = w;
                                break;
                            }
                        }

                        const countdownIcon = document.getElementById('countdown-icon');
                        const countdownFormIconBg = document.getElementById('countdown-form-icon-bg');
                        const countdownFormIcon = document.getElementById('countdown-form-icon');

                        if (activeWindow) {
                            // Calculate remaining time
                            const [endH, endM] = activeWindow.end.split(':').map(Number);
                            const endDate = new Date(now);
                            endDate.setHours(endH, endM, 0, 0);
                            
                            const diffMs = endDate - now;
                            const diffSecs = Math.max(0, Math.floor(diffMs / 1000));
                            
                            const hours = Math.floor(diffSecs / 3600);
                            const mins = Math.floor((diffSecs % 3600) / 60);
                            const secs = diffSecs % 60;
                            
                            const timeStr = `${String(hours).padStart(2, '0')}:${String(mins).padStart(2, '0')}:${String(secs).padStart(2, '0')}`;
                            
                            if (countdownText) {
                                countdownText.innerHTML = `<span class="text-emerald-400 font-bold">Bisa Laporan (${activeWindow.name})</span> sisa: <span class="font-mono text-white font-bold">${timeStr}</span>`;
                            }
                            if (countdownFormText) {
                                countdownFormText.innerHTML = `<span class="text-emerald-600 font-bold">Terbuka (${activeWindow.name})</span> &bull; Sisa waktu pengisian: <span class="font-mono font-bold bg-emerald-100 text-emerald-800 px-1.5 py-0.5 rounded">${timeStr}</span>`;
                            }
                            
                            if (countdownIcon) {
                                countdownIcon.className = 'w-3.5 h-3.5 text-emerald-400 animate-pulse';
                            }
                            if (countdownFormIconBg) {
                                countdownFormIconBg.className = 'w-8 h-8 rounded-lg bg-emerald-100 text-emerald-600 flex items-center justify-center shrink-0';
                            }
                            if (countdownFormIcon) {
                                countdownFormIcon.className = 'w-4 h-4 animate-pulse text-emerald-600';
                            }
                        } else {
                            // Find next window
                            let closestDiff = Infinity;
                            let nextW = null;
                            let isTomorrow = false;

                            for (const w of windows) {
                                const [startH, startM] = w.start.split(':').map(Number);
                                const startDate = new Date(now);
                                startDate.setHours(startH, startM, 0, 0);

                                let diffMs = startDate - now;
                                if (diffMs < 0) {
                                    // If it already passed today, check tomorrow
                                    startDate.setDate(startDate.getDate() + 1);
                                    diffMs = startDate - now;
                                    if (diffMs < closestDiff) {
                                        closestDiff = diffMs;
                                        nextW = w;
                                        isTomorrow = true;
                                    }
                                } else {
                                    if (diffMs < closestDiff) {
                                        closestDiff = diffMs;
                                        nextW = w;
                                        isTomorrow = false;
                                    }
                                }
                            }

                            if (nextW) {
                                const diffSecs = Math.max(0, Math.floor(closestDiff / 1000));
                                const hours = Math.floor(diffSecs / 3600);
                                const mins = Math.floor((diffSecs % 3600) / 60);
                                const secs = diffSecs % 60;
                                
                                const timeStr = `${String(hours).padStart(2, '0')}:${String(mins).padStart(2, '0')}:${String(secs).padStart(2, '0')}`;
                                
                                if (countdownText) {
                                    countdownText.innerHTML = `Buka dalam: <span class="font-mono text-amber-400 font-bold">${timeStr}</span> (${nextW.name}${isTomorrow ? ' Besok' : ''})`;
                                }
                                if (countdownFormText) {
                                    countdownFormText.innerHTML = `<span class="text-amber-600 font-bold">Ditutup</span> &bull; Buka dalam: <span class="font-mono font-bold bg-amber-100 text-amber-800 px-1.5 py-0.5 rounded">${timeStr}</span> (${nextW.name}${isTomorrow ? ' Besok' : ''})`;
                                }
                                
                                if (countdownIcon) {
                                    countdownIcon.className = 'w-3.5 h-3.5 text-amber-400 animate-pulse';
                                }
                                if (countdownFormIconBg) {
                                    countdownFormIconBg.className = 'w-8 h-8 rounded-lg bg-amber-100 text-amber-600 flex items-center justify-center shrink-0';
                                }
                                if (countdownFormIcon) {
                                    countdownFormIcon.className = 'w-4 h-4 animate-pulse text-amber-650';
                                }
                            }
                        }
                    }

                    // Run immediately and then every second
                    updateCountdown();
                    setInterval(updateCountdown, 1000);
                }

                // Edit function for rejected records
                function editReport(report) {
                    document.getElementById('report_id').value = report.id;
                    document.getElementById('tanggal').value = report.tanggal;
                    
                    // Set shift radio button
                    const radios = document.querySelectorAll('input[name="shift"]');
                    radios.forEach(radio => {
                        radio.checked = (radio.value === report.shift);
                    });
                    
                    document.getElementById('pengerjaan').value = report.pengerjaan;
                    document.getElementById('jenis_produk').value = report.jenis_produk;
                    document.getElementById('produk_yang_dikerjakan').value = report.produk_yang_dikerjakan;
                    document.getElementById('hasil').value = report.hasil;
                    document.getElementById('satuan').value = report.satuan;
                    document.getElementById('keterangan').value = report.keterangan;

                    // Show info banner
                    document.getElementById('editBanner').classList.remove('hidden');
                    document.getElementById('editReportName').textContent = report.produk_yang_dikerjakan;

                    // Scroll to form smoothly
                    document.getElementById('productionForm').scrollIntoView({ behavior: 'smooth' });
                    updateRadioStyles();
                }

                // Cancel Edit
                function cancelEdit() {
                    document.getElementById('report_id').value = '';
                    document.getElementById('editBanner').classList.add('hidden');
                    clearFormFields();
                }

                function clearFormFields() {
                    document.getElementById('tanggal').value = new Date().toISOString().split('T')[0];
                    document.getElementById('pengerjaan').selectedIndex = 0;
                    document.getElementById('jenis_produk').selectedIndex = 0;
                    document.getElementById('produk_yang_dikerjakan').value = '';
                    document.getElementById('hasil').value = '';
                    document.getElementById('satuan').selectedIndex = 0;
                    document.getElementById('keterangan').value = '';
                }

                function clearForm() {
                    if(confirm("Apakah Anda yakin ingin mengosongkan form?")) {
                        clearFormFields();
                        document.getElementById('report_id').value = '';
                        document.getElementById('editBanner').classList.add('hidden');
                    }
                }
            </script>
            
        @endif

    </main>

    <!-- Footer -->
    <footer class="bg-slate-900 border-t border-slate-800 text-slate-400 py-6 mt-12 shrink-0">
        <div class="max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 text-center sm:flex sm:items-center sm:justify-between">
            <span class="text-2xs block sm:inline">&copy; 2026 Woundcare. All rights reserved.</span>
            <span class="text-3xs text-slate-500 block sm:inline mt-1 sm:mt-0 uppercase tracking-wider font-semibold font-outfit">Sistem Verifikasi & Proteksi Laporan Harian</span>
        </div>
    </footer>

    <script>
        // Initialize lucide icons for both layouts
        lucide.createIcons();
    </script>
</body>
</html>
