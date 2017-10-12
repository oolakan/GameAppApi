<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class GameTransaction extends Model
{
    protected $fillable = [
        'game_no_played',
        'ticket_id',
        'serial_no',
        'amount_paid',
        'game_type_options_id',
        'users_id',
        'time_played',
        'payment_option'
    ];
}
