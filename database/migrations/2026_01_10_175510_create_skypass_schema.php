<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // --- 1. TABEL SYSTEM LARAVEL (MANDATORY) ---
        
        // Tabel Users
        Schema::create('users', function (Blueprint $table) {
            $table->id(); 
            $table->string('name', 100);
            $table->string('username', 50)->unique();
            $table->string('password');
            $table->string('role', 20); // admin, supervisor
            $table->rememberToken();
            $table->timestamps();
        });

        // Tabel Sessions (Ini yang menyebabkan Error Anda)
        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });

        // Tabel Cache (Opsional tapi bagus untuk performa)
        Schema::create('cache', function (Blueprint $table) {
            $table->string('key')->primary();
            $table->mediumText('value');
            $table->integer('expiration');
        });

        // --- 2. TABEL APLIKASI SKYPASS ---

        // Tabel Vehicles (Armada)
        Schema::create('vehicles', function (Blueprint $table) {
            $table->id();
            $table->string('fleet_number', 20)->unique(); // Cth: KNA 12
            $table->timestamps();
        });

        // Tabel Spareparts
        Schema::create('spareparts', function (Blueprint $table) {
            $table->id();
            $table->string('part_number', 50)->nullable()->unique(); 
            $table->string('description', 255); 
            $table->string('category', 20); 
            $table->string('unit', 20); 
            $table->string('rack_location', 20); 
            $table->integer('min_stock')->default(0);
            $table->integer('current_stock')->default(0);
            $table->integer('limit_high_usage')->default(10);
            $table->integer('limit_medium_usage')->default(5);
            $table->string('image_url', 255)->nullable(); 
            $table->timestamps();
        });

        // Tabel Transactions (Header)
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_no', 50)->unique(); 
            $table->string('type', 10); // IN, OUT
            $table->date('date');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('vehicle_id')->nullable()->constrained('vehicles')->onDelete('set null');
            $table->string('requester_name', 100)->nullable(); 
            $table->text('notes')->nullable(); 
            $table->timestamps();
        });

        // Tabel Transaction Details (Rincian Barang)
        Schema::create('transaction_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('transaction_id')->constrained('transactions')->onDelete('cascade');
            $table->foreignId('sparepart_id')->constrained('spareparts')->onDelete('cascade');
            $table->integer('qty'); 
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('transaction_details');
        Schema::dropIfExists('transactions');
        Schema::dropIfExists('spareparts');
        Schema::dropIfExists('vehicles');
        Schema::dropIfExists('cache');
        Schema::dropIfExists('sessions');
        Schema::dropIfExists('users');
        Schema::enableForeignKeyConstraints();
    }
};