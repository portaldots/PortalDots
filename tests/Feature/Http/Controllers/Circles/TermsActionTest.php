<?php

namespace Tests\Feature\Http\Controllers\Circles;

use App\Eloquents\CustomForm;
use App\Eloquents\User;
use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class TermsActionTest extends BaseTestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();
        $this->user = factory(User::class)->create();

        // 受付期間内
        Carbon::setTestNow(new CarbonImmutable('2020-02-16 02:25:15'));
        CarbonImmutable::setTestNow(new CarbonImmutable('2020-02-16 02:25:15'));
    }

    /**
     * @test
     */
    public function 説明があるときViewを表示する()
    {
        $form = CustomForm::getFormByType('circle');
        $form->description = '参加登録前に読んでほしい内容';
        $form->save();

        $responce = $this
                    ->actingAs($this->user)
                    ->get(
                        route('circles.terms')
                    );

        $responce->assertOk();
        $responce->assertSee('参加登録前に読んでほしい内容');
    }

    /**
     * @test
     */
    public function 説明がないときCreateActionにリダイレクトする()
    {
        $form = CustomForm::getFormByType('circle');
        $form->description = '';
        $form->save();

        $responce = $this
                    ->actingAs($this->user)
                    ->get(
                        route('circles.terms')
                    );

        $responce->assertStatus(302);
        $responce->assertRedirect(route('circles.create'));
    }
}
