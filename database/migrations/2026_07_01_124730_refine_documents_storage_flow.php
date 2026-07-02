<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->dropIndex('documents_tenant_id_user_id_category_index');
        });

        Schema::table('documents', function (Blueprint $table) {
            $table->renameColumn('category', 'folder');
        });

        DB::table('documents')->where('folder', 'contract')->update(['folder' => 'contratos']);
        DB::table('documents')->where('folder', 'payslip')->update(['folder' => 'nominas']);
        DB::table('documents')->where('folder', 'certificate')->update(['folder' => 'otros']);
        DB::table('documents')->where('folder', 'other')->update(['folder' => 'otros']);

        Schema::table('documents', function (Blueprint $table) {
            $table->foreignId('uploaded_by_user_id')->nullable()->after('user_id')->constrained('users')->nullOnDelete();
            $table->string('disk')->default('documents')->after('description');
            $table->string('original_filename')->nullable()->after('file_path');
            $table->index(['tenant_id', 'user_id', 'folder']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->dropIndex('documents_tenant_id_user_id_folder_index');
            $table->dropConstrainedForeignId('uploaded_by_user_id');
            $table->dropColumn(['disk', 'original_filename']);
        });

        DB::table('documents')->where('folder', 'contratos')->update(['folder' => 'contract']);
        DB::table('documents')->where('folder', 'nominas')->update(['folder' => 'payslip']);
        DB::table('documents')->where('folder', 'otros')->update(['folder' => 'other']);

        Schema::table('documents', function (Blueprint $table) {
            $table->renameColumn('folder', 'category');
            $table->index(['tenant_id', 'user_id', 'category']);
        });
    }
};
