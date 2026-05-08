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
        Schema::create('cure_patients', function (Blueprint $table) {
            $table->id();

            $table->string('ltbi_no');       // 00035
            $table->string('cc_no')->nullable();
            $table->string('tr_no')->nullable();

            $table->string('access_code')->unique();  // 0035049
            $table->string('file_path');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cure_patients');
    }
};
