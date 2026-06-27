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
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->string('tenant_id');
            $table->foreignId('user_id')->nullable()->unique()->constrained()->nullOnDelete();
            $table->foreignId('department_id')->constrained()->restrictOnDelete();
            $table->string('employee_code');
            $table->string('first_name');
            $table->string('last_name');
            $table->date('hire_date');
            $table->string('employment_status')->default('active');
            $table->string('job_title')->nullable();
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->index(['tenant_id', 'department_id']);
            $table->index(['tenant_id', 'employment_status']);
            $table->unique(['tenant_id', 'employee_code']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};
