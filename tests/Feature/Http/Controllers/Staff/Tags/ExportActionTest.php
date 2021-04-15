<?php

namespace Tests\Feature\Http\Controllers\Staff\Tags;

use App\Eloquents\Tag;
use App\Eloquents\User;
use App\Exports\TagsExport;
use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
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
    private $tags;

    public function setUp(): void
    {
        parent::setUp();
        Carbon::setTestNow(new CarbonImmutable('2021-09-14 21:22:23'));
        CarbonImmutable::setTestNow(new CarbonImmutable('2021-09-14 21:22:23'));

        $this->staff = factory(User::class)->states('staff')->create();
        $this->tags = factory(Tag::class, 2)->create();
    }

    /**
     * @test
     */
    public function 企画タグのCSVがダウンロードできる()
    {
        Excel::fake();
        $this->actingAs($this->staff)
            ->withSession(['staff_authorized' => true])
            ->get(route('staff.tags.export'));

        $now = Carbon::now()->format('Y-m-d_H-i-s');

        Excel::assertDownloaded("tags_{$now}.csv", function (TagsExport $export) {
            $names = $this->tags->pluck('name');
            return $export->collection()->contains('name', $names[0])
                && $export->collection()->contains('name', $names[1]);
        });
    }
}
