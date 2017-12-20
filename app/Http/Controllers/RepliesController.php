<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreatePostRequest;
use App\Inspections\Spam;
use App\Reply;
use Illuminate\Http\Request;
use App\Thread;

class RepliesController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth', ['except' => 'index']);
    }

    /**
     * Get all relevant replies
     * @param $channelId
     * @param Thread $thread
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     *
     * @author Eric
     * @date 2017-12-15
     */
    public function index( $channelId, Thread $thread)
    {
        return $thread->replies()->paginate(20);
    }


    /**
     * Persist a new reply
     *
     * @param int    $channelId
     * @param Thread $thread
     * @param  CreatePostRequest $form
     * @return \Illuminate\Database\Eloquent\Model|\Illuminate\Http\RedirectResponse
     */
    public function store($channelId, Thread $thread, CreatePostRequest $form)
    {

        return $thread->addReply([
            'body' => request('body'),
            'user_id' => auth()->id()
        ])->load('owner');

    }

    /**
     * Update an existing reply
     * @param Reply $reply
     * @return mixed
     *
     * @author Eric
     * @date 2017-12-12
     */
    public function update(Reply $reply)
    {
        $this->authorize('update', $reply);

        $this->validate(request(), ['body' => 'required|spamfree']);

        $reply->update(request(['body']));
    }


    /**
     * Delete the given reply
     *
     * @param Reply $reply
     * @return \Illuminate\Http\RedirectResponse
     *
     * @author Eric
     * @date 2017-12-12
     */
    public function destroy(Reply $reply)
    {
        $this->authorize('update', $reply);

        $reply->delete();

        // Ajaxify reply delete
        if(request()->expectsJson()){
            return response(['status' => 'Reply Deleted']);
        }

        return back();
    }

}
