<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\BorrowingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Exception;

class BorrowingApiController extends Controller
{
    protected $borrowingService;

    public function __construct(BorrowingService $borrowingService)
    {
        $this->borrowingService = $borrowingService;
    }

    public function index(Request $request): JsonResponse
    {
        $userId = $request->query('user_id');
        $status = $request->query('status');
        
        $query = \App\Models\BorrowingRequest::with(['user', 'equipment']);
        if ($userId) {
            $query->where('user_id', $userId);
        }
        if ($status) {
            $query->where('status', $status);
        }
        
        $requests = $query->orderBy('created_at', 'desc')->get()->map(function ($req) {
            return [
                'id' => 'PEM-' . $req->id,
                'dbId' => $req->id,
                'userId' => $req->user_id,
                'userName' => $req->user->name ?? 'Unknown',
                'className' => $req->user->role === 'Siswa' ? 'Siswa SMKN 2' : 'Guru SMKN 2',
                'itemName' => $req->equipment->name ?? 'Unknown',
                'equipmentId' => $req->equipment->code ?? $req->equipment_id,
                'reason' => $req->reason,
                'quantity' => $req->quantity,
                'dateReq' => \Carbon\Carbon::parse($req->date_requested)->format('d M Y'),
                'dateStart' => $req->date_start,
                'dateEnd' => $req->date_end,
                'status' => $req->status,
            ];
        });

        return response()->json([
            'status' => 'success',
            'data' => $requests
        ]);
    }

    public function transactions(Request $request): JsonResponse
    {
        $userId = $request->query('user_id');
        $status = $request->query('status');
        
        $query = \App\Models\Transaction::with(['user', 'equipment', 'fine']);
        if ($userId) {
            $query->where('user_id', $userId);
        }
        if ($status) {
            $query->where('status', $status);
        }
        
        $transactions = $query->orderBy('created_at', 'desc')->get()->map(function ($trx) {
            $delayDays = 0;
            $fineAmount = 0;
            if ($trx->fine) {
                $fineAmount = $trx->fine->amount;
            }
            
            return [
                'id' => $trx->transaction_code,
                'dbId' => $trx->id,
                'userId' => $trx->user_id,
                'userName' => $trx->user->name ?? 'Unknown',
                'className' => $trx->user->role === 'Siswa' ? 'Siswa SMKN 2' : 'Guru SMKN 2',
                'itemName' => $trx->equipment->name ?? 'Unknown',
                'equipmentCode' => $trx->equipment->code ?? '',
                'dateOut' => \Carbon\Carbon::parse($trx->date_out)->format('d M Y'),
                'dateDue' => \Carbon\Carbon::parse($trx->date_due)->format('d M Y'),
                'dateReturned' => $trx->date_returned ? \Carbon\Carbon::parse($trx->date_returned)->format('d M Y') : null,
                'status' => $trx->status,
                'fine' => $fineAmount > 0 ? 'Rp ' . number_format($fineAmount, 0, ',', '.') : '-',
            ];
        });

        return response()->json([
            'status' => 'success',
            'data' => $transactions
        ]);
    }

    public function request(Request $request): JsonResponse
    {
        $request->validate([
            'user_id' => 'required|integer',
            'equipment_id' => 'required|integer',
            'quantity' => 'required|integer|min:1',
            'reason' => 'required|string',
            'date_start' => 'required|date',
            'date_end' => 'required|date|after_or_equal:date_start',
        ]);

        try {
            $borrowRequest = $this->borrowingService->requestBorrow(
                $request->user_id,
                $request->equipment_id,
                $request->quantity,
                $request->reason,
                $request->date_start,
                $request->date_end
            );

            return response()->json([
                'status' => 'success',
                'message' => 'Pengajuan peminjaman berhasil dibuat.',
                'data' => $borrowRequest
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 400);
        }
    }

    public function approve(Request $request, int $requestId): JsonResponse
    {
        $request->validate([
            'laboran_id' => 'required|integer',
        ]);

        try {
            $transaction = $this->borrowingService->approveBorrow($requestId, $request->laboran_id);

            return response()->json([
                'status' => 'success',
                'message' => 'Peminjaman disetujui, transaksi aktif dibuat.',
                'data' => $transaction
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 400);
        }
    }

    public function reject(Request $request, int $requestId): JsonResponse
    {
        try {
            $borrowRequest = \App\Models\BorrowingRequest::find($requestId);
            if (!$borrowRequest) {
                throw new Exception("Permintaan peminjaman tidak ditemukan.");
            }
            if ($borrowRequest->status !== 'Pending') {
                throw new Exception("Permintaan peminjaman sudah diproses.");
            }
            $borrowRequest->status = 'Rejected';
            $borrowRequest->save();

            return response()->json([
                'status' => 'success',
                'message' => 'Pengajuan peminjaman ditolak.',
                'data' => $borrowRequest
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 400);
        }
    }

    public function return(Request $request): JsonResponse
    {
        $request->validate([
            'transaction_code' => 'required|string',
            'status_condition' => 'required|string|in:Sangat Baik,Baik,Cukup,Rusak Ringan,Rusak Berat',
            'notes' => 'nullable|string',
            'laboran_id' => 'required|integer',
            'actual_return_date' => 'nullable|date',
        ]);

        try {
            $result = $this->borrowingService->returnEquipment(
                $request->transaction_code,
                $request->status_condition,
                $request->notes,
                $request->laboran_id,
                $request->actual_return_date
            );

            return response()->json([
                'status' => 'success',
                'message' => 'Peralatan berhasil dikembalikan.',
                'data' => $result
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 400);
        }
    }
}
