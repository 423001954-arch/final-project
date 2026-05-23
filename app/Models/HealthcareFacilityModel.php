<?php

namespace App\Models;

use CodeIgniter\Model;

class HealthcareFacilityModel extends Model
{
    protected $table            = 'healthcare_facilities';
    protected $primaryKey       = 'id';
    protected $returnType       = 'array';
    protected $useTimestamps    = true;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'code',
        'name',
        'address',
        'contact_person',
        'contact_phone',
        'is_active',
    ];

    protected $validationRules = [
        'code'          => 'required|alpha_dash|min_length[2]|max_length[30]|is_unique[healthcare_facilities.code,id,{id}]',
        'name'          => 'required|min_length[2]|max_length[150]',
        'contact_phone' => 'permit_empty|max_length[40]',
        'is_active'     => 'permit_empty|in_list[0,1]',
    ];
}
