<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('transaction_details', function (Blueprint $table) {
            $table->string('proof_image_before_url')->nullable()->after('qty');
            $table->string('proof_image_url')->nullable()->after('proof_image_before_url');
        });
    }

    public function down(): void
    {
        Schema::table('transaction_details', function (Blueprint $table) {
            $table->dropColumn(['proof_image_before_url', 'proof_image_url']);
        });
    }
};
