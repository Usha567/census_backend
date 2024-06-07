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
        'family_id',
        'family_photo',
        'native_address',
        'created_at',
        'updated_at'
    ];
    public function familymemberdetails(){
        return $this->hasMany(FamilyDetails::class, 'family_id', 'id');
    } 
}
