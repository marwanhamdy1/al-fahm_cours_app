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
        Schema::create('courses', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->decimal('price', 8, 2); // Example: 99.99
            $table->integer('earnings_point')->default(0); // Reward points
            $table->string('address'); // Course location
            $table->date('start_date'); // start date
            $table->date('end_date'); // end date
            $table->integer('max_people')->default(0); // Max people who can enroll
            $table->integer('signed_people')->default(0); // Already signed-up users
            $table->integer('age_range'); // Store 18
            $table->integer('session_count')->default(0); // Total sessions
            $table->unsignedBigInteger('category_id');
            $table->unsignedBigInteger('instructor_id');
            $table->boolean('active')->default(false); // Total sessions
            $table->enum('type',['offline','online'])->default('offline'); // Total sessions
            $table->timestamps();
            $table->foreign('category_id')->references('id')->on('categories')->onDelete('cascade');
            $table->foreign('instructor_id')->references('id')->on('instructors')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('courses');
    }
};