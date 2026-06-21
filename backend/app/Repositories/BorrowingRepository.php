<?php

namespace App\Repositories;

use App\Models\BorrowingRequest;
use Illuminate\Database\Eloquent\Collection;

class BorrowingRepository
{
    public function findById(int $id): ?BorrowingRequest
    {
        return BorrowingRequest::find($id);
    }

    public function create(array $data): BorrowingRequest
    {
        return BorrowingRequest::create($data);
    }

    public function updateStatus(int $id, string $status): bool
    {
        $request = BorrowingRequest::find($id);
        if ($request) {
            $request->status = $status;
            return $request->save();
        }
        return false;
    }

    public function getAll(): Collection
    {
        return BorrowingRequest::with(['user', 'equipment'])->get();
    }
}
