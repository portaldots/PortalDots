<?php

namespace Tests\Feature\Http\Controllers\Forms\Answers;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Carbon\Carbon;
use Carbon\CarbonImmutable;
use App\Eloquents\User;
use App\Eloquents\Circle;
use App\Eloquents\Form;

class StoreActionTest extends TestCase
{
    use RefreshDatabase;

    private $user;
    private $circle;
    private $form;

    public function setUp(): void
    {
        parent::setUp();

        $this->user = factory(User::class)->create();
        $this->circle = factory(Circle::class)->create();
        $this->form = factory(Form::class)->create([
            'open_at' => new CarbonImmutable('2020-01-26 11:42:51'),
            'close_at' => new CarbonImmutable('2020-03-26 15:23:31'),
        ]);

        $this->user->circles()->attach($this->circle->id, ['is_leader' => true]);
    }

    /**
     * @test
     * @dataProvider 受付期間中かどうかに応じてリクエストを許可する_provider
     */
    public function 受付期間中かどうかに応じてリクエストを許可する(
        CarbonImmutable $today,
        bool $is_answerable
    ) {
        Carbon::setTestNow($today);
        CarbonImmutable::setTestNow($today);

        $response = $this
                    ->actingAs($this->user)
                    ->post(
                        route('forms.answers.store', [
                            'form' => $this->form,
                        ]),
                        [
                            'circle_id' => $this->circle->id,
                        ]
                    );

        if ($is_answerable) {
            $this->assertDatabaseHas('answers', [
                'form_id' => $this->form->id,
                'circle_id' => $this->circle->id,
            ]);
            // バリデーションエラーがなければ編集画面へリダイレクトする
            $response->assertStatus(302);
        } else {
            $this->assertDatabaseMissing('answers', [
                'form_id' => $this->form->id,
                'circle_id' => $this->circle->id,
            ]);
            $response->assertStatus(403);
        }
    }

    public function 受付期間中かどうかに応じてリクエストを許可する_provider()
    {
        return [
            '受付開始はまだまだ先' => [new CarbonImmutable('2019-12-25 23:42:22'), false],
            '受付開始前' => [new CarbonImmutable('2020-01-26 11:42:50'), false],
            '受付開始した瞬間' => [new CarbonImmutable('2020-01-26 11:42:51'), true],
            '受付期間中' => [new CarbonImmutable('2020-02-16 02:25:15'), true],
            '受付終了する瞬間' => [new CarbonImmutable('2020-03-26 15:23:31'), true],
            '受付終了後' => [new CarbonImmutable('2020-03-26 15:23:32'), false],
            '受付終了してだいぶ経過' => [new CarbonImmutable('2020-08-14 02:35:31'), false],
        ];
    }

    /**
     * @test
     */
    public function 非公開のフォームには回答できない()
    {
        Carbon::setTestNow(new CarbonImmutable('2020-02-16 02:25:15'));
        CarbonImmutable::setTestNow(new CarbonImmutable('2020-02-16 02:25:15'));

        $privateForm = factory(Form::class)->states('private')->create();

        $response = $this
                    ->actingAs($this->user)
                    ->post(
                        route('forms.answers.store', [
                            'form' => $privateForm,
                        ]),
                        [
                            'circle_id' => $this->circle->id,
                        ]
                    );

        $this->assertDatabaseMissing('answers', [
            'form_id' => $privateForm->id,
            'circle_id' => $this->circle->id,
        ]);

        $response->assertStatus(403);
    }

    /**
     * @test
     */
    public function 他企画に成り済ました回答はできない()
    {
        Carbon::setTestNow(new CarbonImmutable('2020-02-16 02:25:15'));
        CarbonImmutable::setTestNow(new CarbonImmutable('2020-02-16 02:25:15'));

        $anotherCircle = factory(Circle::class)->create();

        $response = $this
                    ->actingAs($this->user)
                    ->post(
                        route('forms.answers.store', [
                            'form' => $this->form,
                        ]),
                        [
                            'circle_id' => $anotherCircle->id,
                        ]
                    );

        $this->assertDatabaseMissing('answers', [
            'form_id' => $this->form->id,
            'circle_id' => $anotherCircle->id,
        ]);

        $response->assertStatus(403);
    }

    /**
     * @test
     */
    public function 参加登録が不受理となった企画は回答できない()
    {
        Carbon::setTestNow(new CarbonImmutable('2020-02-16 02:25:15'));
        CarbonImmutable::setTestNow(new CarbonImmutable('2020-02-16 02:25:15'));

        $rejectedCircle = factory(Circle::class)->states('rejected')->create();
        $this->user->circles()->attach($rejectedCircle->id, ['is_leader' => true]);

        $response = $this
                    ->actingAs($this->user)
                    ->post(
                        route('forms.answers.store', [
                            'form' => $this->form,
                        ]),
                        [
                            'circle_id' => $rejectedCircle->id,
                        ]
                    );

        $this->assertDatabaseMissing('answers', [
            'form_id' => $this->form->id,
            'circle_id' => $rejectedCircle->id,
        ]);

        $response->assertStatus(404);
    }
}
