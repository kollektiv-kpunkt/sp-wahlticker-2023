<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Party;

class PoliticianResult extends Model
{
    use HasFactory;
    public $table = "politician_results";
    public $timestamps = true;

    protected $fillable = [
        'politicianId',
        "name",
        "partyId",
        'constituencyId',
        'votes',
        'initialPosition',
        'finalPosition',
        'elected',
        "chats_interested"
    ];

    protected $casts = [
        'elected' => 'boolean',
        "chats_interested" => "array"
    ];

    public function party()
    {
        return $this->belongsTo(Party::class, "partyId", "partyId");
    }

    public function constituency()
    {
        return $this->belongsTo(Constituency::class, "constituencyId", "id");
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
