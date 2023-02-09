<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ScheduledMessage extends Model
{
    use HasFactory;
    protected $table = "scheduled_messages";
    public $timestamps = true;

    protected $fillable = [
        "tele_chat_id",
        "content",
        "sent",
        "status",
        "sent_at",
        "message_identifier"
    ];
}
