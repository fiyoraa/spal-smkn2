<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('condition_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('transaction_id')->constrained('transactions')->onDelete('cascade');
            $table->enum('condition_type', ['Out', 'In']);
            $table->enum('status_condition', ['Sangat Baik', 'Baik', 'Cukup', 'Rusak Ringan', 'Rusak Berat']);
            $table->string('photo_path')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('reporter_id')->constrained('users')->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('condition_records');
    }
};
