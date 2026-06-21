<?php

namespace App\Repositories;

use App\Models\Equipment;
use Illuminate\Database\Eloquent\Collection;

class EquipmentRepository
{
    public function findById(int $id): ?Equipment
    {
        return Equipment::find($id);
    }

    public function findByCode(string $code): ?Equipment
    {
        return Equipment::where('code', $code)->first();
    }

    public function getAll(): Collection
    {
        return Equipment::all();
    }

    public function updateStatus(int $id, string $status): bool
    {
        $equipment = Equipment::find($id);
        if ($equipment) {
            $equipment->status = $status;
            return $equipment->save();
        }
        return false;
    }

    public function save(Equipment $equipment): bool
    {
        return $equipment->save();
    }

    public function delete(Equipment $equipment): bool
    {
        return $equipment->delete();
    }
}

