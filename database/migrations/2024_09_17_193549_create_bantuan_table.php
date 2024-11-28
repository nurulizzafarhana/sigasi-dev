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
        Schema::create('bantuan', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->integer('IDBantuan')->autoIncrement();
            $table->integer('IDDonatur');
            $table->dateTime('TanggalBantuan');
            $table->dateTime('LastUpdateDate')->nullable();
            $table->integer('LastUpdateBy')->nullable();
            $table->dateTime('deleted_at')->nullable();
            $table->integer('deleted_by')->nullable();

            $table->foreign('IDDonatur')->references('IDDonatur')->on('donatur');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bantuan');
    }
};
