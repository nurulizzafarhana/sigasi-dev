<?php

namespace App\Models\Kebutuhan;

use App\Models\Barang\Barang;
use App\Models\Posko\Posko;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Kebutuhan extends Model
{
    use HasFactory;
    protected $primaryKey = 'IDKebutuhan';
    protected $table = 'kebutuhan';
    protected $guarded = [];
    public $timestamps = false;

    public function posko()
    {
        return $this->hasOne(Posko::class,'IDPosko','IDPosko');
    }

    public function barang()
    {
        return $this->hasOne(Barang::class,'IDBarang','IDBarang');
    }
}
