<?php

namespace Tests\Feature\Http\Controllers\Staff\Schedules;

use App\Eloquents\Schedule;
use App\Eloquents\User;
use App\Exports\SchedulesExport;
use Carbon\Carbon;
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
     * @var Collection
     */
    private $schedules;

    public function setUp(): void
    {
        parent::setUp();

        $this->staff = factory(User::class)->state('staff')->create();
        $this->schedules = factory(Schedule::class, 2)->create();
    }

    /**
     * @test
     */
    public function 予定をCSVでダウンロードできる()
    {
        Excel::fake();
        $this->actingAs($this->staff)
            ->withSession(['staff_authorized' => true])
            ->get(route('staff.schedules.export'));

        $now = Carbon::now()->format('Y-m-d_H-i-s');

        Excel::assertDownloaded("スケジュール一覧_{$now}.csv", function (SchedulesExport $export) {
            $names = $this->schedules->pluck('name');
            return $export->collection()->contains('name', $names[0])
                && $export->collection()->contains('name', $names[1]);
        });
    }
}
