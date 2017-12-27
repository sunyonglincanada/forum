<?php

namespace Tests\Feature;

use App\Activity;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;


class CreateThreadsTest extends TestCase
{
    use DatabaseMigrations;

    /** @test */
    function guest_may_not_create_threads()
    {

        $this->withExceptionHandling();

        $this->get('/threads/create')
            ->assertRedirect( route('login') );

        $this->post( route('threads') )
            ->assertRedirect( route('login') );

    }


    /** @test */
    function a_user_can_create_new_forum_threads()
    {
        // Given we have a signed in user
        $this->signIn();

        // When we hit the endpoint to create a new thread
        $thread = make('App\Thread');
        $response = $this->post('/threads', $thread->toArray());


        // Then, when we visit the thread page and We should see the new thread
        $this->get($response->headers->get('Location'))
            ->assertSee($thread->title)
            ->assertSee($thread->body);
    }

    /** @test */
    function a_thread_requires_a_title()
    {
        $this->publishThread(['title' => null])
             ->assertSessionHasErrors('title');

    }

    /** @test */
    function a_thread_requires_a_body()
    {
        $this->publishThread(['body' => null])
            ->assertSessionHasErrors('body');

    }

    /** @test */
    function a_thread_requires_a_valid_channel()
    {
        factory('App\Channel', 2)->create();

        $this->publishThread(['channel_id' => null])
            ->assertSessionHasErrors('channel_id');

        $this->publishThread(['channel_id' => 999])
            ->assertSessionHasErrors('channel_id');

    }

    /** @test */
    function unauthorized_users_may_not_delete_threads()
    {
        $this->withExceptionHandling();

        $thread = create('App\Thread');

        $this->delete($thread->path())->assertRedirect('/login');

        $this->signIn();


        $this->delete($thread->path())->assertStatus(403);
    }

    /** @test */
    function authorized_users_can_delete_threads()
    {
        $this->signIn();

        $thread = create('App\Thread', ['user_id'=>auth()->id()]);
        $reply = create('App\Reply', ['thread_id' => $thread->id]);


        $response = $this->json('DELETE', $thread->path());



        $response->assertStatus(204);

        $this->assertDatabaseMissing('threads', ['id' => $thread->id]);
        $this->assertDatabaseMissing('replies', ['id' => $reply->id]);

        $this->assertEquals(0, Activity::count());

    }


    /**
     * @param array $overrides
     * @return \Illuminate\Foundation\Testing\TestResponse
     */
    public function publishThread($overrides = [] )
    {

        $this->withExceptionHandling()->signIn();

        $thread = make('App\Thread', $overrides);

        return $this->post(route('threads'), $thread->toArray());

    }

    /** @test */
    function new_users_must_first_confirm_their_email_address_before_creating_threads()
    {
        $user = factory('App\User')->states('unconfirmed')->create();

        $this->signIn($user);

        $thread = make('App\Thread');

        $this->post(route('threads'), $thread->toArray())
            ->assertRedirect(route('threads'))
            ->assertSessionHas('flash', 'You must confirm your email address first.');
    }

}
