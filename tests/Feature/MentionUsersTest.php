<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class MentionUsersTest extends TestCase
{
    use DatabaseMigrations;

    /** @test */
    function mentioned_users_in_a_reply_are_notified()
    {
        // Assume we have a user, JohnDoe, who is signed in.
        $john = create('App\User', ['name' => 'JohnDoe']);

        $this->signIn($john);

        // We have another user Tom
        $tom = create('App\User', ['name' => 'Tom']);

        $thread = create('App\Thread');

        $reply = make('App\Reply', [
            'body'  => 'Hey @Tom check this out'
        ]);

        $this->json('post', $thread->path() . '/replies', $reply->toArray());

        // tom would receive notifications
        $this->assertCount(1,$tom->notifications);
    }
}
