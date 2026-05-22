<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\WoundReport;
use App\Models\Operator;
use App\Models\Pengerjaan;
use App\Models\JenisProduk;
use App\Models\Satuan;
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class WoundReportController extends Controller
{
    public function index(Request $request)
    {
        $role = session('operator_role');
        $operatorId = session('operator_id');

        $query = WoundReport::query();

        // 1. Filter based on Role
        if ($role === 'coordinator') {
            // Coordinator sees everything
            if ($request->filled('status')) {
                $query->where('status', $request->input('status'));
            }
            if ($request->filled('operator_filter')) {
                $query->where('operator_id', $request->input('operator_filter'));
            }
        } else {
            // Staff sees only their own reports
            $query->where('operator_id', $operatorId);
        }

        // 2. Search filter
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function($q) use ($search) {
                $q->where('produk_yang_dikerjakan', 'like', "%{$search}%")
                  ->orWhere('pengerjaan', 'like', "%{$search}%")
                  ->orWhere('jenis_produk', 'like', "%{$search}%")
                  ->orWhere('vendor', 'like', "%{$search}%")
                  ->orWhere('operator', 'like', "%{$search}%")
                  ->orWhere('keterangan', 'like', "%{$search}%");
            });
        }

        // Get matching reports ordered by newest
        $reports = $query->orderBy('tanggal', 'desc')->orderBy('created_at', 'desc')->get();

        // Get other master data
        $pengerjaans = Pengerjaan::orderBy('name')->get();
        $jenisProduks = JenisProduk::orderBy('name')->get();
        $satuans = Satuan::orderBy('name')->get();
        $allOperators = Operator::orderBy('name')->get();

        // Shift schedule info to display on UI
        $shiftWindows = [
            'Shift 1' => ['work' => '06.00 - 14.00', 'window' => '12.00 - 15.00'],
            'Shift 2' => ['work' => '14.00 - 22.00', 'window' => '20.00 - 23.00'],
            'Shift 3' => ['work' => '22.00 - 06.00', 'window' => '04.00 - 07.00 (Besok)'],
            'Lembur Shift 1' => ['work' => '06.00 - 18.00', 'window' => '16.00 - 19.00'],
            'Lembur Shift 2' => ['work' => '18.00 - 06.00', 'window' => '04.00 - 07.00 (Besok)'],
        ];

        // Fetch submitted report keys (date|shift => ID) for the logged in operator
        $submittedKeys = WoundReport::where('operator_id', session('operator_id'))
            ->get(['tanggal', 'shift', 'id'])
            ->mapWithKeys(function ($r) {
                return [$r->tanggal . '|' . $r->shift => $r->id];
            })
            ->toArray();

        // Fetch active late PINs for Coordinator display (persists on refresh, disappears on use/close)
        $activeLatePins = [];
        $activeEmergencyPins = [];
        if ($role === 'coordinator') {
            foreach ($allOperators as $op) {
                // Late PINs
                $metaLate = \Illuminate\Support\Facades\Cache::get('late_pin_meta_' . $op->id);
                if ($metaLate) {
                    if (now('Asia/Jakarta')->timestamp < $metaLate['expires_timestamp']) {
                        $activeLatePins[$op->id] = $metaLate;
                    } else {
                        \Illuminate\Support\Facades\Cache::forget('late_pin_meta_' . $op->id);
                        \Illuminate\Support\Facades\Cache::forget('late_pin_' . $op->id);
                    }
                }

                // Emergency PINs (Forgot Phone)
                $metaEmergency = \Illuminate\Support\Facades\Cache::get('emergency_pin_meta_' . $op->id);
                if ($metaEmergency) {
                    if (now('Asia/Jakarta')->timestamp < $metaEmergency['expires_timestamp']) {
                        $activeEmergencyPins[$op->id] = $metaEmergency;
                    } else {
                        \Illuminate\Support\Facades\Cache::forget('emergency_pin_meta_' . $op->id);
                        \Illuminate\Support\Facades\Cache::forget('emergency_pin_' . $op->id);
                    }
                }
            }
        }

        return view('welcome', compact('reports', 'pengerjaans', 'jenisProduks', 'satuans', 'allOperators', 'shiftWindows', 'submittedKeys', 'activeLatePins', 'activeEmergencyPins'));
    }

    public function store(Request $request)
    {
        // Clean 'hasil' from thousands separator dots
        if ($request->has('hasil')) {
            $request->merge(['hasil' => str_replace('.', '', $request->hasil)]);
        }

        $validated = $request->validate([
            'tanggal' => 'required|date',
            'pengerjaan' => 'required|string|max:255',
            'jenis_produk' => 'required|string|max:255',
            'shift' => 'required|string|max:255',
            'hasil' => 'required|integer|min:0',
            'produk_yang_dikerjakan' => 'required|string|max:255',
            'satuan' => 'required|string|max:255',
            'keterangan' => 'required|string',
        ]);

        $op = Operator::find(session('operator_id'));
        if (!$op || empty($op->whatsapp)) {
            return redirect()->back()
                ->withInput()
                ->withErrors('Nomor WhatsApp belum diatur. Silakan atur nomor WhatsApp aktif Anda terlebih dahulu di menu Pengaturan (ikon roda gigi di kanan atas) agar Anda bisa menerima notifikasi revisi.');
        }

        // Check for duplicate report (same operator, date, and mutually exclusive shift group)
        $shiftGroups = [
            'Shift 1' => ['Shift 1', 'Lembur Shift 1'],
            'Lembur Shift 1' => ['Shift 1', 'Lembur Shift 1'],
            'Shift 2' => ['Shift 2', 'Lembur Shift 2'],
            'Lembur Shift 2' => ['Shift 2', 'Lembur Shift 2'],
            'Shift 3' => ['Shift 3'],
        ];
        $targetShifts = $shiftGroups[$validated['shift']] ?? [$validated['shift']];

        $duplicateQuery = WoundReport::where('operator_id', session('operator_id'))
            ->where('tanggal', $validated['tanggal'])
            ->whereIn('shift', $targetShifts);

        if ($request->filled('report_id')) {
            $duplicateQuery->where('id', '!=', $request->report_id);
        }

        if ($duplicateQuery->exists()) {
            $existingReport = $duplicateQuery->first();
            return redirect()->back()
                ->withInput()
                ->withErrors('Duplikasi Laporan: Anda sudah mengirimkan laporan untuk Tanggal Kerja ' . $validated['tanggal'] . ' pada ' . $existingReport->shift . '. Anda tidak dapat mengirimkan laporan ganda pada shift yang sama (termasuk versi Lembur). Silakan lakukan edit/revisi pada laporan yang sudah terdaftar di tabel Riwayat Laporan.');
        }

        $role = session('operator_role');
        $shiftName = $validated['shift'];

        // Check if this is a valid revision (resubmission/edit of an existing report by the owner)
        $isRevision = false;
        if ($request->filled('report_id')) {
            $isRevision = WoundReport::where('operator_id', session('operator_id'))
                ->where('id', $request->report_id)
                ->exists();
        }

        // 1. Strict time lock for Karyawan (bypassed for revisions)
        $isLateSubmission = false;
        if ($role === 'karyawan' && !$isRevision) {
            $windowCheck = $this->checkShiftWindow($shiftName);
            if (!$windowCheck['allowed']) {
                // If late, we verify the late bypass PIN from the coordinator
                $cachedPin = \Illuminate\Support\Facades\Cache::get('late_pin_' . session('operator_id'));
                $submittedPin = $request->input('late_bypass_pin');
                
                if (empty($submittedPin) || $submittedPin !== $cachedPin) {
                    return redirect()->back()
                        ->withInput()
                        ->withErrors('Penguncian Waktu: Batas waktu pengisian reguler telah ditutup. Silakan masukkan PIN Akses Keterlambatan 6-digit yang valid dari Koordinator Anda.');
                }
                
                // PIN is valid! Set status directly to 'telat'
                $isLateSubmission = true;
            }
        }

        // 2. Set operator metadata
        $validated['operator_id'] = session('operator_id');
        $validated['operator'] = session('operator_name');
        $validated['vendor'] = session('operator_vendor');
        $validated['status'] = $isLateSubmission ? 'pending_late' : 'pending';
        $validated['catatan_revisi'] = null;

        // If editing an existing report
        if ($request->filled('report_id')) {
            $report = WoundReport::where('operator_id', session('operator_id'))
                ->findOrFail($request->report_id);
            
            // If the report was originally pending_late, telat, or if it is a new late submission
            $wasLate = in_array($report->status, ['pending_late', 'telat']);
            if ($wasLate || $isLateSubmission) {
                $validated['status'] = 'pending_late';
            } else {
                $validated['status'] = 'pending';
            }

            $report->update($validated);
            
            if ($isLateSubmission) {
                \Illuminate\Support\Facades\Cache::forget('late_pin_' . session('operator_id'));
                \Illuminate\Support\Facades\Cache::forget('late_pin_meta_' . session('operator_id'));
            }
            return redirect()->route('dashboard')->with('success', 'Laporan berhasil diperbarui.');
        }

        WoundReport::create($validated);

        if ($isLateSubmission) {
            \Illuminate\Support\Facades\Cache::forget('late_pin_' . session('operator_id'));
            \Illuminate\Support\Facades\Cache::forget('late_pin_meta_' . session('operator_id'));
        }

        return redirect()->route('dashboard')->with('success', $isLateSubmission ? 'Laporan keterlambatan berhasil diajukan. Menunggu persetujuan khusus Koordinator.' : 'Laporan pengerjaan berhasil diajukan. Menunggu persetujuan Koordinator.');
    }

    public function approve(Request $request, $id)
    {
        if (session('operator_role') !== 'coordinator') {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Hanya koordinator yang dapat menyetujui laporan.'], 403);
            }
            return redirect()->back()->withErrors('Hanya koordinator yang dapat menyetujui laporan.');
        }

        $report = WoundReport::findOrFail($id);
        $newStatus = ($report->status === 'pending_late') ? 'telat' : 'approved';
        $report->update([
            'status' => $newStatus,
            'catatan_revisi' => null
        ]);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Laporan dari ' . $report->operator . ' disetujui.',
                'new_status' => $newStatus
            ]);
        }

        return redirect()->route('dashboard')->with('success', 'Laporan dari ' . $report->operator . ' disetujui.');
    }

    public function reject(Request $request, $id)
    {
        if (session('operator_role') !== 'coordinator') {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Hanya koordinator yang dapat menolak laporan.'], 403);
            }
            return redirect()->back()->withErrors('Hanya koordinator yang dapat menolak laporan.');
        }

        $request->validate([
            'catatan_revisi' => 'required|string|max:500',
        ]);

        $report = WoundReport::findOrFail($id);
        $report->update([
            'status' => 'rejected',
            'catatan_revisi' => $request->catatan_revisi
        ]);

        // Send WhatsApp notification using WablasService
        $operator = Operator::find($report->operator_id);
        if ($operator && !empty($operator->whatsapp)) {
            try {
                $wablas = new \App\Services\WablasService();
                $message = "Halo *{$operator->name}*,\n\n"
                         . "Laporan produksi Anda untuk produk *{$report->produk_yang_dikerjakan}* (Shift: {$report->shift}, Tanggal: {$report->tanggal}) telah *DITOLAK oleh Koordinator*.\n\n"
                         . "*Catatan Revisi*:\n"
                         . "\"{$request->catatan_revisi}\"\n\n"
                         . "Mohon segera lakukan perbaikan laporan melalui tautan berikut:\n"
                         . "https://woundcare.fun";
                $wablas->send($operator->whatsapp, $message);
            } catch (\Exception $e) {
                // Logged inside WablasService, we continue execution without crashing
            }
        }

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Laporan dari ' . $report->operator . ' ditolak untuk direvisi.',
                'new_status' => 'rejected'
            ]);
        }

        return redirect()->route('dashboard')->with('success', 'Laporan dari ' . $report->operator . ' ditolak untuk direvisi.');
    }

    public function destroy($id)
    {
        $report = WoundReport::findOrFail($id);

        // Security check: staff can only delete their own pending/rejected reports
        if (session('operator_role') !== 'coordinator') {
            if ($report->operator_id !== session('operator_id') || $report->status === 'approved') {
                if (request()->ajax() || request()->wantsJson()) {
                    return response()->json(['success' => false, 'message' => 'Anda tidak memiliki wewenang untuk menghapus laporan ini.'], 403);
                }
                return redirect()->back()->withErrors('Anda tidak memiliki wewenang untuk menghapus laporan ini.');
            }
        }

        $report->delete();

        if (request()->ajax() || request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Laporan pengerjaan berhasil dihapus.'
            ]);
        }

        return redirect()->route('dashboard')->with('success', 'Laporan pengerjaan berhasil dihapus.');
    }

    /**
     * Check if current time is within the allowed shift window.
     */
    private function checkShiftWindow($shiftName)
    {
        $now = Carbon::now('Asia/Jakarta');
        $timeString = $now->format('H:i');

        // Formatted window messages
        $windows = [
            'Shift 1' => ['start' => '12:00', 'end' => '15:00', 'desc' => '12.00 - 15.00'],
            'Shift 2' => ['start' => '20:00', 'end' => '23:00', 'desc' => '20.00 - 23.00'],
            'Shift 3' => ['start' => '04:00', 'end' => '07:00', 'desc' => '04.00 - 07.00 pagi'],
            'Lembur Shift 1' => ['start' => '16:00', 'end' => '19:00', 'desc' => '16.00 - 19.00'],
            'Lembur Shift 2' => ['start' => '04:00', 'end' => '07:00', 'desc' => '04.00 - 07.00 pagi'],
        ];

        if (!isset($windows[$shiftName])) {
            return ['allowed' => false, 'reason' => 'Shift tidak valid.'];
        }

        $rule = $windows[$shiftName];
        
        // Handle normal shift check (same day range)
        $isAllowed = ($timeString >= $rule['start'] && $timeString <= $rule['end']);

        if ($isAllowed) {
            return ['allowed' => true];
        }

        return [
            'allowed' => false,
            'reason' => "Form pengisian untuk {$shiftName} hanya dibuka pada pukul {$rule['desc']}. Jam server saat ini: {$timeString}."
        ];
    }

    public function resetEmail($id)
    {
        if (session('operator_role') !== 'coordinator') {
            return redirect()->back()->withErrors(['access' => 'Anda tidak memiliki akses.']);
        }

        $operator = Operator::findOrFail($id);
        $operator->email = null;
        $operator->save();

        return redirect()->back()->with('success', "Akun Google untuk operator {$operator->name} berhasil di-reset.");
    }

    public function updateOperator(Request $request, $id)
    {
        if (session('operator_role') !== 'coordinator') {
            return redirect()->back()->withErrors(['access' => 'Anda tidak memiliki akses.']);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'vendor' => 'required|string|in:KWI,MJA,AA,IPS,JMI',
            'role' => 'required|string|in:coordinator,karyawan',
            'whatsapp' => 'nullable|string|max:20',
        ]);

        $operator = Operator::findOrFail($id);
        $operator->name = $request->input('name');
        $operator->vendor = $request->input('vendor');
        $operator->role = $request->input('role');
        $operator->whatsapp = $request->input('whatsapp');
        $operator->save();

        return redirect()->back()->with('success', "Data operator {$operator->name} berhasil diperbarui.");
    }

    public function destroyOperator($id)
    {
        if (session('operator_role') !== 'coordinator') {
            return redirect()->back()->withErrors(['access' => 'Anda tidak memiliki akses.']);
        }

        $operator = Operator::findOrFail($id);
        $name = $operator->name;
        $operator->delete();

        return redirect()->back()->with('success', "Operator {$name} berhasil dihapus.");
    }

    public function generateEmergencyPin($id)
    {
        if (session('operator_role') !== 'coordinator') {
            return redirect()->back()->withErrors(['access' => 'Anda tidak memiliki akses.']);
        }

        $operator = Operator::findOrFail($id);
        
        // Generate a 6-digit random PIN
        $pin = str_pad(rand(100000, 999999), 6, '0', STR_PAD_LEFT);
        $expiresAt = now('Asia/Jakarta')->addMinutes(20);

        // Store in cache for 20 minutes
        \Illuminate\Support\Facades\Cache::put('emergency_pin_' . $operator->id, $pin, $expiresAt);

        // Store metadata in cache for Coordinator display toast
        $metaData = [
            'operator_id' => $operator->id,
            'operator_name' => $operator->name,
            'pin' => $pin,
            'expires_at' => $expiresAt->format('H:i'),
            'expires_timestamp' => $expiresAt->timestamp
        ];
        \Illuminate\Support\Facades\Cache::put('emergency_pin_meta_' . $operator->id, $metaData, $expiresAt);

        return redirect()->back()->with('success', "PIN Darurat (Lupa HP) berhasil dibuat untuk {$operator->name}.");
    }

    public function dismissEmergencyPin($id)
    {
        if (session('operator_role') !== 'coordinator') {
            return redirect()->back()->withErrors(['access' => 'Anda tidak memiliki akses.']);
        }

        \Illuminate\Support\Facades\Cache::forget('emergency_pin_' . $id);
        \Illuminate\Support\Facades\Cache::forget('emergency_pin_meta_' . $id);

        return redirect()->back()->with('success', 'PIN Darurat (Lupa HP) berhasil ditutup.');
    }

    public function generateLatePin($id)
    {
        if (session('operator_role') !== 'coordinator') {
            return redirect()->back()->withErrors(['access' => 'Anda tidak memiliki akses.']);
        }

        $operator = Operator::findOrFail($id);
        
        // Generate a 6-digit random PIN
        $pin = str_pad(rand(100000, 999999), 6, '0', STR_PAD_LEFT);
        $expiresAt = now('Asia/Jakarta')->addMinutes(60);

        // Store in cache for 60 minutes (1 hour)
        \Illuminate\Support\Facades\Cache::put('late_pin_' . $operator->id, $pin, $expiresAt);

        // Store metadata in cache for Coordinator display toast
        $metaData = [
            'operator_id' => $operator->id,
            'operator_name' => $operator->name,
            'pin' => $pin,
            'expires_at' => $expiresAt->format('H:i'),
            'expires_timestamp' => $expiresAt->timestamp
        ];
        \Illuminate\Support\Facades\Cache::put('late_pin_meta_' . $operator->id, $metaData, $expiresAt);

        return redirect()->back()->with('success', "PIN Keterlambatan berhasil dibuat untuk {$operator->name}.");
    }

    public function dismissLatePin($id)
    {
        if (session('operator_role') !== 'coordinator') {
            return redirect()->back()->withErrors(['access' => 'Anda tidak memiliki akses.']);
        }

        \Illuminate\Support\Facades\Cache::forget('late_pin_' . $id);
        \Illuminate\Support\Facades\Cache::forget('late_pin_meta_' . $id);

        return redirect()->back()->with('success', 'PIN Keterlambatan berhasil ditutup.');
    }

    public function export(Request $request)
    {
        if (session('operator_role') !== 'coordinator') {
            return redirect()->back()->withErrors('Hanya koordinator yang dapat mengekspor laporan.');
        }

        $query = WoundReport::query();

        // 1. Filter based on filters
        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }
        if ($request->filled('operator_filter')) {
            $query->where('operator_id', $request->input('operator_filter'));
        }

        // 2. Search filter
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function($q) use ($search) {
                $q->where('produk_yang_dikerjakan', 'like', "%{$search}%")
                  ->orWhere('pengerjaan', 'like', "%{$search}%")
                  ->orWhere('jenis_produk', 'like', "%{$search}%")
                  ->orWhere('vendor', 'like', "%{$search}%")
                  ->orWhere('operator', 'like', "%{$search}%")
                  ->orWhere('keterangan', 'like', "%{$search}%");
            });
        }

        // Eager load operatorRelation to retrieve Google emails quickly
        $reports = $query->with('operatorRelation')->orderBy('tanggal', 'desc')->orderBy('created_at', 'desc')->get();

        $format = $request->input('format', 'excel');

        // Create beautiful Indonesian date string for file name
        $monthsIndo = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        ];
        $currentDay = date('j');
        $currentMonth = $monthsIndo[(int)date('n')];
        $currentYear = date('Y');
        $dateString = "{$currentDay} {$currentMonth} {$currentYear}";

        // Layout matching previous Google Sheet template (Keterangan first, then Status)
        $columns = [
            'Timestamp',
            'Email Address',
            'Tanggal',
            'Shift',
            'Nama',
            'Vendor',
            'Pengerjaan',
            'Jenis Produk',
            'Produk yang Dikerjakan',
            'Hasil',
            'Satuan',
            'Keterangan',
            'Status'
        ];

        if ($format === 'csv') {
            $filename = "Laporan Produksi Woundcare - FILTERED - {$dateString}.csv";
            
            $headers = [
                "Content-type"        => "text/csv; charset=UTF-8",
                "Content-Disposition" => "attachment; filename=\"{$filename}\"",
                "Pragma"              => "no-cache",
                "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
                "Expires"             => "0"
            ];

            $callback = function() use($reports, $columns) {
                $file = fopen('php://output', 'w');
                
                // Add UTF-8 BOM for proper Excel encoding on Windows
                fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
                
                fputcsv($file, $columns, ';'); // Semicolon is best for Excel regional settings in Indonesia

                foreach ($reports as $report) {
                    $email = $report->operatorRelation ? $report->operatorRelation->email : '';
                    fputcsv($file, [
                        $report->created_at->format('d/m/Y H:i:s'),
                        $email,
                        \Carbon\Carbon::parse($report->tanggal)->format('d/m/Y'),
                        $report->shift,
                        $report->operator,
                        $report->vendor,
                        $report->pengerjaan,
                        $report->jenis_produk,
                        $report->produk_yang_dikerjakan,
                        $report->hasil,
                        $report->satuan,
                        $report->keterangan,
                        $report->status === 'telat' ? 'Telat Laporan' : ucfirst($report->status)
                    ], ';');
                }

                fclose($file);
            };

            return response()->stream($callback, 200, $headers);
        } else {
            // Excel Export using PhpSpreadsheet
            $spreadsheet = new Spreadsheet();
            
            // Set global font family and size for a crisp corporate look
            $spreadsheet->getDefaultStyle()->getFont()->setName('Arial')->setSize(10);
            
            $colLetters = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M'];
            
            // Indonesian month names mapping
            $months = [
                1 => 'JANUARI', 2 => 'FEBRUARI', 3 => 'MARET', 4 => 'APRIL',
                5 => 'MEI', 6 => 'JUNI', 7 => 'JULI', 8 => 'AGUSTUS',
                9 => 'SEPTEMBER', 10 => 'OKTOBER', 11 => 'NOVEMBER', 12 => 'DESEMBER'
            ];
            
            // Group reports by month name and year based on the work date
            $grouped = $reports->groupBy(function($report) use ($months) {
                $date = \Carbon\Carbon::parse($report->tanggal);
                return $months[$date->month] . ' ' . $date->year;
            });
            
            if ($grouped->isEmpty()) {
                // Default empty state
                $sheet = $spreadsheet->getActiveSheet();
                $sheet->setTitle('Laporan');
                
                // Write title banner (merged A2:M3)
                $sheet->mergeCells('A2:M3');
                $sheet->setCellValue('A2', 'LAPORAN PRODUKSI WOUNDCARE');
                $sheet->getStyle('A2')->getFont()->setSize(16)->setBold(true)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FF1565C0'));
                $sheet->getStyle('A2')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER)->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
                
                // Style banner background and outline border
                $sheet->getStyle('A2:M3')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FFE3F2FD');
                $sheet->getStyle('A2:M3')->getBorders()->getOutline()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM)->getColor()->setARGB('FF1565C0');
                
                // Set heights
                $sheet->getRowDimension(2)->setRowHeight(20);
                $sheet->getRowDimension(3)->setRowHeight(20);
                $sheet->getRowDimension(4)->setRowHeight(12); // spacer row
                
                // Write header in Row 5
                foreach ($columns as $colIndex => $colName) {
                    $sheet->setCellValue($colLetters[$colIndex] . '5', $colName);
                }
                
                // Style header
                $headerRange = 'A5:M5';
                $sheet->getStyle($headerRange)->getFont()->setBold(true)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_WHITE));
                $sheet->getStyle($headerRange)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FF1565C0');
                
                foreach ($colLetters as $letter) {
                    $sheet->getColumnDimension($letter)->setAutoSize(true);
                }
            } else {
                $sheetIndex = 0;
                foreach ($grouped as $monthYear => $monthReports) {
                    if ($sheetIndex === 0) {
                        $sheet = $spreadsheet->getActiveSheet();
                    } else {
                        $sheet = $spreadsheet->createSheet();
                    }
                    
                    // Set title (max 31 characters)
                    $sheet->setTitle(substr($monthYear, 0, 31));
                    
                    // Color the sheet tab accent to match the Material Blue theme
                    $sheet->getTabColor()->setARGB('FF1565C0');
                    
                    // 1. Title Banner Block (Row 2-3 Merged)
                    $sheet->mergeCells('A2:M3');
                    $sheet->setCellValue('A2', 'LAPORAN PRODUKSI WOUNDCARE');
                    $sheet->getStyle('A2')->getFont()->setSize(16)->setBold(true)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FF1565C0'));
                    $sheet->getStyle('A2')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER)->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
                    
                    // Style banner background and outline border
                    $sheet->getStyle('A2:M3')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FFE3F2FD');
                    $sheet->getStyle('A2:M3')->getBorders()->getOutline()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM)->getColor()->setARGB('FF1565C0');
                    
                    // Set row heights for elegant spacing
                    $sheet->getRowDimension(2)->setRowHeight(20);
                    $sheet->getRowDimension(3)->setRowHeight(20);
                    $sheet->getRowDimension(4)->setRowHeight(12); // spacer row
                    
                    // Freeze first row of data so header remains visible on scroll
                    $sheet->freezePane('A6');
                    
                    // Set header row height for breathing room
                    $sheet->getRowDimension(5)->setRowHeight(28);
                    
                    // Write header in Row 5
                    foreach ($columns as $colIndex => $colName) {
                        $sheet->setCellValue($colLetters[$colIndex] . '5', $colName);
                    }
                    
                    // Write data starting at Row 6
                    $row = 6;
                    $reportsArray = $monthReports->values()->all();
                    $totalReports = count($reportsArray);
                    
                    for ($i = 0; $i < $totalReports; $i++) {
                        $report = $reportsArray[$i];
                        $email = $report->operatorRelation ? $report->operatorRelation->email : '';
                        
                        // Set row height for elegant spacing
                        $sheet->getRowDimension($row)->setRowHeight(20);
                        
                        $sheet->setCellValue('A' . $row, $report->created_at->format('d/m/Y H:i:s'));
                        $sheet->setCellValue('B' . $row, $email);
                        $sheet->setCellValue('C' . $row, \Carbon\Carbon::parse($report->tanggal)->format('d/m/Y'));
                        $sheet->setCellValue('D' . $row, $report->shift);
                        $sheet->setCellValue('E' . $row, $report->operator);
                        $sheet->setCellValue('F' . $row, $report->vendor);
                        $sheet->setCellValue('G' . $row, $report->pengerjaan);
                        $sheet->setCellValue('H' . $row, $report->jenis_produk);
                        $sheet->setCellValue('I' . $row, $report->produk_yang_dikerjakan);
                        // Cast to numeric for Excel calculations
                        $sheet->setCellValueExplicit('J' . $row, (int)$report->hasil, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);
                        if ($report->status === 'telat') {
                            $sheet->setCellValue('M' . $row, 'Telat Laporan');
                        } else {
                            $sheet->setCellValue('M' . $row, ucfirst($report->status));
                        }
                        
                        // Zebra Striping (ultra soft blue-gray for even rows on columns A-L)
                        if ($row % 2 === 0) {
                            $sheet->getStyle('A' . $row . ':L' . $row)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FFF4F9FF');
                        }
                        
                        // Status styling with elegant pastel badges (Column M)
                        $statusCell = 'M' . $row;
                        $statusVal = strtolower($report->status);
                        if ($statusVal === 'telat') {
                            $sheet->getStyle($statusCell)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FFFFE082'); // Amber Pastel background
                            $sheet->getStyle($statusCell)->getFont()->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FFD84315'))->setBold(true); // Dark orange/amber text
                        } elseif ($statusVal === 'approved') {
                            $sheet->getStyle($statusCell)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FFE2F0D9');
                            $sheet->getStyle($statusCell)->getFont()->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FF385723'))->setBold(true);
                        } elseif ($statusVal === 'pending') {
                            $sheet->getStyle($statusCell)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FFFCE4D6');
                            $sheet->getStyle($statusCell)->getFont()->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FFC65911'))->setBold(true);
                        } elseif ($statusVal === 'rejected') {
                            $sheet->getStyle($statusCell)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FFF8CBAD');
                            $sheet->getStyle($statusCell)->getFont()->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FFC00000'))->setBold(true);
                        }
                        
                        $row++;
                    }
                    
                    // Auto size columns for beautiful presentation
                    foreach ($colLetters as $letter) {
                        $sheet->getColumnDimension($letter)->setAutoSize(true);
                    }
                    
                    // Style the header row (Solid Material Blue Hex #1565C0 background with White bold text)
                    $headerRange = 'A5:M5';
                    $sheet->getStyle($headerRange)->getFont()->setBold(true)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_WHITE));
                    $sheet->getStyle($headerRange)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FF1565C0');
                    
                    // Center align header horizontally and vertically
                    $sheet->getStyle($headerRange)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER)->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
                    
                    // Style borders, grid gridlines, and alignments
                    if ($row > 6) {
                        // Apply thin borders starting from Row 5
                        $sheet->getStyle('A5:M' . ($row - 1))->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN)->getColor()->setARGB('FFD3D3D3');
                        
                        // Apply elegant double bottom border as day separators (overlayed on top of grid borders)
                        $rowTracker = 6;
                        for ($i = 0; $i < $totalReports; $i++) {
                            $report = $reportsArray[$i];
                            $currentDate = \Carbon\Carbon::parse($report->tanggal)->format('Y-m-d');
                            $nextReport = ($i + 1 < $totalReports) ? $reportsArray[$i + 1] : null;
                            
                            if ($nextReport) {
                                $nextDate = \Carbon\Carbon::parse($nextReport->tanggal)->format('Y-m-d');
                                if ($currentDate !== $nextDate) {
                                    $sheet->getStyle('A' . $rowTracker . ':M' . $rowTracker)->getBorders()->getBottom()
                                        ->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_DOUBLE)
                                        ->getColor()->setARGB('FF1565C0');
                                }
                            }
                            $rowTracker++;
                        }
                        
                        // Format volume column (Hasil) with thousands separator
                        $sheet->getStyle('J6:J' . ($row - 1))->getNumberFormat()->setFormatCode('#,##0');
                        
                        // Center-align columns: C (Tanggal), D (Shift), F (Vendor), K (Satuan), M (Status)
                        $sheet->getStyle('C6:C' . ($row - 1))->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                        $sheet->getStyle('D6:D' . ($row - 1))->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                        $sheet->getStyle('F6:F' . ($row - 1))->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                        $sheet->getStyle('K6:K' . ($row - 1))->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                        $sheet->getStyle('M6:M' . ($row - 1))->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                        
                        // Apply professional padding/indent for left-aligned columns (Prevent text touching lines)
                        $leftAlignedCols = ['A', 'B', 'E', 'G', 'H', 'I', 'L'];
                        foreach ($leftAlignedCols as $col) {
                            $sheet->getStyle($col . '6:' . $col . ($row - 1))->getAlignment()->setIndent(1);
                        }
                        
                        // Apply standard Excel Auto-Filter dropdown arrows on Row 5
                        $sheet->setAutoFilter('A5:M' . ($row - 1));
                        
                        // Vertically align all data rows to center
                        $sheet->getStyle('A6:M' . ($row - 1))->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
                    }
                    
                    $sheetIndex++;
                }
            }
            
            // Set the first sheet (newest month) as the active one
            $spreadsheet->setActiveSheetIndex(0);
            
            // Output as download
            $filename = "Laporan Produksi Woundcare - LENGKAP - {$dateString}.xlsx";
            
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            header('Cache-Control: max-age=0');
            
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
            exit;
        }
    }

    public function updateMyWhatsapp(Request $request)
    {
        $request->validate([
            'whatsapp' => 'required|string|max:20',
        ]);

        $operator = Operator::findOrFail(session('operator_id'));
        $operator->update([
            'whatsapp' => $request->whatsapp
        ]);

        session(['operator_whatsapp' => $request->whatsapp]);

        return redirect()->back()->with('success', 'Nomor WhatsApp Anda berhasil diperbarui.');
    }
}
