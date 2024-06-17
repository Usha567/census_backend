<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FamilyDetails extends Model
{
    use HasFactory;
    protected $table='family_members_details';
    protected $fillable=[
        'family_id',
        'father_id',
        'family_member_id',
        'name',
        'mother_name',
        'father_name',
        'father_surname',
        'age',
        'dob',
        'mobile_number',
        'relation',
        'qualification',
        'marriage_type',
        'marital_status',
        'marriage_stage',
        'blood_group',
        'total_kids',
        'sons',
        'daughters',
        'occupation',
        'self_image'
    ];
    public function initfamilydetails(){
        return $this->hasMany(InitialFamilyDetails::class, 'fk_family_id', 'family_id');
    } 
}
