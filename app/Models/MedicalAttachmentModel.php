<?php

namespace App\Models;

use CodeIgniter\Model;

class MedicalAttachmentModel extends Model
{
    protected $table         = 'medical_attachments';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $protectFields = true;
    protected $allowedFields = [
        'facility_id',
        'medicine_id',
        'batch_id',
        'original_name',
        'stored_name',
        'mime_type',
        'size_kb',
        'path',
        'optimized_path',
        'uploaded_by',
        'created_at',
    ];

    protected $validationRules = [
        'facility_id'    => 'required|is_natural_no_zero',
        'medicine_id'    => 'permit_empty|is_natural_no_zero',
        'batch_id'       => 'permit_empty|is_natural_no_zero',
        'original_name'  => 'required|max_length[180]',
        'stored_name'    => 'required|max_length[180]',
        'mime_type'      => 'required|max_length[100]',
        'size_kb'        => 'required|is_natural',
        'path'           => 'required|max_length[255]',
        'optimized_path' => 'permit_empty|max_length[255]',
        'uploaded_by'    => 'permit_empty|is_natural_no_zero',
    ];
}
