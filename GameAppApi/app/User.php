<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'phone', 'location', 'approval_status', 'password','api_token', 'approval_status'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     * A user has a role
     */
    public function role(){
        return $this->hasOne(Role::class, 'id', 'roles_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     * user hs credit balance
     */
    public function credit() {
        return $this->hasOne(Credit::class, 'users_id', 'id');
    }
}
