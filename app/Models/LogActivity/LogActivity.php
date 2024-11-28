<?php

namespace App\Models\LogActivity;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LogActivity extends Model
{
    use HasFactory;
    protected $primaryKey = 'IDLogActivity';
    protected $table = 'log_activity';
    protected $guarded = [];

    public function user()
    {
        return $this->hasOne(User::class, 'id', 'IDUser');
    }
}
