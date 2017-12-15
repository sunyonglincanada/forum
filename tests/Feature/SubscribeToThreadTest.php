<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;

class SubscribeToThreadTest extends TestCase
{
    use DatabaseMigrations;


    /** @test */
    function a_user_can_subscribe_to_threads()
    {
        $this->signIn();

        $thread = create('App\Thread');

        $this->post($thread->path() . '/subscriptions/');

        $thread->addReply([
            'user_id'  => auth()->id(),
            'body'     => 'Leave a Reply here.'
        ]);

        // A notification should be prepared for the user.
        // TODO: [Eric] created notification when user subscribe to thread
        // $this->assertCount(1, auth()->user()->notifications);
    }

    /** @test */
    function a_user_can_unsubscribe_from_threads()
    {
        $this->signIn();

        $thread = create('App\Thread');

        $thread->subscribe();

        $this->delete($thread->path() . '/subscriptions/');


        $this->assertCount(0, $thread->subscriptions);

    }
}
