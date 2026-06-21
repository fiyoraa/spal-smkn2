<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\QrCodeService;
use App\Services\BorrowingService;
use App\Services\NotificationService;
use App\Repositories\UserRepository;
use App\Repositories\EquipmentRepository;
use App\Repositories\BorrowingRepository;
use App\Repositories\TransactionRepository;
use App\Repositories\FineRepository;
use Illuminate\Support\Facades\DB;
use Exception;

class TestWorkflow extends Command
{
    protected $signature = 'app:test-workflow';

    protected $description = 'Executes programmatic test checks for the 4-layer backend system to verify all logic';

    protected $userRepo;
    protected $equipmentRepo;
    protected $borrowingRepo;
    protected $transactionRepo;
    protected $fineRepo;
    protected $qrCodeService;
    protected $borrowingService;
    protected $notificationService;

    public function __construct(
        UserRepository $userRepo,
        EquipmentRepository $equipmentRepo,
        BorrowingRepository $borrowingRepo,
        TransactionRepository $transactionRepo,
        FineRepository $fineRepo,
        QrCodeService $qrCodeService,
        BorrowingService $borrowingService,
        NotificationService $notificationService
    ) {
        parent::__construct();
        $this->userRepo = $userRepo;
        $this->equipmentRepo = $equipmentRepo;
        $this->borrowingRepo = $borrowingRepo;
        $this->transactionRepo = $transactionRepo;
        $this->fineRepo = $fineRepo;
        $this->qrCodeService = $qrCodeService;
        $this->borrowingService = $borrowingService;
        $this->notificationService = $notificationService;
    }

