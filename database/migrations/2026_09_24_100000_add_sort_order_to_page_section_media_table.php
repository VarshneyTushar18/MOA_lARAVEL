<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('page_section_media', function (Blueprint $table) {
            if (! Schema::hasColumn('page_section_media', 'sort_order')) {
                $table->unsignedInteger('sort_order')->default(0)->after('description');
            }
        });

        if (Schema::hasColumn('page_section_media', 'sort_order')) {
            DB::table('page_section_media')
                ->orderBy('page_section_id')
                ->orderBy('id')
                ->get()
                ->groupBy('page_section_id')
                ->each(function ($items) {
                    $order = 1;
                    foreach ($items as $item) {
                        DB::table('page_section_media')
                            ->where('id', $item->id)
                            ->update(['sort_order' => $order++]);
                    }
                });
        }
    }

    public function down(): void
    {
        Schema::table('page_section_media', function (Blueprint $table) {
            if (Schema::hasColumn('page_section_media', 'sort_order')) {
                $table->dropColumn('sort_order');
            }
        });
    }
};
