<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Filters\ThreadFilters;
use Illuminate\Database\Eloquent\Builder;

class Thread extends Model
{
    use RecordsActivity;

    /**
     * Don't auto-apply mass assignment protection.
     *
     * @var array
     */
    protected $guarded = [];


    /**
     * The relationships to always eager-load
     * @var array
     */
    protected $with = ['creator', 'channel'];

    /**
     * The accessors to append to the model's array form
     *
     * @var array
     */
    protected $appends = ['isSubscribedTo'];

    /**
     * Boot the Thread instance
     */
    protected static function boot()
    {
        parent::boot();

        static::deleting(function($thread){
            $thread->replies->each->delete();
        });

        static::addGlobalScope('creator', function($builder){
            $builder->withCount('creator');
        });
    }

    public function path()
    {
        return "/threads/{$this->channel->slug}/{$this->id}";
    }

    // [Eric] bug fix: General error: 25 bind or column index out of range due to withCount('favorites')
    public function replies()
    {
        return $this->hasMany(Reply::class);
    }


    public function creator()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function channel()
    {
        return $this->belongsTo(Channel::class);
    }

    /**
     * Add a reply to a thread
     * @param $reply
     * @return Model
     */
    public function addReply( $reply )
    {
        $reply = $this->replies()->create($reply);

        // Prepare notifications for all subscribers.
        $this->notifySubscribers($reply);

    }

    /**
     * Notify all thread subscribers about a new reply
     * @param $reply
     * @return mixed
     *
     * @author Eric
     * @date 2017-12-17
     */
    public function notifySubscribers($reply)
    {

        $this->subscriptions
            ->where('user_id', '!=', $reply->user_id)
            ->each
            ->notify($reply);

        return $reply;
    }

    /**
     * Apply all relevant thread filters
     * @param $query
     * @param $filters
     * @return Builder
     */
    public function scopeFilter($query, ThreadFilters $filters)
    {
        return $filters->apply($query);
    }

    /**
     * Subscribe a user from the current thread
     * @param null|int $userId
     * @return $this
     *
     * @author Eric
     * @date 2017-12-15
     */
    public function subscribe($userId = null)
    {
        $this->subscriptions()->create([
            'user_id'   => $userId ?: auth()->id()
        ]);

        return $this;
    }

    /**
     * Unsubscribe a user from the current thread
     * @param null|int $userId
     *
     * @author Eric
     * @date 2017-12-15
     */
    public function unsubscribe($userId = null)
    {
        $this->subscriptions()
            ->where('user_id',$userId ?: auth()->id())
            ->delete();
    }

    /**
     * A thread can have many subscriptions
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     *
     * @author Eric
     * @date 2017-12-15
     */
    public function subscriptions()
    {
        return $this->hasMany(ThreadSubscription::class);
    }

    /**
     * Check the current user is subscribed to the current thread
     *
     * @return bool
     *
     * @author Eric
     * @date 2017-12-15
     */
    public function getIsSubscribedToAttribute()
    {
        return $this->subscriptions()
                    ->where('user_id', auth()->id())
                    ->exists();
    }
}
