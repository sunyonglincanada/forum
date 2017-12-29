<?php

namespace Tests\Unit;

use App\Reply;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;

class ReplyTest extends TestCase
{
    use DatabaseMigrations;

    /** @test */
    function it_has_an_owner()
    {
        $reply = create('App\Reply');

        $this->assertInstanceOf('App\User', $reply->owner);
    }

    /** @test */
    function it_knows_if_it_was_just_published()
    {
        $reply = create('App\Reply');

        $this->assertTrue($reply->wasJustPublished());

        $reply->created_at = Carbon::now()->subMonth();

        $this->assertFalse($reply->wasJustPublished());
    }

    /** @test */
    function it_can_detect_all_mentioned_users_in_the_body()
    {
        $reply = new Reply([
            'body' => '@Tom wants to talk to @JohnDoe'
        ]);

        $this->assertEquals(['Tom', 'JohnDoe'], $reply->mentionedUsers());
    }

    /** @test */
    function it_wraps_mentioned_usernames_in_the_body_within_anchor_tag()
    {
        $reply = new Reply([
            'body'  => 'Hello @Eric-Sun.'
        ]);

        $this->assertEquals(
            'Hello <a href="/profiles/Eric-Sun">@Eric-Sun</a>.',
                      $reply->body
        );
    }

    function it_knows_if_it_is_the_best_reply()
    {
        $reply = create('App\Reply');

        $this->assertFalse($reply->isBest());

        $reply->thread->update( ['best_reply_id' => $reply->id] );

        $this->assertTrue($reply->fresh()->isBest());
    }
}
