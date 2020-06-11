<?php

namespace Tests\Feature\Http\Controllers\Staff\Circles;

use App\Exports\CirclesExport;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Eloquents\User;
use App\Eloquents\Circle;
use Maatwebsite\Excel\Facades\Excel;

class ExportActionTest extends TestCase
{
    use RefreshDatabase;

    private $staff;
    private $user;
    private $circle;
    private $circle_notsubmit;

    public function setUp(): void
    {
        parent::setUp();

        $this->staff = factory(User::class)->states('staff')->create();

        $this->user = factory(User::class)->create();
        $this->circle = factory(Circle::class)->create();
        $this->circle_notsubmit = factory(Circle::class)->states('notSubmitted')->create();

        $this->user->circles()->attach($this->circle->id, ['is_leader' => true]);
    }

    /**
     * @test
     */
    public function 企画情報をダウンロードできる()
    {
        Excel::fake();
        $this->actingAs($this->staff)
            ->withSession(['staff_authorized' => true])
            ->get('/staff/circles/export');

        Excel::assertDownloaded("circles_test.csv", function (CirclesExport $export) {
            return $export->collection()->contains('name', $this->circle->name)
                && $export->collection()->contains('name', '<>', $this->circle_notsubmit->name);
        });
    }
}
