<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TeleChat extends Model
{
    use HasFactory;
    public $table = 'tele_chats';
    public $timestamps = true;
    
    protected $fillable = [
        'chat_id',
        'chat_type',
        'chat_title',
        'chat_username',
        'chat_first_name',
        'chat_last_name',
    ];
}
