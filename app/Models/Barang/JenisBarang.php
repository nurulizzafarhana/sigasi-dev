<?php

namespace App\Models\Barang;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JenisBarang extends Model
{
    use HasFactory;
    protected $primaryKey = 'IDJenisBarang';
    protected $table = 'jenis_barang';
    protected $guarded = [];
    public $timestamps = false;
}
