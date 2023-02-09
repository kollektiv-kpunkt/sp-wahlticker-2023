<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Municipality extends Model
{
    use HasFactory;
    public $table = "municipalities";
    public $timestamps = true;

    public $fillable = [
        "name",
        "constituency_id"
    ];
}
