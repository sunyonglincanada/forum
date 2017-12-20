<?php

namespace App\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Events\ThreadReceivedNewReply;

class NotifySubscribers
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  ThreadReceivedNewReply $event
     * @return void
     */
    public function handle($event)
    {
        $thread = $event->reply->thread;

        $thread->subscriptions
               ->where('user_id', '!=', $event->reply->user_id)
               ->each
               ->notify($event->reply);
    }
}
