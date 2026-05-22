<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Pengerjaan;
use App\Models\JenisProduk;
use App\Models\Satuan;

class MasterDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Seed Pengerjaans
        $pengerjaans = [
            "OPERATOR",
            "PACKING",
            "ASSEMBLING",
            "GULUNG",
            "GULUNG + PACKING",
            "POUCH + SEAL + PACKING",
            "POUCH + SEAL",
            "POTONG",
            "GULUNG BB SAMBUNGAN",
            "SPLIT BB",
            "PLONG",
            "SEAL",
            "POUCH + PACK",
            "MEMBERSIHKAN BB",
        ];

        foreach ($pengerjaans as $name) {
            Pengerjaan::firstOrCreate(['name' => $name]);
        }

        // 2. Seed Jenis Produks
        $jenisProduks = [
            "ULTRAFIX",
            "PLESTERIN ROLL",
            "ISOPLAST",
            "ISOPORE",
            "ISOFIX",
            "ECG PAPER",
            "PLESTERIN",
            "STERIL POUCH",
            "EKAPLAST",
            "DERMAFIX",
            "HOTMELT",
            "SLITING",
            "POUCH",
            "FLEXO",
            "RIWEND",
            "K-ONE",
            "SUPERFIX",
            "C-DOT",
            "SUPERFIX SENSITIVE",
            "SILICONE TAPE",
            "LIPO FOAM",
            "MEDIFLEX",
            "DISPOSABLE MOUTHPIECE SPIROMETER",
        ];

        foreach ($jenisProduks as $name) {
            JenisProduk::firstOrCreate(['name' => $name]);
        }

        // 3. Seed Satuans
        $satuans = [
            "PCS",
            "ROLL",
            "BOX",
            "DOS",
            "TAX",
            "M",
            "RENCENG",
            "AMPLOP",
            "PAC",
        ];

        foreach ($satuans as $name) {
            Satuan::firstOrCreate(['name' => $name]);
        }
    }
}
