<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Reply extends Model
{
    use Favoritable, RecordsActivity;


    protected $guarded = [];

    protected $with = ['owner','favorites'];

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


}
