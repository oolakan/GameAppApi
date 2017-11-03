<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Game extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'game_status',
        'game_names_id',
        'game_quaters_id',
        'start_time',
        'stop_time',
        'draw_time',
        'users_id',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     * Game name
     */
    public function game_name(){
        return $this->hasOne(GameName::class, 'id', 'game_names_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     * Game qquater
     */
    public function game_quater() {
        return $this->hasOne(GameQuater::class, 'id', 'game_quaters_id');
    }

}
