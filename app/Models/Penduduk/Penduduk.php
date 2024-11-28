<?php

namespace App\Models\Penduduk;

use App\Models\Kelompok\Kelompok;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Penduduk extends Model
{
    use HasFactory;
    protected $primaryKey = 'IDPenduduk';
    protected $table = 'penduduk';
    protected $guarded = [];
    public $timestamps = false;

    public function kelompok()
    {
        return $this->hasOne(Kelompok::class, 'IDKelompok', 'Kelompok');
    }
}
