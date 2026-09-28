<?php

namespace Tests\Feature\Http\Controllers\Staff\Threads;

use App\Eloquents\Circle;
use App\Eloquents\Permission;
use App\Eloquents\Thread;
use App\Eloquents\ThreadEntry;
use App\Eloquents\ThreadEntryAttachment;
use App\Eloquents\User;
use App\Services\Threads\ThreadsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

class NotesStoreActionTest extends TestCase
{
    use RefreshDatabase;

    private ThreadsService $threadsService;
    private Circle $circle;
    private User $member;
    private User $staff;
    private Thread $thread;

    public function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');

        $this->threadsService = App::make(ThreadsService::class);
        $this->circle = factory(Circle::class)->create();
        $this->member = factory(User::class)->create();
        $this->circle->users()->attach($this->member->id, ['is_leader' => true]);
        $this->staff = factory(User::class)->states('staff')->create();
        Permission::create(['name' => 'staff.threads.read,edit']);
        Permission::create(['name' => 'staff.threads.read']);
        $this->staff->syncPermissions(['staff.threads.read,edit', 'staff.threads.read']);

        $this->thread = $this->threadsService->getOrCreateForCircle($this->circle);
        $this->threadsService->postCircleMessage(
            $this->thread,
            $this->member,
            'ご質問があります',
            null,
            [],
            (string)Str::uuid()
        );
        $this->thread->refresh();
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function 内部メモは状態を変えずメールを送信しないがスタッフ画面には表示される()
    {
        Mail::fake();
        $statusBefore = $this->thread->status;

        $response = $this->actingAs($this->staff)
            ->withSession(['staff_authorized' => true])
            ->post(route('staff.threads.notes.store', ['thread' => $this->thread]), [
                'body' => '社内向けの内部メモです',
                'client_token' => (string)Str::uuid(),
            ]);

        $response->assertRedirect(route('staff.threads.show', ['thread' => $this->thread]));
        $this->thread->refresh();
        $this->assertSame($statusBefore, $this->thread->status);
        Mail::assertNothingSent();

        $showResponse = $this->actingAs($this->staff)
            ->withSession(['staff_authorized' => true])
            ->get(route('staff.threads.show', ['thread' => $this->thread]));
        $showResponse->assertOk();
        $showResponse->assertSee('社内向けの内部メモです');
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function 内部メモは企画側の画面のHTMLに含まれず添付ファイルは404になる()
    {
        $file = UploadedFile::fake()->create('memo.pdf', 100);

        $this->actingAs($this->staff)
            ->withSession(['staff_authorized' => true])
            ->post(route('staff.threads.notes.store', ['thread' => $this->thread]), [
                'body' => '企画には見せない内部メモです',
                'client_token' => (string)Str::uuid(),
                'attachments' => [$file],
            ]);

        $note = ThreadEntry::where('thread_id', $this->thread->id)
            ->where('kind', ThreadEntry::KIND_INTERNAL_NOTE)
            ->firstOrFail();
        $attachment = ThreadEntryAttachment::where('thread_entry_id', $note->id)->firstOrFail();

        $circleResponse = $this->actingAs($this->member)->get(route('contacts'));
        $circleResponse->assertOk();
        $circleResponse->assertDontSee('企画には見せない内部メモです');

        $attachmentResponse = $this->actingAs($this->member)
            ->get(route('contacts.attachments.show', ['attachment' => $attachment]));
        $attachmentResponse->assertNotFound();
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function 別の企画のメンバーは会話や添付ファイルを開けない()
    {
        $file = UploadedFile::fake()->create('circle_a.pdf', 100);
        $this->threadsService->postCircleMessage(
            $this->thread,
            $this->member,
            '添付します',
            null,
            [$file],
            (string)Str::uuid()
        );
        $attachment = ThreadEntryAttachment::firstOrFail();

        $otherCircle = factory(Circle::class)->create();
        $otherMember = factory(User::class)->create();
        $otherCircle->users()->attach($otherMember->id, ['is_leader' => true]);

        $response = $this->actingAs($otherMember)
            ->get(route('contacts.attachments.show', ['attachment' => $attachment]));

        $response->assertNotFound();
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function 会話のHTML添付ファイルはスタッフと企画の両方でダウンロードになる()
    {
        $file = UploadedFile::fake()->create('content.html', 1, 'text/html');
        $this->threadsService->postCircleMessage(
            $this->thread,
            $this->member,
            '添付します',
            null,
            [$file],
            (string)Str::uuid()
        );
        $attachment = ThreadEntryAttachment::where('name', 'content.html')->firstOrFail();

        $staffResponse = $this->actingAs($this->staff)
            ->withSession(['staff_authorized' => true])
            ->get(route('staff.threads.attachments.show', ['attachment' => $attachment]));
        $staffResponse->assertOk();
        $this->assertStringStartsWith('attachment;', $staffResponse->headers->get('content-disposition'));

        $memberResponse = $this->actingAs($this->member)
            ->get(route('contacts.attachments.show', ['attachment' => $attachment]));
        $memberResponse->assertOk();
        $this->assertStringStartsWith('attachment;', $memberResponse->headers->get('content-disposition'));
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function 権限がないユーザーはスタッフの会話一覧や詳細を開けない()
    {
        $noPermissionStaff = factory(User::class)->states('staff')->create();

        $indexResponse = $this->actingAs($noPermissionStaff)
            ->withSession(['staff_authorized' => true])
            ->get(route('staff.threads.index'));
        $indexResponse->assertForbidden();

        $showResponse = $this->actingAs($noPermissionStaff)
            ->withSession(['staff_authorized' => true])
            ->get(route('staff.threads.show', ['thread' => $this->thread]));
        $showResponse->assertForbidden();
    }
}
