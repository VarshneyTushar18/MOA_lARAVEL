<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('page_section_images', function (Blueprint $table) {
            if (! Schema::hasColumn('page_section_images', 'sort_order')) {
                $table->unsignedInteger('sort_order')->default(0)->after('image');
            }
        });
    }

    public function down(): void
    {
        Schema::table('page_section_images', function (Blueprint $table) {
            if (Schema::hasColumn('page_section_images', 'sort_order')) {
                $table->dropColumn('sort_order');
            }
        });
    }
};
