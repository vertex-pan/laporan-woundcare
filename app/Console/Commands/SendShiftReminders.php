<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use App\Models\Operator;
use App\Models\WoundReport;
use App\Services\WablasService;
use Carbon\Carbon;

#[Signature('app:send-shift-reminders {--shift= : Force send reminders for a specific shift} {--date= : Force send reminders for a specific work date}')]
#[Description('Send WhatsApp reminders to operators who have not submitted their reports 30 minutes before the shift closes')]
class SendShiftReminders extends Command
{
    /**
     * Execute the console command.
     */
    public function handle()
    {
        $forceShift = $this->option('shift');
        $forceDate = $this->option('date');

        $shiftsToRemind = [];
        $targetDate = null;

        if ($forceShift) {
            $shiftsToRemind = [$forceShift];
            $targetDate = $forceDate ?: Carbon::now('Asia/Jakarta')->format('Y-m-d');
            $this->info("Force mode active. Target shift: " . implode(', ', $shiftsToRemind) . ", Target date: " . $targetDate);
        } else {
            // Auto mode based on current time
            $currentTimeStr = Carbon::now('Asia/Jakarta')->format('H:i');
            
            // Check matching window (30 mins before closing)
            // Shift 1 window: 12:00 - 15:00. Closes at 15:00. Reminder at 14:30.
            // Lembur Shift 1 window: 16:00 - 19:00. Closes at 19:00. Reminder at 18:30.
            // Shift 2 window: 20:00 - 23:00. Closes at 23:00. Reminder at 22:30.
            // Shift 3 & Lembur Shift 2 window: 04:00 - 07:00 (next day). Closes at 07:00. Reminder at 06:30.
            
            if ($currentTimeStr === '14:30') {
                $shiftsToRemind = ['Shift 1'];
                $targetDate = Carbon::now('Asia/Jakarta')->format('Y-m-d');
            } elseif ($currentTimeStr === '18:30') {
                $shiftsToRemind = ['Lembur Shift 1'];
                $targetDate = Carbon::now('Asia/Jakarta')->format('Y-m-d');
            } elseif ($currentTimeStr === '22:30') {
                $shiftsToRemind = ['Shift 2'];
                $targetDate = Carbon::now('Asia/Jakarta')->format('Y-m-d');
            } elseif ($currentTimeStr === '06:30') {
                $shiftsToRemind = ['Shift 3', 'Lembur Shift 2'];
                // Since they report the next morning, their work date was yesterday
                $targetDate = Carbon::now('Asia/Jakarta')->subDay()->format('Y-m-d');
            }
        }

        if (empty($shiftsToRemind)) {
            $this->info("No shifts scheduled for reminders at this time (" . Carbon::now('Asia/Jakarta')->format('H:i') . ").");
            return 0;
        }

        $wablas = new WablasService();
        $totalSent = 0;

        foreach ($shiftsToRemind as $shiftName) {
            $this->info("Processing reminders for Shift: {$shiftName}, Date: {$targetDate}");

            // 1. Get IDs of operators who ALREADY submitted any report for this target date
            $submittedOperatorIds = WoundReport::where('tanggal', $targetDate)
                ->pluck('operator_id')
                ->filter()
                ->unique()
                ->toArray();

            // 2. Get all karyawan operators who have NOT submitted a report
            $missingOperators = Operator::where('role', 'karyawan')
                ->whereNotIn('id', $submittedOperatorIds)
                ->whereNotNull('whatsapp')
                ->where('whatsapp', '!=', '')
                ->get();

            if ($missingOperators->isEmpty()) {
                $this->info("All operators have submitted reports for {$shiftName} on {$targetDate}.");
                continue;
            }

            foreach ($missingOperators as $operator) {
                $message = "Halo *{$operator->name}*,\n\n"
                         . "Mengingatkan bahwa batas akhir pengisian laporan untuk *{$shiftName}* (Tanggal Kerja: " . Carbon::parse($targetDate)->translatedFormat('d F Y') . ") akan ditutup dalam 30 menit lagi.\n\n"
                         . "Mohon segera kirimkan laporan hasil produksi Anda di dashboard:\n"
                         . "https://woundcare.fun";

                $this->info("Sending reminder to {$operator->name} ({$operator->whatsapp})...");
                
                $result = $wablas->send($operator->whatsapp, $message);
                if ($result['status'] ?? false) {
                    $totalSent++;
                } else {
                    $this->error("Failed to send message to {$operator->name}: " . ($result['reason'] ?? 'Unknown API error'));
                }
            }
        }

        $this->info("Done. Total reminders sent: {$totalSent}");
        return 0;
    }
}
