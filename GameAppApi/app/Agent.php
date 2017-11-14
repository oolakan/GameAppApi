<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Agent extends Model
{
    protected $fillable = [
        'users_id',
        'ticket_id',
        'merchants_id',
        'name',
        'credit_balance',
        'merchants_id',
    ];

}
