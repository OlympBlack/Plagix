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
        Schema::table('collected_documents', function (Blueprint $table) {
            $table->string('publication_year', 4)->nullable()->after('university');
            $table->longText('description')->nullable()->after('content');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('collected_documents', function (Blueprint $table) {
            $table->dropColumn(['publication_year', 'description']);
        });
    }
};
