<?php

namespace Tests\Feature\Http\Controllers\Staff\Threads;

use App\Eloquents\Circle;
use App\Eloquents\Permission;
use App\Eloquents\Thread;
use App\Eloquents\User;
use App\Services\Threads\ThreadsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Str;
use Tests\TestCase;

class IndexActionTest extends TestCase
{
    use RefreshDatabase;

    private User $staff;

    public function setUp(): void
    {
        parent::setUp();

        $this->staff = factory(User::class)->states('staff')->create();
        Permission::create(['name' => 'staff.threads.read']);
        $this->staff->syncPermissions(['staff.threads.read']);

        $circle = factory(Circle::class)->create();
        $member = factory(User::class)->create();
        $circle->users()->attach($member->id, ['is_leader' => true]);

        $threadsService = App::make(ThreadsService::class);
        $thread = $threadsService->getOrCreateForCircle($circle);
        $threadsService->postCircleMessage($thread, $member, 'お問い合わせです', null, [], (string)Str::uuid());
        $thread->refresh();
        $threadsService->setAssignee($thread, $this->staff, $thread->lock_version);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function 一覧画面とAPIが表示できる()
    {
        $response = $this->actingAs($this->staff)
            ->withSession(['staff_authorized' => true])
            ->get(route('staff.threads.index'));
        $response->assertOk();

        $apiResponse = $this->actingAs($this->staff)
            ->withSession(['staff_authorized' => true])
            ->getJson(route('staff.threads.api'));
        $apiResponse->assertOk();
        $apiResponse->assertJsonFragment(['status' => Thread::STATUS_NEEDS_STAFF]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function 担当者名で絞り込める()
    {
        $response = $this->actingAs($this->staff)
            ->withSession(['staff_authorized' => true])
            ->getJson(route('staff.threads.api', [
                'queries' => json_encode([
                    [
                        'key_name' => 'assignee_id.name_family',
                        'operator' => 'like',
                        'value' => $this->staff->name_family,
                    ],
                ]),
            ]));

        $response->assertOk();
    }
}
