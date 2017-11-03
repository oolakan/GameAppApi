<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Otp extends Model
{

    protected $fillable = ['users_id', 'code'];
}
