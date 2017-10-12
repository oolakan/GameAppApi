<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Winning extends Model
{
    protected $fillable = [
        'game_no',
        'winning_date',
        'winning_time',
        'game_type_options_id',
        'users_id'
    ];
}
