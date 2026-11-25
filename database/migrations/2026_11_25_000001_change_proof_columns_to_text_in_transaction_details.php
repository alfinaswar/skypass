<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('transaction_details', function (Blueprint $table) {
            $table->text('proof_image_before_url')->nullable()->change();
            $table->text('proof_image_url')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('transaction_details', function (Blueprint $table) {
            $table->string('proof_image_before_url')->nullable()->change();
            $table->string('proof_image_url')->nullable()->change();
        });
    }
};
