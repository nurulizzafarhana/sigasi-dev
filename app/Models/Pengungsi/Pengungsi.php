<?php

namespace App\Models\Pengungsi;

use App\Models\Penduduk\Penduduk;
use App\Models\Posko\Posko;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pengungsi extends Model
{
    use HasFactory;
    protected $primaryKey = 'IDPengungsi';
    protected $table = 'pengungsi';
    protected $guarded = [];
    public $timestamps = false;

    public function penduduk()
    {
        return $this->hasOne(Penduduk::class,'IDPenduduk','IDPenduduk');
    }

    public function posko()
    {
        return $this->hasOne(Posko::class,'IDPosko','IDPosko');
    }
}
