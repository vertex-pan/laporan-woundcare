<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\WoundReport;

class WoundReportController extends Controller
{
    public function index(Request $request)
    {
        $query = WoundReport::query();

        // Optional search filter
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

        // Get matching logs ordered by newest date
        $reports = $query->orderBy('tanggal', 'desc')->orderBy('created_at', 'desc')->get();

        return view('welcome', compact('reports'));
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
            'vendor' => 'required|string|max:255',
            'operator' => 'required|string|max:255',
        ]);

        WoundReport::create($validated);

        return redirect()->route('dashboard')->with('success', 'Laporan pengerjaan berhasil disimpan.');
    }

    public function destroy($id)
    {
        $report = WoundReport::findOrFail($id);
        $report->delete();

        return redirect()->route('dashboard')->with('success', 'Laporan pengerjaan berhasil dihapus.');
    }
}
