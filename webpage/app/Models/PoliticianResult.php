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
}
