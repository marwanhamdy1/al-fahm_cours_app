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
        Schema::create('login_logs', function (Blueprint $table) {
        $table->id();
        $table->string('device_id')->nullable();
        $table->string('model')->nullable();
        $table->string('brand')->nullable();
        $table->string('manufacturer')->nullable();
        $table->string('os_version')->nullable();
        $table->string('sdk_version')->nullable();
        $table->string('system_name')->nullable();
        $table->string('device_name')->nullable();
        $table->string('ip_address')->nullable();
        $table->decimal('latitude', 10, 7)->nullable();
        $table->decimal('longitude', 10, 7)->nullable();
        $table->string('contact_info')->nullable();
        $table->enum('status',['success', 'failed'])->nullable();
        $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('login_logs');
    }
};