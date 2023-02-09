<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PartyResult extends Model
{
    use HasFactory;
    public $table = "party_results";
    public $timestamps = true;

    public $fillable = [
        "party_id",
        "constituency_id",
        "votes",
        "voteShare",
        "seats",
        "seatChange",
        "chats_interested",
        "municipality_id",
        "voteShare_change"
    ];

    protected $casts = [
        'chats_interested' => 'array',
    ];

    public function party()
    {
        return $this->belongsTo(Party::class, "party_id", "party_id");
    }

    public function constituency()
    {
        return $this->belongsTo(Constituency::class, "constituency_id", "id");
    }

    public function municipality() {
        return $this->belongsTo(Municipality::class, "municipality_id", "id");
    }

    public function addChatInterested($chatId) {
        if (in_array($chatId, $this->chats_interested ?? [])) {
            return false;
        } else {
            $this->chats_interested = array_merge($this->chats_interested ?? [], [$chatId]);
            $this->save();
            return true;
        }
    }

    public function removeChatInterested($chatId) {
        if (in_array($chatId, $this->chats_interested ?? [])) {
            $this->chats_interested = array_diff($this->chats_interested ?? [], [$chatId]);
            $this->save();
            return true;
        } else {
            return false;
        }
    }
}
