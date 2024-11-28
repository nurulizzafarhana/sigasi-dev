<?php

namespace App\Models\Barang;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Barang extends Model
{
    use HasFactory;
    protected $primaryKey = 'IDBarang';
    protected $table = 'barang';
    protected $guarded = [];
    public $timestamps = false;

    public function jenisBarang()
    {
        return $this->hasOne(JenisBarang::class, 'IDJenisBarang', 'IDJenisBarang');
    }
}
