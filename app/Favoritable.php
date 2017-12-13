<?php

namespace App;


use Illuminate\Database\Eloquent\Model;

trait Favoritable
{

    protected static function bootFavoritable()
    {
        static::deleting(function($model){
            $model->favorites->each->delete();
        });
    }

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

        if (! $this->favorites()->where($attributes)->exists()) {
            return $this->favorites()->create($attributes);
        }
    }

    /**
     * Unfavorite the current reply
     *
     * @author Eric
     * @date 2017-12-13
     */
    public function unfavorite()
    {
        $attributes = ['user_id' => auth()->id()];

        $this->favorites()->where($attributes)->get()->each->delete();
    }

    /**
     * Check current user favorite one specific reply or not
     * @return bool
     */
    public function isFavorited()
    {
        return ! !$this->favorites->where('user_id', auth()->id())->count();
    }

    /**
     * Check whether the reply is favorited or not
     * @return bool
     *
     * @author Eric
     * @date 2017-12-13
     */
    public function getIsFavoritedAttribute()
    {
        return $this->isFavorited();
    }

    /**
     * Get the number of favorites for the reply
     * @return integer
     *
     * @author Eric
     * @date 2017-12-09
     */
    public function getFavoritesCountAttribute()
    {
        return $this->favorites->count();
    }
}