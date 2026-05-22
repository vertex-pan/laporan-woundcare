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
        Schema::create('wound_reports', function (Blueprint $table) {
            $table->id();
            $table->date('tanggal');
            $table->string('pengerjaan');
            $table->string('jenis_produk');
            $table->string('shift');
            $table->integer('hasil');
            $table->string('produk_yang_dikerjakan');
            $table->string('satuan');
            $table->text('keterangan');
            $table->string('vendor');
            $table->string('operator');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wound_reports');
    }
};
