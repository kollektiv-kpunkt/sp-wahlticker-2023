<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OpenReply extends Model
{
    use HasFactory;

    protected $fillable = [
        "command",
        "replied",
        "tele_chat_id",
    ];

    public $casts = [
        "replied" => "boolean",
    ];
}
