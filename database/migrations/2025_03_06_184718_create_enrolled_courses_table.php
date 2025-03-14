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
        Schema::create('enrolled_courses', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id'); // المستخدم الأساسي
            $table->unsignedBigInteger('course_id'); // الكورس
            $table->unsignedBigInteger('assigned_by')->nullable(); // المستخدم الذي قام بالتسجيل (إذا كان ولي أمر)
            $table->enum('status', ['on_basket','pending', 'approved'])->default('on_basket'); // حالة التسجيل
            $table->decimal('amount_paid', 10, 2)->default(0.00); // المبلغ المدفوع
            $table->decimal('remaining_amount', 10, 2)->default(0.00); // المبلغ المتبقي
            $table->enum('payment_status', ['unpaid', 'partially_paid', 'paid'])->default('unpaid'); // حالة الدفع
            $table->timestamps();

            // الربط بالعلاقات
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('course_id')->references('id')->on('courses')->onDelete('cascade');
            $table->foreign('assigned_by')->references('id')->on('users')->onDelete('set null'); // في حالة كان التسجيل بواسطة ولي أمر
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('enrolled_courses');
    }
};