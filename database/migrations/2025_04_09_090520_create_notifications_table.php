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
        Schema::create('notifications', function (Blueprint $table) {
             $table->id();
            $table->string('title');
            $table->text('body')->nullable();
            $table->string('type')->nullable(); // optional: 'system', 'user', etc
            $table->unsignedBigInteger('user_id')->nullable(); // optional: if notification belongs to a user
            $table->boolean('is_read')->default(false);
            $table->timestamps();

            // optional foreign key
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notification');
    }
};