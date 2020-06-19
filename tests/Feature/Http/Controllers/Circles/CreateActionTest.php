<?php

namespace Tests\Feature\Http\Controllers\Circles;

use App\Eloquents\User;
use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class CreateActionTest extends BaseTestCase
{
    use RefreshDatabase;

    private $user;

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
    public function 説明が設定されていて直接アクセスされたときTermsActionにリダイレクトする()
    {
        $this->form->description = '参加登録前に読んでほしい内容';
        $this->form->save();

        $responce = $this
                    ->actingAs($this->user)
                    ->get(
                        route('circles.create')
                    );

        $responce->assertStatus(302);
        $responce->assertRedirect(route('circles.terms'));
    }

    /**
     * @test
     */
    public function 説明が設定されていてTermsActionから来たときはフォームを表示する()
    {
        $this->form->description = '参加登録前に読んでほしい内容';
        $this->form->save();

        $responce = $this
                    ->actingAs($this->user)
                    ->withSession([
                        'read_terms' => true,
                    ])
                    ->get(
                        route('circles.create')
                    );

        $responce->assertOk();
    }

    /**
     * @test
     */
    public function 説明が設定されていないときはリダイレクトせずにフォームを表示する()
    {
        $this->form->description = null;
        $this->form->save();

        $responce = $this
                    ->actingAs($this->user)
                    ->get(
                        route('circles.create')
                    );

        $responce->assertOk();
    }
}
