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
        Schema::create('distribusi_bantuan', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->integer('IDDistribusiBantuan')->autoIncrement();
            $table->integer('IDPosko');
            $table->integer('IDBantuan');
            $table->dateTime('TanggalDistribusi');
            $table->text('Deskripsi')->nullable();
            $table->dateTime('deleted_at')->nullable();
            $table->integer('deleted_by')->nullable();

            $table->foreign('IDPosko')->references('IDPosko')->on('posko');
            $table->foreign('IDBantuan')->references('IDBantuan')->on('bantuan');
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
