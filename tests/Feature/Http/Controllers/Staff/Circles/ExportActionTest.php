<?php

namespace Tests\Feature\Http\Controllers\Staff\Circles;

use App\Exports\CirclesExport;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Eloquents\User;
use App\Eloquents\Circle;
use App\Eloquents\Permission;
use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Maatwebsite\Excel\Facades\Excel;

class ExportActionTest extends TestCase
{
    use RefreshDatabase;

    /** @var User */
    private $staff;

    /** @var User */
    private $user;

    /** @var Circle */
    private $circle;

    /** @var Circle */
    private $circle_not_submitted;

    public function setUp(): void
    {
        parent::setUp();
        Carbon::setTestNow(new CarbonImmutable('2019-08-21 14:52:38'));
        CarbonImmutable::setTestNow(new CarbonImmutable('2019-08-21 14:52:38'));

        $this->staff = factory(User::class)->states('staff')->create();

        $this->user = factory(User::class)->create();
        $this->circle = factory(Circle::class)->create();
        $this->circle_not_submitted = factory(Circle::class)->states('notSubmitted')->create();

        $this->user->circles()->attach($this->circle->id, ['is_leader' => true]);
    }

    /**
     * @test
     */
    public function 企画情報をCSVでダウンロードできる()
    {
        Permission::create(['name' => 'staff.circles.export']);
        $this->staff->syncPermissions(['staff.circles.export']);

        Excel::fake();
        $this->actingAs($this->staff)
            ->withSession(['staff_authorized' => true])
            ->get('/staff/circles/export');

        $now = Carbon::now()->format('Y-m-d_H-i-s');

        Excel::assertDownloaded("企画一覧_{$now}.csv", function (CirclesExport $export) {
            return $export->collection()->contains('name', $this->circle->name)
                && $export->collection()->contains('name', '<>', $this->circle_not_submitted->name);
        });
    }

    /**
     * @test
     */
    public function 権限がない場合は企画情報をダウンロードできない()
    {
        $this->actingAs($this->staff)
            ->withSession(['staff_authorized' => true])
            ->get('/staff/circles/export')
            ->assertForbidden();
    }
}
