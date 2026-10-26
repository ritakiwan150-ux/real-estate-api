<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('broker_license_number', 50)->nullable()->after('password');
            $table->enum('broker_verification_status', ['not_applicable', 'pending', 'verified', 'rejected'])
                ->default('not_applicable')
                ->after('broker_license_number');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['broker_license_number', 'broker_verification_status']);
        });
    }
};
