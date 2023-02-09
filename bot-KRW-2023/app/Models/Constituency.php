<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Constituency extends Model
{
    use HasFactory;

    public $table = "constituencies";
    public $timestamps = true;

    protected $fillable = [
        'name',
        "seats_2023",
        "seats_2019",
        "population"
    ];
}
