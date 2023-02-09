<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CountedMunicipality extends Model
{
    use HasFactory;
    protected $table = 'counted_municipalities';
    public $timestamps = true;

    protected $fillable = [
        'municipality_id',
        'type',
    ];
}
