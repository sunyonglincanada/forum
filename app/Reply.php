<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Reply extends Model
{
    use Favoritable, RecordsActivity;


    protected $guarded = [];

    protected $with = ['owner','favorites'];

    /**
     * The accessors to append to the model's array form
     * @var array
     */
    protected $appends = ['favoritesCount', 'isFavorited'];

    /**
     * Boot the reply instance
     */
    protected static function boot()
    {
        parent::boot();

        static::created(function ($reply) {

            $reply->thread->increment('replies_count');
        });

        static::deleted(function ($reply){

            $reply->thread->decrement('replies_count');
        });
    }

    /**
     * A reply belongs to an owner
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     *
     * @author Eric
     * @date 2017-12-10
     */
    public function owner()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * A reply belongs to a thread
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     *
     * @author Eric
     * @date 2017-12-10
     */
    public function thread()
    {
        return $this->belongsTo(Thread::class);
    }

    /**
     * Determine the path to the reply
     * @return string
     *
     * @author Eric
     * @date 2017-12-12
     */
    public function path()
    {
        return $this->thread->path() . "#reply-{$this->id}";
    }


}
