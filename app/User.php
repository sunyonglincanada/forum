<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'avatar_path'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
        'email'
    ];

    /**
     * The attributes that should be cast to native types.
     * @var array
     */
    protected $casts = [
        'confirmed' => 'boolean'
    ];


    /**
     * Get the route key name for Laravel
     * @return string
     */
    public function getRouteKeyName()
    {
        return 'name';
    }

    /**
     * Fetch all threads that were created by the user
     *
     * @return \Illuminate\Database\Query\Builder|static
     */
    public function threads()
    {
        return $this->hasMany(Thread::class)->latest();
    }

    /**
     * Grab all activities to the user
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     *
     * @author Eric
     * @date 2017-12-10
     */
    public function activity()
    {
        return $this->hasMany(Activity::class);
    }

    /**
     * Record that the user has read the given thread.
     * @param Thread $thread
     *
     * @author Eric
     * @date 2017-12-18
     */
    public function read($thread)
    {
        cache()->forever(
            $this->visitedThreadCacheKey($thread),
            Carbon::now()
        );
    }

    /**
     * Get the cache key for when a user reads a thread.
     *
     * @param Thread $thread
     * @return string
     *
     * @author Eric
     * @date 2017-12-18
     */
    public function visitedThreadCacheKey( $thread )
    {

        return sprintf("users.%s.visits.%s", $this->id, $thread->id);
    }

    /**
     * Get the latest reply for the user
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     *
     * @author Eric
     * @date 2017-12-19
     */
    public function lastReply()
    {
        return $this->hasOne(Reply::class)->latest();
    }

    /**
     * Get user avatar path
     * @param $avatar
     * @return string
     *
     * @author Eric
     * @data 2017-12-22
     */
    public function getAvatarPathAttribute($avatar)
    {
        return asset($avatar ?: 'images/avatars/default-avatar.png');
    }

    /**
     * Mark the user's account as confirmed.
     *
     * @author Eric
     * @date 2017-12-27
     */
    public function confirm()
    {
        $this->confirmed = true;
        $this->confirmation_token = null;

        $this->save();
    }

    /**
     * Determine the user is administrator or not.
     * @return bool
     *
     * @author Eric
     * @date 2017-12-31
     */
    public function isAdmin()
    {
        return in_array($this->name, ['JohnDoe', 'JaneDoe']);
    }
}
