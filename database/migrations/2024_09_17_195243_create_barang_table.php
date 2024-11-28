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
        Schema::create('barang', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->integer('IDBarang')->autoIncrement();
            $table->string('NamaBarang', 20);
            $table->integer('IDJenisBarang')->nullable();
            $table->float('HargaSatuan',0,2)->nullable();
            $table->dateTime('LastUpdateDate')->nullable();
            $table->dateTime('deleted_at')->nullable();
            $table->integer('deleted_by')->nullable();

            $table->foreign('IDJenisBarang')->references('IDJenisBarang')->on('jenis_barang');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('barang');
    }
};
