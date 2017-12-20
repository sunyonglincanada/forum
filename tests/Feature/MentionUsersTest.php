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

    /** @test */
    function it_can_fetch_all_mentioned_users_starting_with_the_given_characters()
    {
        create('App\User', ['name' => 'johndoe']);

        create('App\User', ['name' => 'johndoe2']);

        create('App\User', ['name' => 'tomdoe']);

        $results = $this->json('GET', '/api/users', ['name' => 'john']);

        $this->assertCount(2, $results->json());

    }
}
