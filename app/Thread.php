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
        return $this->replies()->create($reply);
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
