<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\ScheduledMessage;
use App\Models\TeleChat;

class Announcement extends Model
{
    use HasFactory;
    public $table = "announcements";
    public $timestamps = true;

    protected $fillable = [
        'user_id',
        'type',
        'title',
        'subtitle',
        'content',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getHtmlContent() {
        $hydrator = new \Setono\EditorJS\BlockHydrator\CompositeHydrator();
        $hydrator->add(new \Setono\EditorJS\BlockHydrator\BlockHydrator());
        $hydrator->add(new \Setono\EditorJS\BlockHydrator\HeaderBlockHydrator());
        $hydrator->add(new \Setono\EditorJS\BlockHydrator\ImageBlockHydrator());
        $hydrator->add(new \Setono\EditorJS\BlockHydrator\ListBlockHydrator());
        $hydrator->add(new \Setono\EditorJS\BlockHydrator\ParagraphBlockHydrator());

        $parser = new \Setono\EditorJS\Parser\Parser($hydrator);
        $parserResult = $parser->parse($this->content);

        $blockRenderer = new \Setono\EditorJS\BlockRenderer\CompositeBlockRenderer();
        $blockRenderer->add(new \Setono\EditorJS\BlockRenderer\HeaderBlockRenderer());
        $blockRenderer->add(new \Setono\EditorJS\BlockRenderer\ImageBlockRenderer());
        $blockRenderer->add(new \Setono\EditorJS\BlockRenderer\ListBlockRenderer());
        $blockRenderer->add(new \Setono\EditorJS\BlockRenderer\ParagraphBlockRenderer());
        $blockRenderer->add(new \Setono\EditorJS\BlockRenderer\RawBlockRenderer());

        $renderer = new \Setono\EditorJS\Renderer\Renderer($blockRenderer);
        $html = $renderer->render($parserResult);
        return $html;
    }

    public static function createWithMessage($data) {
        $announcement = new Announcement();
        $announcement->fill($data);

        $scheduledMessage = new ScheduledMessage();
        $content = $announcement->getHtmlContent();
        if (strlen(strip_tags($content)) < 4096) {
            $scheduledMessage->content = $content;
        } else {
            $scheduledMessage->content = strip_tags($announcement->getHtmlContent());
        }
        $scheduledMessage->tele_chat_id = TeleChat::where("chat_id", get_option("telegram_channel_id"))->first()->id;

        $announcement->save();
        $scheduledMessage->save();
    }
}
