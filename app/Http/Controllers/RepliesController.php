<?php

namespace App\Http\Controllers;

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
     * @param $channelId
     * @param Thread $thread
     * @return \Illuminate\Database\Eloquent\Model|\Illuminate\Http\RedirectResponse
     */
    public function store($channelId, Thread $thread)
    {
        $this->validateReply();


        $reply = $thread->addReply([
            'body' => request('body'),
            'user_id' => auth()->id()
        ]);

        if(request()->expectsJson()){
            return $reply->load('owner');
        }

        return back()->with('flash', 'Your reply has been left. ');
    }

    /**
     * Update an existing reply
     * @param Reply $reply
     *
     * @author Eric
     * @date 2017-12-12
     */
    public function update(Reply $reply)
    {
        $this->authorize('update', $reply);

        $this->validateReply();

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

    /**
     * Validate the reply for spam detection
     *
     * @author Eric
     * @date 2017-12-18
     */
    protected function validateReply()
    {
        $this->validate(request(), ['body' => 'required']);

        resolve(Spam::class)->detect(request('body'));
    }
}
