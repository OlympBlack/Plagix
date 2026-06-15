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
        Schema::create('collected_documents', function (Blueprint $table) {
            $table->id();

            $table->string('title');
            $table->string('author')->nullable();
            $table->string('university')->nullable();

            $table->date('published_at')->nullable();

            $table->longText('content')->nullable();

            $table->string('source_url', 1000);
            $table->string('hash')->unique();

            $table->foreignId('scraping_source_id')->constrained('scraping_sources')->onDelete('cascade');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('collected_documents');
    }
};
