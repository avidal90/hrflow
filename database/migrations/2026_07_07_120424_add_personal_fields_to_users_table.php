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
        Schema::table('users', function (Blueprint $table): void {
            $table->string('phone_personal')->nullable()->after('email');
            $table->string('phone_company')->nullable()->after('phone_personal');
            $table->date('birth_date')->nullable()->after('phone_company');
            $table->string('national_id')->nullable()->after('birth_date');
            $table->string('social_security_number')->nullable()->after('national_id');
            $table->string('birth_country')->nullable()->after('social_security_number');
            $table->string('address')->nullable()->after('birth_country');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropColumn([
                'phone_personal',
                'phone_company',
                'birth_date',
                'national_id',
                'social_security_number',
                'birth_country',
                'address',
            ]);
        });
    }
};
