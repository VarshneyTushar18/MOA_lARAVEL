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
        Schema::create('page_sections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('page_id')->constrained()->onDelete('cascade');

            $table->string('section_key'); 
            $table->string('type')->default('single'); 
            // single | banner | personal

            $table->unsignedBigInteger('parent_id')->nullable();
            // used for grouping (like multiple banners)

            $table->string('title')->nullable();
            $table->text('description')->nullable();
            $table->string('image')->nullable();

            $table->integer('sort_order')->default(0);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('page_sections');
    }
};
