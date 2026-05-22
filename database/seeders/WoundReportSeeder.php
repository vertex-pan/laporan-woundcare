<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use App\Models\WoundReport;

class WoundReportSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        WoundReport::create([
            'patient_name' => 'Budi Santoso',
            'medical_record_number' => 'RM-2026-0842',
            'wound_type' => 'Diabetic Foot Ulcer',
            'stage' => 'Stage III',
            'length_cm' => 4.5,
            'width_cm' => 3.2,
            'depth_cm' => 0.8,
            'exudate' => 'Moderate',
            'tissue_type' => 'Slough & Granulation',
            'pain_score' => 4,
            'treatment_applied' => 'Hydrocolloid dressing with silver alginate, offloading shoe applied.',
            'evaluation_date' => '2026-05-18',
            'status' => 'Improving',
        ]);

        WoundReport::create([
            'patient_name' => 'Siti Rahma',
            'medical_record_number' => 'RM-2026-0119',
            'wound_type' => 'Pressure Injury',
            'stage' => 'Stage IV',
            'length_cm' => 6.0,
            'width_cm' => 5.5,
            'depth_cm' => 2.1,
            'exudate' => 'Copious',
            'tissue_type' => 'Necrotic',
            'pain_score' => 6,
            'treatment_applied' => 'Surgical debridement performed. Negative Pressure Wound Therapy (NPWT) initiated.',
            'evaluation_date' => '2026-05-20',
            'status' => 'Stable',
        ]);

        WoundReport::create([
            'patient_name' => 'Ahmad Hidayat',
            'medical_record_number' => 'RM-2026-0955',
            'wound_type' => 'Surgical Wound Dehiscence',
            'stage' => 'Unstageable',
            'length_cm' => 8.2,
            'width_cm' => 1.5,
            'depth_cm' => 0.5,
            'exudate' => 'Minimal',
            'tissue_type' => 'Granulation',
            'pain_score' => 3,
            'treatment_applied' => 'Alginate packing, secondary dressing with foam, clean technique.',
            'evaluation_date' => '2026-05-21',
            'status' => 'Improving',
        ]);

        WoundReport::create([
            'patient_name' => 'Dewi Lestari',
            'medical_record_number' => 'RM-2026-0312',
            'wound_type' => 'Venous Leg Ulcer',
            'stage' => 'Stage II',
            'length_cm' => 3.0,
            'width_cm' => 2.8,
            'depth_cm' => 0.2,
            'exudate' => 'Minimal',
            'tissue_type' => 'Epithelializing',
            'pain_score' => 2,
            'treatment_applied' => 'Compression therapy 4-layer system, zinc-based barrier cream to wound margins.',
            'evaluation_date' => '2026-05-15',
            'status' => 'Healed',
        ]);

        WoundReport::create([
            'patient_name' => 'Eko Prasetyo',
            'medical_record_number' => 'RM-2026-0774',
            'wound_type' => 'Diabetic Foot Ulcer',
            'stage' => 'Stage II',
            'length_cm' => 2.1,
            'width_cm' => 1.8,
            'depth_cm' => 0.3,
            'exudate' => 'None',
            'tissue_type' => 'Epithelializing',
            'pain_score' => 1,
            'treatment_applied' => 'Hydrogel, non-adherent silicone dressing.',
            'evaluation_date' => '2026-05-22',
            'status' => 'Improving',
        ]);
    }
}
