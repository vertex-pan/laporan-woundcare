<!DOCTYPE html>
<html lang="id" class="h-full bg-slate-900">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hubungkan Akun Google - Woundcare</title>
    
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
                    }
                }
            }
        }
    </script>
    <style>
        .custom-scrollbar::-webkit-scrollbar {
            width: 4px;
        }
        .custom-scrollbar::-webkit-scrollbar-track {
            background: transparent;
        }
        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: #334155;
            border-radius: 9999px;
        }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover {
            background: #475569;
        }
    </style>
</head>
<body class="min-h-full flex flex-col items-center justify-center p-4 sm:p-6 bg-slate-950 text-slate-100 font-sans py-8 sm:py-12">

    <!-- Background decorative blur glow -->
    <div class="absolute inset-0 overflow-hidden pointer-events-none">
        <div class="absolute -top-40 -left-40 w-96 h-96 rounded-full bg-blue-500/10 blur-[128px]"></div>
        <div class="absolute -bottom-40 -right-40 w-96 h-96 rounded-full bg-indigo-500/10 blur-[128px]"></div>
    </div>

    <!-- Main Card Container -->
    <div class="relative w-full max-w-md bg-slate-900/60 backdrop-blur-xl border border-slate-800 rounded-2xl p-6 sm:p-8 shadow-2xl space-y-6">
        
        <!-- Logo / Brand Header -->
        <div class="text-center space-y-2">
            <div class="w-12 h-12 rounded-2xl bg-gradient-to-tr from-blue-600 to-indigo-600 flex items-center justify-center mx-auto text-white shadow-lg shadow-indigo-500/10">
                <i data-lucide="link-2" class="w-6 h-6"></i>
            </div>
            <div>
                <h1 class="text-xl font-bold font-outfit text-white tracking-tight">Hubungkan Akun Google</h1>
                <p class="text-xs text-slate-400 mt-1">Langkah akhir untuk menyelesaikan pendaftaran profil Anda</p>
            </div>
        </div>

        <!-- Info / Notice box -->
        <div class="bg-indigo-500/10 border border-indigo-500/20 rounded-xl p-3.5 flex gap-3 text-xs text-indigo-300">
            <i data-lucide="info" class="w-4 h-4 shrink-0 mt-0.5"></i>
            <p>Akun Google Anda akan dihubungkan secara permanen ke profil operator Woundcare terpilih. Ini mencegah orang lain mengisi laporan atas nama Anda.</p>
        </div>

        <!-- Setup Form -->
        <form action="{{ route('login.setup-profile.save') }}" method="POST" class="space-y-4">
            @csrf
            
            <!-- Email Display (Read-Only) -->
            <div class="space-y-1.5">
                <label class="text-2xs font-extrabold uppercase text-slate-400 tracking-wider block">Akun Google Terhubung</label>
                <div class="flex items-center gap-2.5 px-3.5 py-2.5 bg-slate-950/60 border border-slate-800 rounded-lg text-slate-300 text-sm">
                    <i data-lucide="mail" class="w-4 h-4 text-slate-500"></i>
                    <span class="font-medium font-mono text-xs">{{ $googleEmail }}</span>
                </div>
            </div>

            <!-- Redesigned Operator Profile Selection -->
            <div class="space-y-3">
                <label class="text-2xs font-extrabold uppercase text-slate-400 tracking-wider block">Pilih Profil Operator Anda <span class="text-rose-500">*</span></label>
                
                <!-- Hidden input for form submission -->
                <input type="hidden" name="operator_id" id="operator_id" required>

                <!-- Vendor Filter Tab Pills -->
                <div class="space-y-1">
                    <span class="text-[10px] font-bold text-slate-550 uppercase tracking-wide">1. Filter Vendor</span>
                    <div id="vendor-tabs" class="flex flex-wrap gap-1">
                        <!-- Filled by JS -->
                    </div>
                </div>

                <!-- Search Input Field -->
                <div class="space-y-1">
                    <span class="text-[10px] font-bold text-slate-550 uppercase tracking-wide">2. Cari Nama</span>
                    <div class="relative">
                        <input type="text" id="search-name" placeholder="Ketik nama operator..." class="w-full rounded-lg border border-slate-800 bg-slate-950 py-2.5 pl-8 pr-8 text-xs text-slate-200 placeholder:text-slate-600 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all">
                        <div class="absolute inset-y-0 left-0 pl-2.5 flex items-center pointer-events-none text-slate-500">
                            <i data-lucide="search" class="w-3.5 h-3.5"></i>
                        </div>
                        <button type="button" id="clear-search" class="hidden absolute inset-y-0 right-0 pr-2.5 flex items-center text-slate-550 hover:text-slate-300">
                            <i data-lucide="x" class="w-3.5 h-3.5"></i>
                        </button>
                    </div>
                </div>

                <!-- Names Selection List (Simpler, Full-Width) -->
                <div class="space-y-1">
                    <span class="text-[10px] font-bold text-slate-550 uppercase tracking-wide">3. Pilih Nama</span>
                    <div class="bg-slate-950/40 border border-slate-800/80 rounded-xl p-2">
                        <!-- Scrollable Name Cards List -->
                        <div id="operator-list" class="overflow-y-auto max-h-56 pr-0.5 space-y-1 custom-scrollbar">
                            <!-- Filled by JS -->
                        </div>
                    </div>
                </div>

                <p class="text-[10px] text-slate-500 leading-normal mt-1">Jika nama Anda tidak muncul, kemungkinan akun Anda sudah dihubungkan oleh rekan kerja lain atau hubungi admin.</p>
            </div>

            <!-- Action buttons -->
            <div class="flex flex-col gap-2 pt-3">
                <button type="submit" class="w-full py-2.5 rounded-lg bg-blue-600 hover:bg-blue-500 text-white font-bold text-xs shadow-md transition-all active:scale-[0.98] flex items-center justify-center gap-2">
                    <i data-lucide="check" class="w-4 h-4"></i> Hubungkan Akun & Masuk
                </button>
                <a href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();" class="w-full py-2.5 rounded-lg border border-slate-800 hover:bg-slate-900 text-slate-400 font-semibold text-xs text-center transition-all">
                    Batal / Keluar
                </a>
            </div>

        </form>

        <form id="logout-form" action="{{ route('logout') }}" method="POST" class="hidden">
            @csrf
        </form>

    </div>

    <script>
        // Init Lucide
        lucide.createIcons();

        // 1. Data injection from Blade
        const operators = @json($availableOperators);

        // 2. State management
        let selectedVendor = 'ALL';
        let searchQuery = '';
        let selectedOperatorId = null;

        // Extract unique vendors
        const vendors = ['ALL', ...[...new Set(operators.map(op => op.vendor))].sort()];

        // DOM elements
        const vendorTabsContainer = document.getElementById('vendor-tabs');
        const searchInput = document.getElementById('search-name');
        const clearSearchBtn = document.getElementById('clear-search');
        const operatorListContainer = document.getElementById('operator-list');
        const hiddenOperatorInput = document.getElementById('operator_id');

        // 3. Initialize components
        function init() {
            // Render vendor tabs
            renderVendorTabs();
            // Render operator list
            renderOperators();

            // Set up search event listeners
            searchInput.addEventListener('input', (e) => {
                searchQuery = e.target.value;
                if (searchQuery.trim().length > 0) {
                    clearSearchBtn.classList.remove('hidden');
                } else {
                    clearSearchBtn.classList.add('hidden');
                }
                renderOperators();
            });

            clearSearchBtn.addEventListener('click', () => {
                searchInput.value = '';
                searchQuery = '';
                clearSearchBtn.classList.add('hidden');
                renderOperators();
                searchInput.focus();
            });
        }

        // 4. Render Vendor Tabs
        function renderVendorTabs() {
            vendorTabsContainer.innerHTML = '';
            vendors.forEach(v => {
                const btn = document.createElement('button');
                btn.type = 'button';
                btn.className = `px-2.5 py-1 text-2xs font-semibold rounded-md border transition-all ${
                    selectedVendor === v
                        ? 'bg-blue-600/15 border-blue-500/80 text-blue-400 shadow-sm'
                        : 'bg-slate-900 border-slate-800/80 text-slate-400 hover:text-slate-200 hover:border-slate-700'
                }`;
                btn.textContent = v === 'ALL' ? 'Semua' : v;
                btn.addEventListener('click', () => {
                    selectedVendor = v;
                    renderVendorTabs();
                    renderOperators();
                });
                vendorTabsContainer.appendChild(btn);
            });
        }

        // 5. Render Operator List
        function renderOperators() {
            operatorListContainer.innerHTML = '';

            // Filter operators
            const filtered = operators.filter(op => {
                // Vendor filter
                if (selectedVendor !== 'ALL' && op.vendor !== selectedVendor) {
                    return false;
                }
                // Search query filter
                if (searchQuery.trim() !== '') {
                    const q = searchQuery.toLowerCase();
                    return op.name.toLowerCase().includes(q) || op.vendor.toLowerCase().includes(q);
                }
                return true;
            });

            if (filtered.length === 0) {
                const empty = document.createElement('div');
                empty.className = 'text-center py-8 text-xs text-slate-500';
                empty.textContent = 'Tidak ada operator yang cocok.';
                operatorListContainer.appendChild(empty);
                return;
            }

            filtered.forEach(op => {
                const card = document.createElement('div');
                const isSelected = selectedOperatorId === op.id;
                
                card.className = `flex items-center justify-between p-2.5 rounded-lg border transition-all cursor-pointer ${
                    isSelected
                        ? 'bg-blue-600/10 border-blue-500 text-blue-200'
                        : 'bg-slate-900/60 border-slate-800/80 text-slate-350 hover:border-slate-700 hover:bg-slate-900'
                }`;
                
                // Card Inner HTML
                card.innerHTML = `
                    <div class="flex items-center gap-2">
                        <div class="w-1.5 h-1.5 rounded-full ${isSelected ? 'bg-blue-500' : 'bg-slate-750'}"></div>
                        <span class="text-xs font-semibold tracking-tight">${op.name}</span>
                    </div>
                    <span class="px-1.5 py-0.5 rounded text-[9px] font-bold uppercase ${
                        isSelected 
                            ? 'bg-blue-500/20 text-blue-300' 
                            : 'bg-slate-800 text-slate-400'
                    }">${op.vendor}</span>
                `;

                card.addEventListener('click', () => {
                    selectedOperatorId = op.id;
                    hiddenOperatorInput.value = op.id;
                    renderOperators(); // Refresh to update highlights
                });

                operatorListContainer.appendChild(card);
            });
        }

        // Initialize on load
        init();
    </script>
</body>
</html>
