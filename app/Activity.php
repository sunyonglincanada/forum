<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Activity extends Model
{
    /**
     * Don't auto-apply mass assignment protection.
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * Fetch the associated subject for the activity
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function subject()
    {
        return $this->morphTo();
    }

    /**
     * Fetch an activity feed for the given user
     *
     * @param $user
     * @param int $take
     * @return mixed
     *
     * @author Eric
     * @date 2017-12-10
     */
    public static function feed( $user, $take = 50)
    {
        return static::where('user_id', $user->id)
            ->latest()
            ->with('subject')
            ->take($take)
            ->get()
            ->groupBy(function ($activity){
//                dd($activity->created_at,789);
                return $activity->created_at->format('Y-m-d');
            });
    }
}
