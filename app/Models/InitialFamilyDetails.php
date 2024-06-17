<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InitialFamilyDetails extends Model
{
    use HasFactory;
    protected $table = 'initial_family_details';
    protected $fillable = [
        'state',
        'district',
        'city',
        'fk_family_id',
        'family_photo',
        'native_address',
        'created_at',
        'updated_at'
    ];
    public function familymemberdetails(){
        return $this->hasMany(FamilyDetails::class, 'family_id', 'fk_family_id');
    } 
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
