<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('survey_responses', function (Blueprint $table) {

            $table->text('address')->nullable()->after('name');

            $table->string('contact_details')->nullable()->after('address');

            $table->string('gender')->nullable()->after('contact_details');

            $table->integer('age')->nullable()->after('gender');

            $table->string('registration_number')->nullable()->after('age');

            $table->date('survey_date')->nullable()->after('registration_number');

            $table->json('answers')->nullable()->after('message');
        });
    }

    public function down(): void
    {
        Schema::table('survey_responses', function (Blueprint $table) {

            $table->dropColumn([
                'address',
                'contact_details',
                'gender',
                'age',
                'registration_number',
                'survey_date',
                'answers',
            ]);
        });
    }
};
