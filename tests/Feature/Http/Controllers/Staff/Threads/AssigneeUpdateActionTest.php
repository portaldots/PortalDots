<?php

namespace Tests\Feature\Http\Controllers\Staff\Threads;

use App\Eloquents\Circle;
use App\Eloquents\Permission;
use App\Eloquents\Thread;
use App\Eloquents\User;
use App\Services\Threads\ThreadsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\App;
use Tests\TestCase;

class AssigneeUpdateActionTest extends TestCase
{
    use RefreshDatabase;

    private User $staff;
    private Thread $thread;

    public function setUp(): void
    {
        parent::setUp();

        $this->staff = factory(User::class)->states('staff')->create();
        Permission::create(['name' => 'staff.threads.read,edit']);
        $this->staff->syncPermissions(['staff.threads.read,edit']);

        $circle = factory(Circle::class)->create();
        $this->thread = App::make(ThreadsService::class)->getOrCreateForCircle($circle);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function 担当者を更新できる()
    {
        $response = $this->actingAs($this->staff)
            ->withSession(['staff_authorized' => true])
            ->patch(route('staff.threads.assignee.update', ['thread' => $this->thread]), [
                'assignee_id' => $this->staff->id,
                'lock_version' => $this->thread->lock_version,
            ]);

        $response->assertRedirect(route('staff.threads.show', ['thread' => $this->thread]));
        $this->thread->refresh();
        $this->assertSame($this->staff->id, $this->thread->assignee_id);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function 古いlock_versionでは更新されずセッションへ戻る()
    {
        $response = $this->actingAs($this->staff)
            ->withSession(['staff_authorized' => true])
            ->patch(route('staff.threads.assignee.update', ['thread' => $this->thread]), [
                'assignee_id' => $this->staff->id,
                'lock_version' => $this->thread->lock_version + 1,
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('topAlert.type', 'danger');
        $this->thread->refresh();
        $this->assertNull($this->thread->assignee_id);
    }
}
