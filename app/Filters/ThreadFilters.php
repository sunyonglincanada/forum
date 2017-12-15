<?php

namespace App\Filters;

use App\User;
use Illuminate\Http\Request;

class ThreadFilters extends Filters
{

    /**
     * Registered filters to operate upon
     *
     * @var array
     */
    protected $filters = ['by', 'popular', 'unanswered'];


    /**
     * Filter the query by a given username
     *
     * @param  string $username
     * @return Builder
     */
    protected function by($username)
    {
        $user = User::where('name', $username)->firstOrFail();

        return $this->builder->where('user_id', $user->id);
    }

    /**
     * Filter the query according to most popular threads
     *
     * @return $this
     */
    protected function popular()
    {
        $this->builder->getQuery()->orders = [];

        return $this->builder->orderBy('replies_count', 'desc');

    }


    /**
     * Filter the query/threads those are unanswered
     * @return $this
     *
     * @author Eric
     * @date 2017-12-15
     */
    protected function unanswered()
    {
        return $this->builder->where('replies_count', 0);
    }
}