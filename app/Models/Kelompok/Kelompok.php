<?php

namespace App\Models\Kelompok;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Kelompok extends Model
{
    use HasFactory;
    protected $primaryKey = 'IDKelompok';
    protected $table = 'kelompok';
    protected $guarded = [];
    public $timestamps = false;
}
