<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\WoundReport;
use App\Models\Operator;

class WoundReportSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        $op1 = Operator::where('name', 'AIDA NUR AVIDA')->first();
        $op2 = Operator::where('name', 'AGUNG PRAYOGI')->first();
        $op3 = Operator::where('name', 'AFISYA PARADISA')->first();

        WoundReport::create([
            'tanggal' => '2026-05-21',
            'pengerjaan' => 'Packing',
            'jenis_produk' => 'Hydrocolloid',
            'shift' => 'Shift 1',
            'hasil' => 350,
            'produk_yang_dikerjakan' => 'Hydrocolloid Dressing 10x10',
            'satuan' => 'Pcs',
            'keterangan' => 'Produksi berjalan lancar dengan hasil optimal.',
            'vendor' => 'KWI',
            'operator' => 'AIDA NUR AVIDA',
            'operator_id' => $op1 ? $op1->id : null,
            'status' => 'approved',
        ]);

        WoundReport::create([
            'tanggal' => '2026-05-22',
            'pengerjaan' => 'Cutting',
            'jenis_produk' => 'Foam',
            'shift' => 'Shift 2',
            'hasil' => 500,
            'produk_yang_dikerjakan' => 'Foam Border 15x15',
            'satuan' => 'Pcs',
            'keterangan' => 'Hasil potong presisi tinggi, tidak ada reject.',
            'vendor' => 'MJA',
            'operator' => 'AGUNG PRAYOGI',
            'operator_id' => $op2 ? $op2->id : null,
            'status' => 'pending',
        ]);

        WoundReport::create([
            'tanggal' => '2026-05-22',
            'pengerjaan' => 'Assembly',
            'jenis_produk' => 'Alginate',
            'shift' => 'Shift 3',
            'hasil' => 200,
            'produk_yang_dikerjakan' => 'Alginate Dressing 5x5',
            'satuan' => 'Pcs',
            'keterangan' => 'Kemasan steril tertutup rapi.',
            'vendor' => 'AA',
            'operator' => 'AFISYA PARADISA',
            'operator_id' => $op3 ? $op3->id : null,
            'status' => 'pending',
        ]);
    }
}
