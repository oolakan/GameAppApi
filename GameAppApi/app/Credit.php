<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Credit extends Model
{
    protected $fillable = ['amount', 'funded_by', 'users_id'];

    public function user() {
        return $this->hasOne(User::class, 'id', 'users_id');
    }
}
