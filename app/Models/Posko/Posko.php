<?php

namespace App\Models\Posko;

use App\Models\Kebutuhan\Kebutuhan;
use App\Models\Pengguna\Pengguna;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Posko extends Model
{
    use HasFactory;
    protected $primaryKey = 'IDPosko';
    protected $table = 'posko';
    protected $guarded = [];
    public $timestamps = false;

    public function user()
    {
        return $this->hasOne(User::class,'id','Ketua');
    }

    public function kebutuhan()
    {
        return $this->hasMany(Kebutuhan::class,'IDPosko','IDPosko');
    }
}
