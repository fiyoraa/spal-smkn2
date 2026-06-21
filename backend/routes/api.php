<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\EquipmentApiController;
use App\Http\Controllers\Api\BorrowingApiController;
use App\Http\Controllers\Api\AuthApiController;

// Auth Routes
Route::post('/login', [AuthApiController::class, 'login']);
Route::post('/register', [AuthApiController::class, 'register']);

// Equipment Routes
Route::get('/equipment', [EquipmentApiController::class, 'index']);
Route::get('/equipment/{id}', [EquipmentApiController::class, 'show']);
Route::post('/equipment', [EquipmentApiController::class, 'store']);
Route::put('/equipment/{id}', [EquipmentApiController::class, 'update']);
Route::delete('/equipment/{id}', [EquipmentApiController::class, 'destroy']);

// Borrowing & Transaction GET Routes
Route::get('/borrowings', [BorrowingApiController::class, 'index']);
Route::get('/transactions', [BorrowingApiController::class, 'transactions']);

// Borrowing Workflow Routes
Route::post('/borrowing/request', [BorrowingApiController::class, 'request']);
Route::post('/borrowing/approve/{id}', [BorrowingApiController::class, 'approve']);
Route::post('/borrowing/reject/{id}', [BorrowingApiController::class, 'reject']);
Route::post('/borrowing/return', [BorrowingApiController::class, 'return']);

// Blacklist Route
Route::get('/blacklist', function () {
    $blacklist = \App\Models\BlacklistManager::with('user')->get()->map(function ($item) {
        return [
            'id' => 'BL-' . $item->id,
            'user_id' => $item->user_id,
            'name' => $item->user->name ?? 'Unknown',
            'className' => $item->user->role === 'Siswa' ? 'Siswa SMKN 2' : 'Guru SMKN 2',
            'reason' => $item->reason,
            'dateAdded' => \Carbon\Carbon::parse($item->date_added)->format('d M Y'),
        ];
    });
    return response()->json([
        'status' => 'success',
        'data' => $blacklist
    ]);
});
