<?php

namespace App;


use Illuminate\Database\Eloquent\Model;

trait Favoritable
{

    /**
     * A reply can be favorited
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function favorites()
    {
        return $this->morphMany(Favorite::class, 'favorited');
    }

    /**
     * Favorite the current reply
     * @return Model
     */
    public function favorite()
    {
        $attributes = ['user_id' => auth()->id()];

        if (!$this->favorites()->where($attributes)->exists()) {
            return $this->favorites()->create($attributes);
        }
    }

    /**
     * Check current user favorite one specific reply or not
     * @return bool
     */
    public function isFavorited()
    {
        return !!$this->favorites->where('user_id', auth()->id())->count();
    }

    public function getFavoriteCountAttribute()
    {
        return $this->favorites->count();
    }
}