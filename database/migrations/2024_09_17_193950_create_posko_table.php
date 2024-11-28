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
        Schema::create('posko', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->integer('IDPosko')->autoIncrement();
            $table->integer('Ketua');
            $table->string('Lokasi', 50);
            $table->string('Masalah', 255)->nullable();
            $table->string('SolusiMasalah', 255)->nullable();
            $table->dateTime('deleted_at')->nullable();
            $table->integer('deleted_by')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('posko');
    }
};
