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
        Schema::create('instructor_ratings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('instructor_id')->constrained('instructors')->onDelete('cascade'); // Links to instructors table
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // Links to users table
            $table->integer('rating')->comment('Rating between 1 to 5');
            $table->text('review')->nullable();
            $table->boolean('is_accept')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('instructor_ratings');
    }
};