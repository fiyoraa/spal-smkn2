<?php

namespace App\Services;

use App\Repositories\TransactionRepository;
use App\Repositories\NotificationGateway;
use Carbon\Carbon;

class NotificationService
{
    protected $transactionRepo;
    protected $notificationGateway;

    public function __construct(
        TransactionRepository $transactionRepo,
        NotificationGateway $notificationGateway
    ) {
        $this->transactionRepo = $transactionRepo;
        $this->notificationGateway = $notificationGateway;
    }

    public function processDailyReminders(?string $checkDate = null): array
    {
        $today = $checkDate ? Carbon::parse($checkDate) : Carbon::now();
        $activeLoans = $this->transactionRepo->getActiveLoans();
        $results = [
            'reminders_sent' => 0,
            'warnings_sent' => 0,
        ];

        foreach ($activeLoans as $loan) {
            $user = $loan->user;
            $equipment = $loan->equipment;
            $dueDate = Carbon::parse($loan->date_due);

            if ($today->copy()->addDay()->isSameDay($dueDate)) {
                $message = "Halo {$user->name}, pengingat H-1 untuk pengembalian alat [{$equipment->name} ({$equipment->code})] yang jatuh tempo pada {$dueDate->toDateString()}. Harap dikembalikan tepat waktu.";
                
                $this->notificationGateway->sendEmail($user->email, "Pengingat H-1 Pengembalian Alat", $message);
                $this->notificationGateway->sendWhatsApp("081234567890", $message);

                $results['reminders_sent']++;
            }
            elseif ($today->greaterThan($dueDate) && !$today->isSameDay($dueDate)) {
                $delayDays = abs($today->diffInDays($dueDate));
                $fineAmount = $delayDays * 10000;

                $message = "PENTING: Peminjaman alat [{$equipment->name} ({$equipment->code})] oleh {$user->name} dinyatakan TERLAMBAT selama {$delayDays} hari. Denda saat ini: Rp " . number_format($fineAmount, 0, ',', '.') . ". Harap segera mengembalikan alat.";

                $this->notificationGateway->sendEmail($user->email, "PERINGATAN: Keterlambatan Pengembalian Alat", $message);
                $this->notificationGateway->sendWhatsApp("081234567890", $message);

                if ($loan->status !== 'Terlambat') {
                    $loan->status = 'Terlambat';
                    $this->transactionRepo->save($loan);
                }

                $results['warnings_sent']++;
            }
        }

        return $results;
    }
}
