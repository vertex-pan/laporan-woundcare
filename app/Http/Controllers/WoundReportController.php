<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\WoundReport;
use App\Models\Operator;
use App\Models\Pengerjaan;
use App\Models\JenisProduk;
use App\Models\Satuan;
use Carbon\Carbon;

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

        return view('welcome', compact('reports', 'pengerjaans', 'jenisProduks', 'satuans', 'allOperators', 'shiftWindows', 'submittedKeys'));
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
        if ($role === 'karyawan' && !$isRevision) {
            $windowCheck = $this->checkShiftWindow($shiftName);
            if (!$windowCheck['allowed']) {
                return redirect()->back()
                    ->withInput()
                    ->withErrors('Penguncian Waktu: ' . $windowCheck['reason']);
            }
        }

        // 2. Set operator metadata
        $validated['operator_id'] = session('operator_id');
        $validated['operator'] = session('operator_name');
        $validated['vendor'] = session('operator_vendor');
        $validated['status'] = 'pending';
        $validated['catatan_revisi'] = null;

        // If editing an existing report
        if ($request->filled('report_id')) {
            $report = WoundReport::where('operator_id', session('operator_id'))
                ->findOrFail($request->report_id);
            $report->update($validated);
            return redirect()->route('dashboard')->with('success', 'Laporan berhasil diperbarui.');
        }

        WoundReport::create($validated);

        return redirect()->route('dashboard')->with('success', 'Laporan pengerjaan berhasil diajukan. Menunggu persetujuan Koordinator.');
    }

    public function approve(Request $request, $id)
    {
        if (session('operator_role') !== 'coordinator') {
            return redirect()->back()->withErrors('Hanya koordinator yang dapat menyetujui laporan.');
        }

        $report = WoundReport::findOrFail($id);
        $report->update([
            'status' => 'approved',
            'catatan_revisi' => null
        ]);

        return redirect()->route('dashboard')->with('success', 'Laporan dari ' . $report->operator . ' disetujui.');
    }

    public function reject(Request $request, $id)
    {
        if (session('operator_role') !== 'coordinator') {
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

        return redirect()->route('dashboard')->with('success', 'Laporan dari ' . $report->operator . ' ditolak untuk direvisi.');
    }

    public function destroy($id)
    {
        $report = WoundReport::findOrFail($id);

        // Security check: staff can only delete their own pending/rejected reports
        if (session('operator_role') !== 'coordinator') {
            if ($report->operator_id !== session('operator_id') || $report->status === 'approved') {
                return redirect()->back()->withErrors('Anda tidak memiliki wewenang untuk menghapus laporan ini.');
            }
        }

        $report->delete();

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

        // Store in cache for 20 minutes
        \Illuminate\Support\Facades\Cache::put('emergency_pin_' . $operator->id, $pin, now()->addMinutes(20));

        return redirect()->back()->with('success', "PIN Darurat berhasil dibuat.")
            ->with('emergency_pin_generated', [
                'operator_id' => $operator->id,
                'operator_name' => $operator->name,
                'pin' => $pin,
                'expires_at' => now()->addMinutes(20)->format('H:i')
            ]);
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
