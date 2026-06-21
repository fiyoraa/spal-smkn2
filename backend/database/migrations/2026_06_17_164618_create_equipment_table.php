<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('equipment', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->enum('category', ['laptop', 'proyektor', 'kamera', 'tripod']);
            $table->enum('status', ['Tersedia', 'Dipinjam', 'Perawatan'])->default('Tersedia');
            $table->string('location');
            $table->text('qr_code')->nullable(); // Base64 QR code image
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('equipment');
    }
};
