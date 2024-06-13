<?php

namespace App\Models;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;
  
    protected $table = 'users';
    
    protected $fillable = [
        'name',
        'username',
        'email',
        'password',
        'mobile',
        'role_id',
        'state',
        'district',
        'city',
        'other_state',
        'other_district',
        'other_city',
        'created_at',
        'updated_at'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function state_details()
    {
        return $this->hasOne(State::class, 'id', 'state');
    }

    public function district_details()
    {
        return $this->hasOne(District::class, 'id','district');
    }

    public function city_details()
    {
        return $this->hasOne(City::class, 'id','city');
    }
}

