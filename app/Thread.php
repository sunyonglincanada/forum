<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Filters\ThreadFilters;
use Illuminate\Database\Eloquent\Builder;

class Thread extends Model
{
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
     * Boot the model
     */
    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope('replyCount', function($builder){
            $builder->withCount('replies');
        });

        static::deleting(function($thread){
            $thread->repliesWithoutFav()->delete();
        });

        static::addGlobalScope('creator', function($builder){
            $builder->withCount('creator');
        });
    }

    public function path()
    {
        return "/threads/{$this->channel->slug}/{$this->id}";
    }

    public function replies()
    {
        return $this->hasMany(Reply::class)->withCount('favorites');
    }

    public function repliesWithoutFav()
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

    public function addReply( $reply )
    {
        $this->replies()->create($reply);
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
}
