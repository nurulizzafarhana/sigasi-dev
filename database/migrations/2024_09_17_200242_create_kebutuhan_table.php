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
        Schema::create('kebutuhan', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->integer('IDKebutuhan')->autoIncrement();
            $table->integer('IDBarang');
            $table->integer('IDPosko');
            $table->integer('JumlahKebutuhan')->nullable();
            $table->integer('JumlahDiterima')->nullable();
            $table->dateTime('LastUpdateDate')->nullable();
            $table->integer('LastUpdateBy')->nullable();
            $table->dateTime('deleted_at')->nullable();
            $table->integer('deleted_by')->nullable();

            $table->foreign('IDPosko')->references('IDPosko')->on('posko');
            $table->foreign('IDBarang')->references('IDBarang')->on('barang');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kebutuhan');
    }
};
