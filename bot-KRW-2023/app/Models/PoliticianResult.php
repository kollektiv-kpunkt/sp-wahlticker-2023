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
        "politician_id",
        "name",
        "party_id",
        "constituency_id",
        "votes",
        "initialPosition",
        "finalPosition",
        "elected",
        "council",
        "chats_interested",
        "change_type",
        "is_scheduled",
        "municipal",
        "municipality_id",
    ];

    protected $casts = [
        "elected" => "boolean",
        'chats_interested' => 'array',
        "is_scheduled" => "boolean",
        "municipal" => "boolean",
    ];

    public function party()
    {
        return $this->belongsTo(Party::class, "party_id", "party_id");
    }

    public function constituency()
    {
        return $this->belongsTo(Constituency::class, "constituency_id", "id");
    }

    public function municipality()
    {
        if (!isset($this->municipality_id)) {
            return null;
        }
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
