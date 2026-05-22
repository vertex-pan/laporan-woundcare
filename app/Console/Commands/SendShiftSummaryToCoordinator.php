<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use App\Models\Operator;
use App\Models\WoundReport;
use App\Services\WablasService;
use Carbon\Carbon;

#[Signature('app:send-shift-summary {--shift= : Force send summary for a specific shift} {--date= : Force send summary for a specific work date}')]
#[Description('Compile shift reporting summary (who reported and who did not) and send WhatsApp notification to all Coordinators')]
class SendShiftSummaryToCoordinator extends Command
{
    /**
     * Execute the console command.
     */
    public function handle()
    {
        $forceShift = $this->option('shift');
        $forceDate = $this->option('date');

        $shiftsToCheck = [];
        $targetDate = null;

        if ($forceShift) {
            $shiftsToCheck = [$forceShift];
            $targetDate = $forceDate ?: Carbon::now('Asia/Jakarta')->format('Y-m-d');
            $this->info("Force mode active. Target shift: " . implode(', ', $shiftsToCheck) . ", Target date: " . $targetDate);
        } else {
            // Auto mode based on current time (runs exactly when each shift window closes)
            $currentTimeStr = Carbon::now('Asia/Jakarta')->format('H:i');
            
            // Shift deadlines:
            // Shift 1 window: 12:00 - 15:00. Summary at 15:00.
            // Lembur Shift 1 window: 16:00 - 19:00. Summary at 19:00.
            // Shift 2 window: 20:00 - 23:00. Summary at 23:00.
            // Shift 3 & Lembur Shift 2 window: 04:00 - 07:00 (next day). Summary at 07:00.
            
            if ($currentTimeStr === '15:00') {
                $shiftsToCheck = ['Shift 1'];
                $targetDate = Carbon::now('Asia/Jakarta')->format('Y-m-d');
            } elseif ($currentTimeStr === '19:00') {
                $shiftsToCheck = ['Lembur Shift 1'];
                $targetDate = Carbon::now('Asia/Jakarta')->format('Y-m-d');
            } elseif ($currentTimeStr === '23:00') {
                $shiftsToCheck = ['Shift 2'];
                $targetDate = Carbon::now('Asia/Jakarta')->format('Y-m-d');
            } elseif ($currentTimeStr === '07:00') {
                $shiftsToCheck = ['Shift 3', 'Lembur Shift 2'];
                // Since they report the next morning, their work date was yesterday
                $targetDate = Carbon::now('Asia/Jakarta')->subDay()->format('Y-m-d');
            }
        }

        if (empty($shiftsToCheck)) {
            $this->info("No shifts scheduled for summaries at this time (" . Carbon::now('Asia/Jakarta')->format('H:i') . ").");
            return 0;
        }

        $wablas = new WablasService();
        
        // Retrieve all active Coordinators with a WhatsApp number
        $coordinators = Operator::where('role', 'coordinator')
            ->whereNotNull('whatsapp')
            ->where('whatsapp', '!=', '')
            ->get();

        if ($coordinators->isEmpty()) {
            $this->info("No coordinators found with an active WhatsApp number.");
            return 0;
        }

        foreach ($shiftsToCheck as $shiftName) {
            $this->info("Compiling summary for Shift: {$shiftName}, Date: {$targetDate}");

            // 1. Fetch all reports submitted for this shift and date
            $reports = WoundReport::where('tanggal', $targetDate)
                ->where('shift', $shiftName)
                ->get();

            // Group reports by operator name and vendor
            $groupedReports = [];
            foreach ($reports as $report) {
                $key = $report->operator . ' [' . $report->vendor . ']';
                $formattedHasil = number_format($report->hasil, 0, ',', '.');
                $groupedReports[$key][] = "*{$report->produk_yang_dikerjakan}* ({$formattedHasil} {$report->satuan})";
            }

            $alreadyReported = [];
            foreach ($groupedReports as $operatorLabel => $details) {
                $alreadyReported[] = "- {$operatorLabel}: " . implode(', ', $details);
            }

            // 2. Format the audit message
            $formattedDate = Carbon::parse($targetDate)->translatedFormat('d F Y');
            
            $message = "📢 *REKAP LAPORAN MASUK - {$shiftName}*\n"
                     . "📅 *Tanggal Kerja:* {$formattedDate}\n"
                     . "⏰ *Status:* Batas waktu pengisian telah berakhir.\n\n";

            if (!empty($alreadyReported)) {
                $message .= "✅ *SUDAH LAPORAN (" . count($alreadyReported) . " Orang):*\n"
                          . implode("\n", $alreadyReported) . "\n\n";
            } else {
                $message .= "✅ *SUDAH LAPORAN:*\n_- Tidak ada staff yang melapor -_\n\n";
            }

            $message .= "Dasbor Laporan Woundcare: https://woundcare.fun";

            // 4. Send message to all coordinators
            foreach ($coordinators as $coordinator) {
                $this->info("Sending summary to Coordinator: {$coordinator->name} ({$coordinator->whatsapp})...");
                $result = $wablas->send($coordinator->whatsapp, $message);
                
                if ($result['status'] ?? false) {
                    $this->info("Sent successfully!");
                } else {
                    $this->error("Failed to send: " . ($result['reason'] ?? 'Unknown API error'));
                }
            }
        }

        $this->info("Finished compiling and sending shift summaries.");
        return 0;
    }
}
