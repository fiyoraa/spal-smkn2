<?php

namespace App\Services;

use App\Repositories\EquipmentRepository;
use App\Repositories\BorrowingRepository;
use App\Repositories\TransactionRepository;
use App\Repositories\FineRepository;
use Carbon\Carbon;
use Exception;

class BorrowingService
{
    protected $equipmentRepo;
    protected $borrowingRepo;
    protected $transactionRepo;
    protected $fineRepo;

    public function __construct(
        EquipmentRepository $equipmentRepo,
        BorrowingRepository $borrowingRepo,
        TransactionRepository $transactionRepo,
        FineRepository $fineRepo
    ) {
        $this->equipmentRepo = $equipmentRepo;
        $this->borrowingRepo = $borrowingRepo;
        $this->transactionRepo = $transactionRepo;
        $this->fineRepo = $fineRepo;
    }

    public function requestBorrow(int $userId, int $equipmentId, int $quantity, string $reason, string $dateStart, string $dateEnd)
    {
        $equipment = $this->equipmentRepo->findById($equipmentId);
        if (!$equipment) {
            throw new Exception("Alat tidak ditemukan.");
        }

        if ($equipment->status !== 'Tersedia') {
            throw new Exception("Alat sedang tidak tersedia (status: {$equipment->status}).");
        }

        return $this->borrowingRepo->create([
            'user_id' => $userId,
            'equipment_id' => $equipmentId,
            'quantity' => $quantity,
            'reason' => $reason,
            'status' => 'Pending',
            'date_requested' => Carbon::now()->toDateString(),
            'date_start' => $dateStart,
            'date_end' => $dateEnd,
        ]);
    }

    public function approveBorrow(int $requestId, int $laboranId)
    {
        $request = $this->borrowingRepo->findById($requestId);
        if (!$request) {
            throw new Exception("Permintaan peminjaman tidak ditemukan.");
        }

        if ($request->status !== 'Pending') {
            throw new Exception("Permintaan peminjaman sudah diproses sebelumnya.");
        }

        $this->borrowingRepo->updateStatus($requestId, 'Approved');
        $this->equipmentRepo->updateStatus($request->equipment_id, 'Dipinjam');

        $transactionCode = 'TRX-' . strtoupper(uniqid());

        $transaction = $this->transactionRepo->create([
            'borrowing_request_id' => $requestId,
            'user_id' => $request->user_id,
            'equipment_id' => $request->equipment_id,
            'transaction_code' => $transactionCode,
            'date_out' => Carbon::now(),
            'date_due' => Carbon::parse($request->date_end),
            'date_returned' => null,
            'status' => 'Dipinjam',
        ]);

        $this->transactionRepo->createConditionRecord([
            'transaction_id' => $transaction->id,
            'condition_type' => 'Out',
            'status_condition' => 'Baik',
            'photo_path' => null,
            'notes' => 'Serah terima awal alat terverifikasi.',
            'reporter_id' => $laboranId,
        ]);

        return $transaction;
    }

    public function returnEquipment(string $transactionCode, string $statusCondition, ?string $notes, int $laboranId, ?string $actualReturnDate = null)
    {
        $transaction = $this->transactionRepo->findByCode($transactionCode);
        if (!$transaction) {
            throw new Exception("Transaksi peminjaman tidak ditemukan.");
        }

        if ($transaction->status === 'Kembali') {
            throw new Exception("Peralatan pada transaksi ini sudah dikembalikan.");
        }

        $returnDate = $actualReturnDate ? Carbon::parse($actualReturnDate) : Carbon::now();
        $dueDate = Carbon::parse($transaction->date_due);

        $delayDays = 0;
        if ($returnDate->greaterThan($dueDate)) {
            $delayDays = abs($returnDate->diffInDays($dueDate));
        }

        $fineAmount = 0;
        if ($delayDays > 0) {
            $fineAmount = $delayDays * 10000;
        }

        if ($fineAmount > 0) {
            $this->fineRepo->create([
                'transaction_id' => $transaction->id,
                'amount' => $fineAmount,
                'status' => 'Pending',
                'paid_at' => null,
                'notes' => "Denda keterlambatan selama {$delayDays} hari.",
            ]);
        }

        $this->transactionRepo->createConditionRecord([
            'transaction_id' => $transaction->id,
            'condition_type' => 'In',
            'status_condition' => $statusCondition,
            'photo_path' => null,
            'notes' => $notes ?? 'Catatan verifikasi pengembalian.',
            'reporter_id' => $laboranId,
        ]);

        $this->transactionRepo->updateStatusAndReturnDate($transaction->id, 'Kembali', $returnDate->toDateTimeString());
        $this->equipmentRepo->updateStatus($transaction->equipment_id, 'Tersedia');

        return [
            'transaction' => $transaction,
            'fine_amount' => $fineAmount,
            'delay_days' => $delayDays
        ];
    }
}
