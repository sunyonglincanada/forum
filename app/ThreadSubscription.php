<?php

namespace App;

use App\Notifications\ThreadWasUpdated;
use Illuminate\Database\Eloquent\Model;

class ThreadSubscription extends Model
{
    /**
     * The attributes that are not mass assignable
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * Get the user associated with subscription.
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     *
     * @author Eric
     * @date 2017-12-17
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the thread associated with subscription.
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     *
     * @author Eric
     * @date 2017-12-17
     */
    public function thread()
    {
        return $this->belongsTo(Thread::class);
    }

    /**
     * Notify the related user that the thread was updated.
     * @param Reply $reply
     *
     * @author Eric
     * @date 2017-12-17
     */
    public function notify($reply)
    {
        $this->user->notify(new ThreadWasUpdated($this->thread, $reply));
    }
}
