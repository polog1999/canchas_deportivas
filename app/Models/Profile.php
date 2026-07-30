<?php

namespace App\Models;

use App\Enums\DocumentType;
use Illuminate\Database\Eloquent\Model;

class Profile extends Model
{
    protected $fillable = [
    'document_type',
    'document_number',
    'names',
    'last_name_paternal',
    'last_name_maternal',
    'address',
    'ubigeo_department',
    'ubigeo_province',
    'ubigeo_district',
];

 protected $casts = [
    'document_type'=> DocumentType::class
   ];

public function user()
{
    return $this->belongsTo(User::class);
}
  
}
