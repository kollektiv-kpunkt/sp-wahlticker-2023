<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Party extends Model
{
    use HasFactory;
    public $table = "parties";
    public $timestamps = true;

    protected $fillable = [
        "party_id",
        "name",
        "abbreviation",
        "color",
        "seats_2023",
        "seats_2019",
        "seats_2015",
        "voteShare_2023",
        "voteShare_2019",
        "voteShare_2015",
    ];
}
