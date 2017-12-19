<?php

namespace App\Http\Controllers;

use App\Inspections\Spam;
use App\Reply;
use Illuminate\Http\Request;
use App\Thread;
use Illuminate\Support\Facades\Gate;

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

        // [Eric] user cannot reply more than once per minute
        if (Gate::denies('create', new Reply)) {
            return response(
                'You are posting too frequently. Please take a break. :)', 429
            );
        }

        try {
            $this->validate(request(), ['body' => 'required|spamfree']);
            $reply = $thread->addReply([
                'body' => request('body'),
                'user_id' => auth()->id()
            ]);
        } catch (\Exception $e) {
            return response(
                'Sorry, your reply could not be saved at this time.', 422
            );
        }
        return $reply->load('owner');

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

        try{
            $this->validate(request(), [
                'body'  => 'required|spamfree'
            ]);

            $reply->update(request(['body']));

        } catch (\Exception $e){
            return response(
                'Sorry, your reply could not be saved at this time.', 422
            );
        }
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
