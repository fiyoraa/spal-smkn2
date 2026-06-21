<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Equipment;
use App\Models\BorrowingRequest;
use App\Models\Transaction;
use App\Models\ConditionRecord;
use App\Models\Fine;
use Carbon\Carbon;

class TransactionSeeder extends Seeder
{
    public function run(): void
    {
        // Fetch all students, teachers and laboran
        $siswas = User::where('role', 'Siswa')->get();
        $gurus = User::where('role', 'Guru')->get();
        $laboran = User::where('role', 'Laboran')->first();

        if ($siswas->isEmpty() || $gurus->isEmpty() || !$laboran) {
            $this->command->error('Users not found. Run UserSeeder first.');
            return;
        }

        // Fetch all equipment
        $equipments = Equipment::all();
        if ($equipments->count() < 55) {
            $this->command->error('Not enough equipment seeded. Run EquipmentSeeder first.');
            return;
        }

        $borrowers = $siswas->concat($gurus);
        $today = Carbon::parse('2026-06-19'); // Set base date matching current system date

        // --- 1. Seed 20 Returned On-Time Transactions ---
        for ($i = 1; $i <= 20; $i++) {
            $borrower = $borrowers->get($i % $borrowers->count());
            $equipment = $equipments->get($i - 1);

            $dateOut = $today->copy()->subDays(20 + $i);
            $dateDue = $dateOut->copy()->addDays(5);
            $dateReturned = $dateOut->copy()->addDays(rand(1, 4)); // Returned within 1-4 days (on-time)

            $request = BorrowingRequest::create([
                'user_id' => $borrower->id,
                'equipment_id' => $equipment->id,
                'quantity' => 1,
                'reason' => 'Praktikum rutin komputer kelas ' . ($i % 3 + 10),
                'status' => 'Approved',
                'date_requested' => $dateOut->copy()->subDays(1),
                'date_start' => $dateOut,
                'date_end' => $dateDue,
            ]);

            $transaction = Transaction::create([
                'borrowing_request_id' => $request->id,
                'user_id' => $borrower->id,
                'equipment_id' => $equipment->id,
                'transaction_code' => 'TRX-RET-' . str_pad($i, 3, '0', STR_PAD_LEFT),
                'date_out' => $dateOut,
                'date_due' => $dateDue,
                'date_returned' => $dateReturned,
                'status' => 'Kembali',
            ]);

            ConditionRecord::create([
                'transaction_id' => $transaction->id,
                'condition_type' => 'Out',
                'status_condition' => 'Baik',
                'photo_path' => null,
                'notes' => 'Diserahkan dalam keadaan baik dan lengkap.',
                'reporter_id' => $laboran->id,
            ]);

            ConditionRecord::create([
                'transaction_id' => $transaction->id,
                'condition_type' => 'In',
                'status_condition' => 'Baik',
                'photo_path' => null,
                'notes' => 'Dikembalikan tepat waktu, kondisi lengkap.',
                'reporter_id' => $laboran->id,
            ]);
        }

        // --- 2. Seed 5 Returned Late Transactions (Denda Paid) ---
        for ($i = 1; $i <= 5; $i++) {
            $borrower = $borrowers->get(($i + 20) % $borrowers->count());
            $equipment = $equipments->get(20 + $i - 1);

            $delayDays = rand(2, 5); // 2 to 5 days late
            $dateOut = $today->copy()->subDays(15 + $i);
            $dateDue = $dateOut->copy()->addDays(3);
            $dateReturned = $dateDue->copy()->addDays($delayDays); // Returned late

            $request = BorrowingRequest::create([
                'user_id' => $borrower->id,
                'equipment_id' => $equipment->id,
                'quantity' => 1,
                'reason' => 'Proyek kelas laboratorium komputer.',
                'status' => 'Approved',
                'date_requested' => $dateOut->copy()->subDays(1),
                'date_start' => $dateOut,
                'date_end' => $dateDue,
            ]);

            $transaction = Transaction::create([
                'borrowing_request_id' => $request->id,
                'user_id' => $borrower->id,
                'equipment_id' => $equipment->id,
                'transaction_code' => 'TRX-RLAT-' . str_pad($i, 3, '0', STR_PAD_LEFT),
                'date_out' => $dateOut,
                'date_due' => $dateDue,
                'date_returned' => $dateReturned,
                'status' => 'Kembali',
            ]);

            ConditionRecord::create([
                'transaction_id' => $transaction->id,
                'condition_type' => 'Out',
                'status_condition' => 'Baik',
                'photo_path' => null,
                'notes' => 'Peralatan dicek lengkap sebelum dipinjam.',
                'reporter_id' => $laboran->id,
            ]);

            ConditionRecord::create([
                'transaction_id' => $transaction->id,
                'condition_type' => 'In',
                'status_condition' => 'Baik',
                'photo_path' => null,
                'notes' => 'Dikembalikan terlambat ' . $delayDays . ' hari.',
                'reporter_id' => $laboran->id,
            ]);

            Fine::create([
                'transaction_id' => $transaction->id,
                'amount' => $delayDays * 10000,
                'status' => 'Paid',
                'paid_at' => $dateReturned,
                'notes' => 'Denda keterlambatan ' . $delayDays . ' hari telah dibayar lunas.',
            ]);
        }

        // --- 3. Seed 5 Active On-Time Loans ---
        for ($i = 1; $i <= 5; $i++) {
            $borrower = $borrowers->get(($i + 25) % $borrowers->count());
            $equipment = $equipments->get(25 + $i - 1);

            $dateOut = $today->copy()->subDays(1); // Borrowed yesterday
            $dateDue = $today->copy()->addDays(2); // Due in 2 days

            $request = BorrowingRequest::create([
                'user_id' => $borrower->id,
                'equipment_id' => $equipment->id,
                'quantity' => 1,
                'reason' => 'Tugas mandiri pemrograman dasar.',
                'status' => 'Approved',
                'date_requested' => $dateOut->copy()->subDays(1),
                'date_start' => $dateOut,
                'date_end' => $dateDue,
            ]);

            $transaction = Transaction::create([
                'borrowing_request_id' => $request->id,
                'user_id' => $borrower->id,
                'equipment_id' => $equipment->id,
                'transaction_code' => 'TRX-ACT-' . str_pad($i, 3, '0', STR_PAD_LEFT),
                'date_out' => $dateOut,
                'date_due' => $dateDue,
                'date_returned' => null,
                'status' => 'Dipinjam',
            ]);

            $equipment->update(['status' => 'Dipinjam']);

            ConditionRecord::create([
                'transaction_id' => $transaction->id,
                'condition_type' => 'Out',
                'status_condition' => 'Sangat Baik',
                'photo_path' => null,
                'notes' => 'Diserahkan dalam keadaan sangat baik.',
                'reporter_id' => $laboran->id,
            ]);
        }

        // --- 4. Seed 5 Active Late Loans (Status 'Terlambat') ---
        for ($i = 1; $i <= 5; $i++) {
            $borrower = $borrowers->get(($i + 30) % $borrowers->count());
            $equipment = $equipments->get(30 + $i - 1);

            $delayDays = rand(2, 5); // 2 to 5 days late
            $dateOut = $today->copy()->subDays(7); // Started 7 days ago
            $dateDue = $today->copy()->subDays($delayDays); // Due $delayDays ago

            $request = BorrowingRequest::create([
                'user_id' => $borrower->id,
                'equipment_id' => $equipment->id,
                'quantity' => 1,
                'reason' => 'Praktikum jaringan komputer lokal.',
                'status' => 'Approved',
                'date_requested' => $dateOut->copy()->subDays(1),
                'date_start' => $dateOut,
                'date_end' => $dateDue,
            ]);

            $transaction = Transaction::create([
                'borrowing_request_id' => $request->id,
                'user_id' => $borrower->id,
                'equipment_id' => $equipment->id,
                'transaction_code' => 'TRX-LAT-' . str_pad($i, 3, '0', STR_PAD_LEFT),
                'date_out' => $dateOut,
                'date_due' => $dateDue,
                'date_returned' => null,
                'status' => 'Terlambat',
            ]);

            $equipment->update(['status' => 'Dipinjam']);

            ConditionRecord::create([
                'transaction_id' => $transaction->id,
                'condition_type' => 'Out',
                'status_condition' => 'Baik',
                'photo_path' => null,
                'notes' => 'Verifikasi awal alat lengkap.',
                'reporter_id' => $laboran->id,
            ]);

            Fine::create([
                'transaction_id' => $transaction->id,
                'amount' => $delayDays * 10000,
                'status' => 'Pending',
                'paid_at' => null,
                'notes' => 'Keterlambatan pengembalian selama ' . $delayDays . ' hari.',
            ]);
        }
    }
}
