<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use App\Models\FamilyDetails;

class UniqueMobileAcrossFamilies implements Rule
{
    private $family_id;
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct($family_id)
    {
        $this->family_id = $family_id;
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        return !FamilyDetails::where('mobile_number', $value)
        ->where('family_id', '<>', $this->family_id)
        ->exists();
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'The mobile number must be unique across different families.';
    }
}
