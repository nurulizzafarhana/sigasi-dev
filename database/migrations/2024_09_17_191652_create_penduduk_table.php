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
        Schema::create('penduduk', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->integer('IDPenduduk')->autoIncrement();
            $table->string('KTP', 16);
            $table->string('Nama', 20);
            $table->string('Alamat', 50);
            $table->char('Desa', 20);
            $table->dateTime('TanggalLahir')->nullable();
            $table->boolean('JenisKelamin')->nullable();
            $table->integer('Kelompok')->nullable();
            $table->dateTime('LastUpdateDate')->nullable();
            $table->integer('LastUpdateBy')->nullable();
            $table->dateTime('deleted_at')->nullable();
            $table->integer('deleted_by')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('penduduk');
    }
};
