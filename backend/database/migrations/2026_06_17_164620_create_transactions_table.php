<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('borrowing_request_id')->nullable()->constrained('borrowing_requests')->onDelete('set null');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('equipment_id')->constrained('equipment')->onDelete('cascade');
            $table->string('transaction_code')->unique();
            $table->dateTime('date_out');
            $table->dateTime('date_due');
            $table->dateTime('date_returned')->nullable();
            $table->enum('status', ['Dipinjam', 'Kembali', 'Terlambat'])->default('Dipinjam');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
