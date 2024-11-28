<?php

namespace App\Models\DistribusiBantuan;

use App\Models\Bantuan\Bantuan;
use App\Models\Posko\Posko;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DistribusiBantuan extends Model
{
    use HasFactory;
    protected $primaryKey = 'IDDistribusiBantuan';
    protected $table = 'distribusi_bantuan';
    protected $guarded = [];
    public $timestamps = false;

    public function posko()
    {
        return $this->hasOne(Posko::class,'IDPosko','IDPosko');
    }

    public function bantuan()
    {
        return $this->hasOne(Bantuan::class,'IDBantuan','IDBantuan');
    }
}
