<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PoliticianResult extends Model
{
    use HasFactory;
    public $table = "politician_results";
    public $timestamps = true;

    protected $fillable = [
        'politician_id',
        'constituency_id',
        'party_id',
        'votes',
        'percentage',
        'elected',
    ];
}
