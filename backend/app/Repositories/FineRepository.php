<?php

namespace App\Repositories;

use App\Models\Fine;

class FineRepository
{
    public function create(array $data): Fine
    {
        return Fine::create($data);
    }

    public function findByTransactionId(int $transactionId): ?Fine
    {
        return Fine::where('transaction_id', $transactionId)->first();
    }
}
