<?php

namespace Tests\Feature\Http\Controllers\Staff\Documents;

use App\Eloquents\Document;
use App\Eloquents\Permission;
use App\Eloquents\User;
use App\Eloquents\Schedule;
use App\Services\Documents\DocumentsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Mockery;

class UpdateActionTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @var User
     */
    private $staff;

    public function setUp(): void
    {
        parent::setUp();
        $this->staff = factory(User::class)->states('staff')->create();
    }

    /**
     * @test
     */
    public function DocumentsServiceのupdateDocumentが呼び出される()
    {
        Permission::create(['name' => 'staff.documents.edit']);
        $this->staff->syncPermissions(['staff.documents.edit']);

        $document = factory(Document::class)->create();

        $schedule = factory(Schedule::class)->create();

        $this->mock(DocumentsService::class, function ($mock) use ($document, $schedule) {
            $mock->shouldReceive('updateDocument')->once()->with(
                Mockery::on(function ($arg) use ($document) {
                    return $document->id === $arg->id;
                }),
                'document name',
                'document description',
                null,
                false,
                true,
                Mockery::on(function ($arg) use ($schedule) {
                    return $schedule->id === $arg->id && $schedule->name === $arg->name;
                }),
                'notes'
            )->andReturn(true);
        });

        $response = $this->actingAs($this->staff)
            ->withSession(['staff_authorized' => true])
            ->patch(route('staff.documents.update', ['document' => $document]), [
                'name' => 'document name',
                'description' => 'document description',
                'is_public' => '0',
                'is_important' => '1',
                'schedule_id' => $schedule->id,
                'notes' => 'notes',
            ]);

        $response->assertSessionHasNoErrors();

        $response->assertRedirect(route('staff.documents.edit', ['document' => $document]));
    }

    /**
     * @test
     */
    public function 権限がない場合は配布資料を更新できない()
    {
        $document = factory(Document::class)->create();

        $schedule = factory(Schedule::class)->create();

        $response = $this->actingAs($this->staff)
            ->withSession(['staff_authorized' => true])
            ->patch(route('staff.documents.update', ['document' => $document]), [
                'name' => 'document name',
                'description' => 'document description',
                'is_public' => '0',
                'is_important' => '1',
                'schedule_id' => $schedule->id,
                'notes' => 'notes',
            ]);

        $response->assertForbidden();
    }
}
