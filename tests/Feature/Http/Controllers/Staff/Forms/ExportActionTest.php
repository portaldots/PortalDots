<?php

namespace Tests\Feature\Http\Controllers\Staff\Forms;

use App\Eloquents\CustomForm;
use App\Eloquents\Form;
use App\Eloquents\Permission;
use App\Eloquents\User;
use App\Exports\FormsExport;
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
     * @var Form
     */
    private $form;

    /**
     * @var Form
     */
    private $anotherForm;

    /**
     * @var CustomForm
     */
    private $customForm;

    public function setUp(): void
    {
        parent::setUp();
        Carbon::setTestNowAndTimezone(new CarbonImmutable('2021-09-14 21:22:23'));
        CarbonImmutable::setTestNowAndTimezone(new CarbonImmutable('2021-09-14 21:22:23'));

        $this->staff = factory(User::class)->state('staff')->create();

        $this->form = factory(Form::class)->create([
            'name' => '場所登録申請',
        ]);

        $this->anotherForm = factory(Form::class)->create([
            'name' => 'パンフレット掲載内容',
        ]);

        $this->customForm = factory(CustomForm::class)->create();
    }

    /**
     * @test
     */
    public function フォーム情報がCSVでダウンロードできる()
    {
        Permission::create(['name' => 'staff.forms.export']);
        $this->staff->syncPermissions(['staff.forms.export']);

        Excel::fake();

        $this->actingAs($this->staff)
            ->withSession(['staff_authorized' => true])
            ->get(route('staff.forms.export'));

        $now = Carbon::now()->format('Y-m-d_H-i-s');

        Excel::assertDownloaded("申請一覧_{$now}.csv", function (FormsExport $export) {
            return $export->collection()->contains('name', '場所登録申請')
                && $export->collection()->contains('name', 'パンフレット掲載内容')
                && !$export->collection()->contains('name', $this->customForm->form->name);
        });
    }

    /**
     * @test
     */
    public function 権限がない場合はCSVをダウンロードできない()
    {
        $this->actingAs($this->staff)
            ->withSession(['staff_authorized' => true])
            ->get(route('staff.forms.export'))
            ->assertForbidden();
    }
}
