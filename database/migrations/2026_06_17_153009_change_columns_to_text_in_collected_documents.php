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
            $table->text('title')->change();
            $table->text('author')->nullable()->change();
            $table->text('university')->nullable()->change();
            $table->text('source_url')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('collected_documents', function (Blueprint $table) {
            $table->string('title')->change();
            $table->string('author')->nullable()->change();
            $table->string('university')->nullable()->change();
            $table->string('source_url')->nullable()->change();
        });
    }
};
