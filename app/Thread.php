<?php

namespace App;

use App\Events\ThreadReceivedNewReply;
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


        static::created(function ($thread) {
            $thread->update([
                'slug'  => $thread->title
            ]);
        });
    }

    public function path()
    {
        return "/threads/{$this->channel->slug}/{$this->slug}";
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
     * @param array $reply
     * @return Model
     */
    public function addReply( $reply )
    {
        $reply = $this->replies()->create($reply);

        // notify users that they are mentioned in the reply eventgit
        event(new ThreadReceivedNewReply($reply));

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

    /**
     * Determine if the thread has been updated since the user last read it.
     *
     * @param User $user
     * @return bool
     *
     * @author Eric
     * @date 2017-12-18
     */
    public function hasUpdatesFor($user)
    {
        $key = $user->visitedThreadCacheKey($this);

        return $this->updated_at > cache($key);
    }

    /**
     * Get the route key name
     * @return string
     *
     * @author Eric
     * @date 2017-12-28
     */
    public function getRouteKeyName()
    {
        return 'slug';
    }

    /**
     * Set the proper slug attribute
     * @param $value
     *
     * @author Eric
     * @date 2017-12-28
     */
    public function setSlugAttribute($value)
    {
        if( static::whereSlug( $slug = str_slug($value) )->exists() ) {

            $slug = "{$slug}-{$this->id}";
        }

        $this->attributes['slug'] = $slug;
    }

    /**
     * Increment a slug's suffix (when there is same slug name, add slash plus number to slug suffix) (deprecated)
     * @param $slug
     * @return mixed|string
     *
     * @author Eric
     * @date 2017-12-28
     */
    public function incrementSlug($slug)
    {
        $max = static::whereTitle($this->title)->latest('id')->value('slug');

        if( is_numeric($max[-1]) ) {
            return preg_replace_callback('/(\d+)$/', function ($matches) {
                return $matches[1] + 1;
            }, $max);
        }

        return "{$slug}-2";
    }

    /**
     * Mark the given reply as the best answer.
     * @param Reply $reply
     *
     * @author Eric
     * @date 2017-12-29
     */
    public function markBestReply(Reply $reply)
    {
        $this->update(['best_reply_id'  => $reply->id]);
    }

    /**
     * Lock the Thread
     *
     * @author Eric
     * @date 2017-12-31
     */
    public function lock()
    {
        $this->update(['locked' => true]);
    }
}
