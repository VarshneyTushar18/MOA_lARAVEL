<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $factSheetExists = DB::table('pages')->where('slug', 'factsheet')->exists();

        if (! $factSheetExists) {
            DB::table('pages')
                ->where('slug', 'facesheet')
                ->update([
                    'slug' => 'factsheet',
                    'title' => 'Fact Sheet',
                ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $faceSheetExists = DB::table('pages')->where('slug', 'facesheet')->exists();

        if (! $faceSheetExists) {
            DB::table('pages')
                ->where('slug', 'factsheet')
                ->update([
                    'slug' => 'facesheet',
                    'title' => 'Face sheet',
                ]);
        }
    }
};
