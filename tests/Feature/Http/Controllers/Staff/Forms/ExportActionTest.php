<?php

namespace Tests\Feature\Http\Controllers\Staff\Forms;

use App\Eloquents\CustomForm;
use App\Eloquents\Form;
use App\Eloquents\User;
use App\Exports\FormsExport;
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
        Carbon::setTestNow(new CarbonImmutable('2021-09-14 21:22:23'));
        CarbonImmutable::setTestNow(new CarbonImmutable('2021-09-14 21:22:23'));

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
        Excel::fake();

        $this->actingAs($this->staff)
            ->withSession(['staff_authorized' => true])
            ->get(route('staff.forms.export'));

        $now = Carbon::now()->format('Y-m-d_H-i-s');

        Excel::assertDownloaded("forms_{$now}.csv", function (FormsExport $export) {
            return $export->collection()->contains('name', '場所登録申請')
                && $export->collection()->contains('name', 'パンフレット掲載内容')
                && !$export->collection()->contains('name', $this->customForm->form->name);
        });
    }
}
