<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('page_section_media', function (Blueprint $table) {
            if (! Schema::hasColumn('page_section_media', 'title')) {
                $table->string('title')->nullable()->after('youtube_url');
            }
            if (! Schema::hasColumn('page_section_media', 'description')) {
                $table->text('description')->nullable()->after('title');
            }
        });
    }

    public function down(): void
    {
        Schema::table('page_section_media', function (Blueprint $table) {
            if (Schema::hasColumn('page_section_media', 'description')) {
                $table->dropColumn('description');
            }
            if (Schema::hasColumn('page_section_media', 'title')) {
                $table->dropColumn('title');
            }
        });
    }
};
