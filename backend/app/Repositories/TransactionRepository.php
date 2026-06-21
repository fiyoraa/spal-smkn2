<?php

namespace App\Repositories;

use App\Models\Transaction;
use App\Models\ConditionRecord;
use Illuminate\Database\Eloquent\Collection;

class TransactionRepository
{
    public function findById(int $id): ?Transaction
    {
        return Transaction::with(['user', 'equipment', 'conditionRecords', 'fine'])->find($id);
    }

    public function findByCode(string $code): ?Transaction
    {
        return Transaction::with(['user', 'equipment', 'conditionRecords', 'fine'])
            ->where('transaction_code', $code)
            ->first();
    }

    public function create(array $data): Transaction
    {
        return Transaction::create($data);
    }

    public function updateStatusAndReturnDate(int $id, string $status, ?string $dateReturned): bool
    {
        $transaction = Transaction::find($id);
        if ($transaction) {
            $transaction->status = $status;
            $transaction->date_returned = $dateReturned;
            return $transaction->save();
        }
        return false;
    }

    public function getActiveLoans(): Collection
    {
        return Transaction::with(['user', 'equipment'])
            ->whereIn('status', ['Dipinjam', 'Terlambat'])
            ->get();
    }

    public function save(Transaction $transaction): bool
    {
        return $transaction->save();
    }

    public function createConditionRecord(array $data): ConditionRecord
    {
        return ConditionRecord::create($data);
    }
}
