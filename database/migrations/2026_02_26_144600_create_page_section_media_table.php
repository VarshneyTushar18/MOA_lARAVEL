<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
{
    Schema::create('page_section_media', function (Blueprint $table) {
        $table->id();
        $table->foreignId('page_section_id')
              ->constrained('page_sections')
              ->onDelete('cascade');

        $table->string('type'); 
        // pdf | video | youtube

        $table->string('file_path')->nullable(); 
        // for pdf & local video

        $table->string('youtube_url')->nullable();

        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('page_section_media');
    }
};
