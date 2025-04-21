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
       Schema::create('users', function (Blueprint $table) {
        $table->id();
        $table->string('first_name')->nullable();
        $table->string('last_name')->nullable();
        $table->string('username')->unique()->nullable();
        $table->string('identity_id')->unique()->nullable();
        $table->string('phone_number')->unique()->nullable();
        $table->string('email')->unique()->nullable();
        $table->string('password')->nullable();
        $table->string('image')->nullable();
        $table->string('color')->nullable();
        $table->string('verify_code')->default("1234");
        $table->enum('role', ['super_admin','admin','parent', 'child', 'individual','moderator'])->nullable();
        $table->date('date_of_birth')->nullable();
        $table->string('school_name')->nullable();
        $table->string('grade_name')->nullable();
        $table->string('educational_stage')->nullable();
        $table->string('neighborhood')->nullable();
        $table->unsignedBigInteger('parent_id')->nullable();
        $table->enum('parent_type', ['father', 'mother'])->nullable();
        $table->enum('child_type', ['male', 'female'])->nullable();
        $table->tinyInteger('status',)->default(1);
        $table->string('mother_name')->nullable();
        $table->string('mother_identity_id')->nullable();
        $table->float('points')->nullable();
        $table->float('balance')->nullable();
        $table->foreign('parent_id')->references('id')->on('users')->onDelete('cascade');
        $table->timestamps();
});


        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};