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
        Schema::create('pengungsi', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->integer('IDPengungsi')->autoIncrement();
            $table->integer('IDPenduduk');
            $table->integer('IDPosko');
            $table->string('KondisiKhusus', 255)->nullable();
            $table->dateTime('LastUpdateDate')->nullable();
            $table->integer('LastUpdateBy')->nullable();
            $table->dateTime('deleted_at')->nullable();
            $table->integer('deleted_by')->nullable();

            $table->foreign('IDPenduduk')->references('IDPenduduk')->on('penduduk');
            $table->foreign('IDPosko')->references('IDPosko')->on('posko');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pengungsi');
    }
};
