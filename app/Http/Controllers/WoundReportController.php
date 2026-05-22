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

        return view('welcome', compact('reports', 'pengerjaans', 'jenisProduks', 'satuans', 'allOperators', 'shiftWindows'));
    }

    public function store(Request $request)
    {
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

        $role = session('operator_role');
        $shiftName = $validated['shift'];

        // 1. Strict time lock for Karyawan
        if ($role === 'karyawan') {
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

        // If editing an existing report (e.g. resubmitting a rejected draft)
        if ($request->filled('report_id')) {
            $report = WoundReport::where('operator_id', session('operator_id'))
                ->where('status', 'rejected')
                ->findOrFail($request->report_id);
            $report->update($validated);
            return redirect()->route('dashboard')->with('success', 'Laporan revisi berhasil diajukan ulang.');
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
}
