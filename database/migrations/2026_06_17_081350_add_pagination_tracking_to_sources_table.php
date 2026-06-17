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
        Schema::table('scraping_sources', function (Blueprint $table) {
            $table->string('scraping_status')->default('idle')->after('is_active');
            $table->integer('scraping_progress')->default(0)->after('scraping_status');
            $table->integer('current_page')->default(0)->after('scraping_progress');
            $table->integer('total_pages')->default(0)->after('current_page');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('scraping_sources', function (Blueprint $table) {
            $table->dropColumn(['scraping_status', 'scraping_progress', 'current_page', 'total_pages']);
        });
    }
};
