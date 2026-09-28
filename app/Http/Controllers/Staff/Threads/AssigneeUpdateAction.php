<?php

namespace App\Http\Controllers\Staff\Threads;

use App\Eloquents\Thread;
use App\Eloquents\User;
use App\Exceptions\Threads\StaleThreadException;
use App\Http\Controllers\Concerns\RespondsToStaleThread;
use App\Http\Controllers\Controller;
use App\Http\Requests\Staff\Threads\UpdateAssigneeRequest;
use App\Services\Threads\ThreadsService;

class AssigneeUpdateAction extends Controller
{
    use RespondsToStaleThread;

    /**
     * @var ThreadsService
     */
    private $threadsService;

    public function __construct(ThreadsService $threadsService)
    {
        $this->threadsService = $threadsService;
    }

    public function __invoke(Thread $thread, UpdateAssigneeRequest $request)
    {
        $values = $request->validated();
        $assignee = !empty($values['assignee_id']) ? User::find($values['assignee_id']) : null;

        try {
            $this->threadsService->setAssignee($thread, $assignee, (int)$values['lock_version']);
        } catch (StaleThreadException $e) {
            return $this->staleThreadResponse($request);
        }

        return redirect()
            ->route('staff.threads.show', ['thread' => $thread])
            ->with('topAlert.title', '担当者を更新しました');
    }
}
