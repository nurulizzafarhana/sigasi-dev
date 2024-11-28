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
        Schema::create('pengguna', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->integer('IDPengguna')->autoIncrement();
            $table->string('Nama', 20);
            $table->string('NomorKontak', 12);
            $table->string('Satuan', 10)->nullable();
            $table->integer('IDPosko')->nullable();
            $table->dateTime('deleted_at')->nullable();
            $table->integer('deleted_by')->nullable();

            $table->foreign('IDPosko')->references('IDPosko')->on('posko');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pengguna');
    }
};
