<?php

namespace Tests\Feature\Http\Controllers\Staff\Users;

use App\Eloquents\Permission;
use App\Eloquents\User;
use App\Exports\UsersExport;
use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Maatwebsite\Excel\Facades\Excel;
use Tests\TestCase;

class ExportActionTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @var User
     */
    private $staff;

    /**
     * @var User
     */
    private $user;

    public function setUp(): void
    {
        parent::setUp();
        Carbon::setTestNowAndTimezone(new CarbonImmutable('2021-09-14 21:22:23'));
        CarbonImmutable::setTestNowAndTimezone(new CarbonImmutable('2021-09-14 21:22:23'));

        $this->staff = factory(User::class)->states('staff')->create();
        $this->user = factory(User::class)->create();
    }

    /**
     * @test
     */
    public function ユーザー情報をCSVでダウンロードできる()
    {
        Permission::create(['name' => 'staff.users.export']);
        $this->staff->syncPermissions(['staff.users.export']);

        Excel::fake();
        $this->actingAs($this->staff)
            ->withSession(['staff_authorized' => true])
            ->get(route('staff.users.export'));

        $now = Carbon::now()->format('Y-m-d_H-i-s');

        Excel::assertDownloaded("ユーザー一覧_{$now}.csv", function (UsersExport $export) {
            return $export->collection()->contains('name', $this->staff->name)
                && $export->collection()->contains('name', $this->user->name);
        });
    }

    /**
     * @test
     */
    public function 権限がない場合はCSVをダウンロードできない()
    {
        $this->actingAs($this->staff)
            ->withSession(['staff_authorized' => true])
            ->get(route('staff.users.export'))
            ->assertForbidden();
    }
}
