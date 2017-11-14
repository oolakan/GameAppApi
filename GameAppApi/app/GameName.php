<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class GameName extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'days_id', 'start_time', 'stop_time', 'draw_time',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     * game day
     */
    public function day() {
        return $this->hasOne(Day::class, 'id', 'days_id');
    }
    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     * game day
     */
    public function quater() {
        return $this->hasOne(GameQuater::class, 'id', 'game_quaters_id');
    }
}
