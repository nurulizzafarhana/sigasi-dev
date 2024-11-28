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
        Schema::create('donatur', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->integer('IDDonatur')->autoIncrement();
            $table->string('NamaPerusahaan', 50);
            $table->string('Alamat', 255);
            $table->string('NomorKontak', 16);
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
        Schema::dropIfExists('donatur');
    }
};