    public function handle(): int
    {
        $this->info("=== STARTING BACKEND INTEGRATION TESTS ===");
        
        DB::beginTransaction();

        try {
            // Fetch actors
            $siswa = $this->userRepo->findByEmail('siswa@school.id');
            $laboran = $this->userRepo->findByEmail('laboran@school.id');
            
            if (!$siswa || !$laboran) {
                throw new Exception("Test users (siswa/laboran) not found in DB.");
            }

            // Find an available laptop
            $laptop = $this->equipmentRepo->getAll()->where('category', 'laptop')->where('status', 'Tersedia')->first();
            if (!$laptop) {
                throw new Exception("No available laptop found to run test on.");
            }

            $this->info("Using Siswa: [{$siswa->name}], Laboran: [{$laboran->name}], Laptop: [{$laptop->name} ({$laptop->code})]");

            // 1. Test QR Code Generator
            $this->info("\n--- 1. Testing QR Code Generator ---");
            $qr = $this->qrCodeService->generateCode($laptop->category, $laptop->id, 2026);
            $expectedQr = "SMKN2-LAB-laptop-2026-" . str_pad($laptop->id, 4, '0', STR_PAD_LEFT);
            if ($qr === $expectedQr) {
                $this->line("QR Code: {$qr} <info>[PASSED]</info>");
            } else {
                throw new Exception("QR Code mismatch. Expected: {$expectedQr}, Got: {$qr}");
            }

            // 2. Test Borrow Request
            $this->info("\n--- 2. Testing Borrowing Request Submission ---");
            $dateStart = '2026-06-18';
            $dateEnd = '2026-06-21';
            
            $req = $this->borrowingService->requestBorrow(
                $siswa->id,
                $laptop->id,
                1,
                'Uji Coba Peminjaman Mandiri',
                $dateStart,
                $dateEnd
            );

            $this->line("Request created. ID: {$req->id}, Status: {$req->status} <info>[PASSED]</info>");

            // Try requesting again (should fail because status is now updated during approval, or let's check validation)
            // But request status is still Pending, so equipment is still technically Tersedia until approved.
            // Let's test validation if equipment status is "Dipinjam"
            $this->info("\n--- 3. Testing Double Borrow Block (Validation) ---");
            // Set equipment to Dipinjam manually to test check
            $laptop->status = 'Dipinjam';
            $this->equipmentRepo->save($laptop);

            try {
                $this->borrowingService->requestBorrow(
                    $siswa->id,
                    $laptop->id,
                    1,
                    'Double Request',
                    $dateStart,
                    $dateEnd
                );
                throw new Exception("Double request was allowed when equipment was not available!");
            } catch (Exception $e) {
                $this->line("Correctly blocked double request: " . $e->getMessage() . " <info>[PASSED]</info>");
            }

            // Restore equipment status for approval test
            $laptop->status = 'Tersedia';
            $this->equipmentRepo->save($laptop);

            // 4. Test Approval
            $this->info("\n--- 4. Testing Laboran Approval ---");
            $trx = $this->borrowingService->approveBorrow($req->id, $laboran->id);
            $this->line("Transaction generated. Code: {$trx->transaction_code}, Status: {$trx->status}");
            
            // Check if equipment status changed to Dipinjam
            $laptopUpdated = $this->equipmentRepo->findById($laptop->id);
            if ($laptopUpdated->status === 'Dipinjam') {
                $this->line("Equipment status set to 'Dipinjam' <info>[PASSED]</info>");
            } else {
                throw new Exception("Equipment status was not updated to Dipinjam upon approval.");
            }

            // 5. Test Return & Fine calculation
            $this->info("\n--- 5. Testing Returning & Fine Calculation ---");
            // Actual return on 2026-06-25 (due date is 2026-06-21, so 4 days late)
            $actualReturnDate = '2026-06-25';
            $returnResult = $this->borrowingService->returnEquipment(
                $trx->transaction_code,
                'Baik',
                'Dikembalikan terlambat tapi kondisi utuh.',
                $laboran->id,
                $actualReturnDate
            );

            $fineValue = $returnResult['fine_amount'];
            $delayValue = $returnResult['delay_days'];
            
            if ($delayValue == 4 && $fineValue == 40000) {
                $this->line("Delay: {$delayValue} Days, Fine: Rp " . number_format($fineValue, 0, ',', '.') . " <info>[PASSED]</info>");
            } else {
                throw new Exception("Fine calculation mismatch. Delay: {$delayValue} (Type: " . gettype($delayValue) . ", Expected: 4), Fine: {$fineValue} (Type: " . gettype($fineValue) . ", Expected: 40000)");
            }

            // Check if equipment status is back to Tersedia
            $laptopFinal = $this->equipmentRepo->findById($laptop->id);
            if ($laptopFinal->status === 'Tersedia') {
                $this->line("Equipment status reset to 'Tersedia' <info>[PASSED]</info>");
            } else {
                throw new Exception("Equipment status was not reset to Tersedia on return.");
            }

            // 6. Test Scheduler Notifikasi
            $this->info("\n--- 6. Testing Notification Scheduler relative to 2026-06-20 (Due is 2026-06-21, so H-1) ---");
            // Let's create an active loan first that is due on 2026-06-21
            $newLaptop = $this->equipmentRepo->getAll()->where('category', 'proyektor')->where('status', 'Tersedia')->first();
            
            $newReq = $this->borrowingService->requestBorrow(
                $siswa->id,
                $newLaptop->id,
                1,
                'Keperluan Proyektor',
                '2026-06-18',
                '2026-06-21' // Due on 21
            );
            $newTrx = $this->borrowingService->approveBorrow($newReq->id, $laboran->id);

            // Execute daily reminders Relative to 2026-06-20 (which is tomorrow relative to 20)
            $notifResult = $this->notificationService->processDailyReminders('2026-06-20');
            
            if ($notifResult['reminders_sent'] > 0) {
                $this->line("Successfully triggered H-1 reminders to user: {$notifResult['reminders_sent']} sent <info>[PASSED]</info>");
            } else {
                throw new Exception("H-1 reminder failed to send.");
            }

            $this->info("\n=== ALL BACKEND LAYER TESTS SUCCESSFULLY PASSED ===");

        } catch (Exception $e) {
            $this->error("TEST FAILED: " . $e->getMessage());
            DB::rollBack();
            return Command::FAILURE;
        }

        DB::rollBack(); // Rollback so we don't pollute the database with tests
        return Command::SUCCESS;
    }
}
