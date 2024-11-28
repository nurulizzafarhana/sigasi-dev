<?php

namespace App\Models\Donatur;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Donatur extends Model
{
    use HasFactory;
    protected $primaryKey = 'IDDonatur';
    protected $table = 'donatur';
    protected $guarded = [];
    public $timestamps = false;
}
