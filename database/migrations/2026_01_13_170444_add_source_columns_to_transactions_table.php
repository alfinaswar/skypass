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
        Schema::table('transactions', function (Blueprint $table) {
            // Menambahkan kolom baru setelah kolom 'type' agar rapi
            // Limit VARCHAR di-set ke 255 (maksimal standar) untuk fleksibilitas
            $table->string('source_type', 255)->nullable()->after('type'); // procurement, purchase, dll
            $table->string('source_name', 255)->nullable()->after('source_type'); // Nama Vendor / Lokasi IBT / Nopol Kanibal
            $table->string('proof_image_url', 255)->nullable()->after('source_name'); // Foto BAST / Nota
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropColumn(['source_type', 'source_name', 'proof_image_url']);
        });
    }
};