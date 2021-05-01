<?php

namespace Tests\Feature\Http\Controllers\Staff\Pages;

use App\Eloquents\Page;
use App\Eloquents\User;
use App\Exports\PagesExport;
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
     * @var Collection
     */
    private $pages;

    public function setUp(): void
    {
        parent::setUp();
        Carbon::setTestNow(new CarbonImmutable('2021-09-14 21:22:23'));
        CarbonImmutable::setTestNow(new CarbonImmutable('2021-09-14 21:22:23'));

        $this->staff = factory(User::class)->states('staff')->create();

        $this->pages = factory(Page::class, 2)->create();
    }

    /**
     * @test
     */
    public function お知らせをCSVでダウンロードできる()
    {
        Excel::fake();
        $this->actingAs($this->staff)
            ->withSession(['staff_authorized' => true])
            ->get(route('staff.pages.export'));

        $now = Carbon::now()->format('Y-m-d_H-i-s');

        Excel::assertDownloaded("お知らせ一覧_{$now}.csv", function (PagesExport $export) {
            $titles = $this->pages->pluck('title');
            return $export->collection()->contains('title', $titles[0])
                && $export->collection()->contains('title', $titles[1]);
        });
    }
}
