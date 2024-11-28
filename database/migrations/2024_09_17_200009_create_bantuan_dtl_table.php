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
        Schema::create('bantuan_dtl', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->integer('IDBantuanDTL')->autoIncrement();
            $table->integer('IDBantuan');
            $table->integer('IDBarang');
            $table->integer('Jumlah')->nullable();
            $table->dateTime('deleted_at')->nullable();
            $table->integer('deleted_by')->nullable();

            $table->foreign('IDBantuan')->references('IDBantuan')->on('bantuan');
            $table->foreign('IDBarang')->references('IDBarang')->on('barang');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bantuan_dtl');
    }
};
