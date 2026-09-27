<?php

namespace App\Http\Controllers\Staff\Threads;

use App\Eloquents\Thread;
use App\Eloquents\User;
use App\Http\Controllers\Controller;
use Illuminate\Support\Str;

class ShowAction extends Controller
{
    public function __invoke(Thread $thread)
    {
        $thread->load(['circle', 'user', 'assignee']);

        return view('staff.threads.show')
            ->with('thread', $thread)
            ->with('entries', $thread->entries()->with(['author', 'contactCategory', 'attachments'])->get())
            ->with('staffUsers', User::where('is_staff', true)->orderBy('name_family')->orderBy('name_given')->get())
            ->with('clientToken', (string)Str::uuid());
    }
}
