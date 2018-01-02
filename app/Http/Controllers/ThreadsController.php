<?php

namespace App\Http\Controllers;

use App\Channel;
use App\Inspections\Spam;
use App\Rules\Recaptcha;
use App\Thread;
use App\Filters\ThreadFilters;
use App\Trending;
use Illuminate\Http\Request;

class ThreadsController extends Controller
{

    /**
     * Create a new ThreadsController instance.
     */
    public function __construct()
    {
       $this->middleware('auth')->except(['index', 'show']);
    }

    /**
     * Display a listing of the resource.
     *
     * @param  Channel              $channel
     * @param  ThreadFilters        $filters
     * @param  \App\Trending        $trending
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Channel $channel, ThreadFilters $filters, Trending $trending)
    {
        $threads = $this->getThreads($channel, $filters);

        if(request()->wantsJson()){
            return $threads;
        }

        return view('threads.index', [
            'threads'  => $threads,
            'trending' => $trending->get()
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('threads.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  Recaptcha $recaptcha
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request, Recaptcha $recaptcha)
    {

        $this->validate($request, [
            'title'      => 'required|spamfree',
            'body'       => 'required|spamfree',
            'channel_id' => 'required|exists:channels,id',
            'g-recaptcha-response' => ['required', $recaptcha]
        ]);


        $thread = Thread::create([
            'user_id'    => auth()->id(),
            'channel_id' => request('channel_id'),
            'title'      => request('title'),
            'body'       => request('body'),
//            'slug'       => request('title')
        ]);

        if( request()->wantsJson() ) {
            return response($thread,201);
        }

        return redirect($thread->path())->with('flash', 'Your thread has been published!');
    }

    /**
     * Display the specified resource.
     *
     * @param  $channel
     * @param  \App\Thread  $thread
     * @param  \App\Trending $trending
     * @return \Illuminate\Http\Response
     */
    public function show($channel, Thread $thread, Trending $trending)
    {

        if( auth()->check() ){
            auth()->user()->read($thread);
        }

        // Get threads that have been read by user
        $trending->pushThreadToTrending($thread);

        // Get the number read by user
        $thread->increment('visits');

        return view('threads.show', compact('thread'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Thread  $thread
     * @return \Illuminate\Http\Response
     */
    public function edit(Thread $thread)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Thread  $thread
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Thread $thread)
    {
        //
    }

    /**
     * delete thread
     *
     * @param  Channel  $channel
     * @param  \App\Thread  $thread
     * @return \Illuminate\Http\Response
     */
    public function destroy($channel, Thread $thread)
    {

        // Determine whether the user have the permission to delete/update the thread
//        $this->authorize('update', $thread);
        if($thread->user_id != auth()->id()){
            abort(403, 'permission denied');
        }

        $thread->delete();

        if( request()->wantsJson()){
            return response([],204);
        }

        return redirect('/threads');
    }

    /**
     * Fetch all relevant threads
     *
     * @param Channel $channel
     * @param ThreadFilters $filters
     * @return \Illuminate\Database\Query\Builder|\Illuminate\Support\Collection|static
     */
    protected function getThreads(Channel $channel, ThreadFilters $filters)
    {

        $threads = Thread::latest()->filter($filters);

        if ($channel->exists) {
            $threads->where('channel_id', $channel->id);
        }


        $threads = $threads->get();
        return $threads;
    }
}
