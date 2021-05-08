<?php

namespace Tests\Feature\Http\Controllers\Staff\Places;

use App\Eloquents\Permission;
use App\Eloquents\Place;
use App\Eloquents\User;
use App\Exports\PlacesExport;
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
     * @var Place
     */
    private $place;

    /**
     * @var Place
     */
    private $anotherPlace;

    public function setUp(): void
    {
        parent::setUp();
        Carbon::setTestNow(new CarbonImmutable('2021-09-14 21:22:23'));
        CarbonImmutable::setTestNow(new CarbonImmutable('2021-09-14 21:22:23'));

        $this->staff = factory(User::class)->state('staff')->create();

        $this->place = factory(Place::class)->create([
            'name' => 'E208',
        ]);

        $this->anotherPlace = factory(Place::class)->create([
            'name' => 'E209',
        ]);
    }

    /**
     * @test
     */
    public function ブース情報がCSVでダウンロードできる()
    {
        Permission::create(['name' => 'staff.places.export']);
        $this->staff->syncPermissions(['staff.places.export']);

        Excel::fake();

        $this->actingAs($this->staff)
            ->withSession(['staff_authorized' => true])
            ->get(route('staff.places.export'));

        $now = Carbon::now()->format('Y-m-d_H-i-s');

        Excel::assertDownloaded("場所一覧_{$now}.csv", function (PlacesExport $export) {
            return $export->collection()->contains('name', 'E208')
                && $export->collection()->contains('name', 'E209');
        });
    }

    /**
     * @test
     */
    public function 権限がない場合はCSVをダウンロードできない()
    {
        $this->actingAs($this->staff)
            ->withSession(['staff_authorized' => true])
            ->get(route('staff.places.export'))
            ->assertForbidden();
    }
}
