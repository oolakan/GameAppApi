<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class GameTransaction extends Model
{
    protected $fillable = [
        'banker_no',
        'game_no_played',
        'no_of_matched_figures',
        'ticket_id',
        'serial_no',
        'unit_stake',
        'total_amount',
        'winning_amount',
        'game_names_id',
        'game_types_id',
        'game_type_options_id',
        'game_quaters_id',
        'users_id',
        'date_played',
        'time_played',
        'payment_option',
        'time_played',
        'status',
        'draw_type'
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
     * Game type
     */
    public function game_type(){
        return $this->hasOne(GameType::class, 'id', 'game_types_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     * Game type option
     */
    public function game_type_option(){
        return $this->hasOne(GameTypeOption::class, 'id', 'game_type_options_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     * Game qquater
     */
    public function game_quater() {
        return $this->hasOne(GameQuater::class, 'id', 'game_quaters_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     * Game qquater
     */
    public function user() {
        return $this->hasOne(User::class, 'id', 'users_id');
    }
}
