<?php

namespace Tests\Feature\Http\Controllers\Staff\Documents;

use App\Eloquents\Document;
use App\Eloquents\Permission;
use App\Eloquents\User;
use App\Exports\DocumentsExport;
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
     * @var Document
     */
    private $document;

    /**
     * @var Document
     */
    private $anotherDocument;

    public function setUp(): void
    {
        parent::setUp();
        Carbon::setTestNowAndTimezone(new CarbonImmutable('2021-09-14 21:22:23'));
        CarbonImmutable::setTestNowAndTimezone(new CarbonImmutable('2021-09-14 21:22:23'));

        $this->staff = factory(User::class)->state('staff')->create();

        $this->document = factory(Document::class)->create([
            'name' => '配布資料',
        ]);

        $this->anotherDocument = factory(Document::class)->create([
            'name' => '見てほしい資料',
        ]);
    }

    /**
     * @test
     */
    public function 配布資料の情報がCSVでダウンロードできる()
    {
        Permission::create(['name' => 'staff.documents.export']);
        $this->staff->syncPermissions(['staff.documents.export']);

        Excel::fake();

        $this->actingAs($this->staff)
            ->withSession(['staff_authorized' => true])
            ->get(route('staff.documents.export'));

        $now = Carbon::now()->format('Y-m-d_H-i-s');

        Excel::assertDownloaded("配布資料一覧_{$now}.csv", function (DocumentsExport $export) {
            return $export->collection()->contains('name', '配布資料')
                && $export->collection()->contains('name', '見てほしい資料');
        });
    }

    /**
     * @test
     */
    public function 権限がない場合はCSVをダウンロードできない()
    {
        $this->actingAs($this->staff)
            ->withSession(['staff_authorized' => true])
            ->get(route('staff.documents.export'))
            ->assertForbidden();
    }
}
