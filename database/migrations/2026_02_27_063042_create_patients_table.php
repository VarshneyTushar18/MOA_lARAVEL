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
        Schema::create('patients', function (Blueprint $table) {
            $table->id();
            $table->date('date')->nullable();
            $table->string('uhid_no')->nullable();
            $table->string('adhaar_no',16)->nullable();
            $table->string('name');
            $table->integer('age')->nullable();
            $table->string('sex')->nullable();
            $table->string('visit_follow_up')->nullable();
            $table->text('address')->nullable();
            $table->text('diagnosis')->nullable();
            $table->text('investigation')->nullable();
            $table->text('medicines')->nullable();
            $table->text('h_o_tb_other_investigations')->nullable();
            $table->string('tb_gold')->nullable();
            $table->string('montoux_test')->nullable();
            $table->string('cbc_esr')->nullable();
            $table->string('xray_cect_hrct')->nullable();
            $table->string('gene_xpert')->nullable();
            $table->string('usg_wa_ct_scan')->nullable();
            $table->string('cd4_cd8')->nullable();
            $table->string('ige')->nullable();
            $table->string('vit_d')->nullable();
            $table->string('lft')->nullable();
            $table->string('rft')->nullable();
            $table->string('il2')->nullable();
            $table->text('contact_details')->nullable();
            $table->string('ltbi_qs_10')->nullable();
            $table->string('ltbi_qs_09')->nullable();
            $table->string('refer')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('patients');
    }
};
