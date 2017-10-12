<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CreditHistory extends Model
{
    protected $fillable = ['amount', 'activity',
        'users_id'];
}
