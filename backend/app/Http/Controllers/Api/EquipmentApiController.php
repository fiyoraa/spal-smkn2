<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Repositories\EquipmentRepository;
use App\Services\QrCodeService;
use Illuminate\Http\JsonResponse;

class EquipmentApiController extends Controller
{
    protected $equipmentRepo;
    protected $qrCodeService;

    public function __construct(EquipmentRepository $equipmentRepo, QrCodeService $qrCodeService)
    {
        $this->equipmentRepo = $equipmentRepo;
        $this->qrCodeService = $qrCodeService;
    }

    public function index(): JsonResponse
    {
        $equipment = $this->equipmentRepo->getAll();
        return response()->json([
            'status' => 'success',
            'data' => $equipment
        ]);
    }

    public function show(string $idOrCode): JsonResponse
    {
        $equipment = null;
        if (is_numeric($idOrCode)) {
            $equipment = $this->equipmentRepo->findById((int)$idOrCode);
        }
        
        if (!$equipment) {
            $equipment = $this->equipmentRepo->findByCode($idOrCode);
        }

        if (!$equipment) {
            return response()->json([
                'status' => 'error',
                'message' => 'Alat tidak ditemukan'
            ], 404);
        }

        $qrCode = $this->qrCodeService->generateCode($equipment->category, $equipment->id);

        return response()->json([
            'status' => 'success',
            'data' => $equipment,
            'qr_code' => $qrCode
        ]);
    }

    public function store(\Illuminate\Http\Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'required|in:laptop,proyektor,kamera,tripod',
            'location' => 'required|string|max:255',
        ]);

        $equipment = new \App\Models\Equipment();
        $equipment->name = $request->input('name');
        $equipment->category = $request->input('category');
        $equipment->location = $request->input('location');
        $equipment->status = 'Tersedia';
        $equipment->code = 'TEMP-' . microtime(true) . '-' . rand(1000, 9999); // temporary unique placeholder code

        $this->equipmentRepo->save($equipment);

        // Auto generate code and qr code
        $code = $this->qrCodeService->generateCode($equipment->category, $equipment->id);
        $qrImage = $this->qrCodeService->generateQrImage($code);

        $equipment->code = $code;
        $equipment->qr_code = $qrImage;
        $this->equipmentRepo->save($equipment);

        return response()->json([
            'status' => 'success',
            'data' => $equipment
        ], 201);
    }

    public function update(\Illuminate\Http\Request $request, int $id): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'required|in:laptop,proyektor,kamera,tripod',
            'location' => 'required|string|max:255',
            'status' => 'required|in:Tersedia,Dipinjam,Perawatan',
        ]);

        $equipment = $this->equipmentRepo->findById($id);
        if (!$equipment) {
            return response()->json([
                'status' => 'error',
                'message' => 'Alat tidak ditemukan'
            ], 404);
        }

        $categoryChanged = ($equipment->category !== $request->input('category'));

        $equipment->name = $request->input('name');
        $equipment->category = $request->input('category');
        $equipment->location = $request->input('location');
        $equipment->status = $request->input('status');

        if ($categoryChanged) {
            $code = $this->qrCodeService->generateCode($equipment->category, $equipment->id);
            $qrImage = $this->qrCodeService->generateQrImage($code);
            $equipment->code = $code;
            $equipment->qr_code = $qrImage;
        }

        $this->equipmentRepo->save($equipment);

        return response()->json([
            'status' => 'success',
            'data' => $equipment
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        $equipment = $this->equipmentRepo->findById($id);
        if (!$equipment) {
            return response()->json([
                'status' => 'error',
                'message' => 'Alat tidak ditemukan'
            ], 404);
        }

        $this->equipmentRepo->delete($equipment);

        return response()->json([
            'status' => 'success',
            'message' => 'Alat berhasil dihapus'
        ]);
    }
}
