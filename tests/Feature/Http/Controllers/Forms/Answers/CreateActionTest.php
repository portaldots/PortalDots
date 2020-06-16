<?php

namespace Tests\Feature\Http\Controllers\Forms\Answers;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\App;
use Carbon\Carbon;
use Carbon\CarbonImmutable;
use App\Eloquents\User;
use App\Eloquents\Circle;
use App\Eloquents\Form;
use App\Eloquents\Tag;
use App\Services\Circles\SelectorService;

class CreateActionTest extends TestCase
{
    use RefreshDatabase;

    private $user;
    private $circle;
    private $form;

    /**
     * @var SelectorService
     */
    private $selectorService;

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

        $this->selectorService = App::make(SelectorService::class);
    }

    public function tearDown(): void
    {
        $this->selectorService->reset();

        parent::tearDown();
    }

    /**
     * @test
     * @dataProvider 受付期間中かどうかに応じて表示が切り替わる_provider
     */
    public function 受付期間中かどうかに応じて表示が切り替わる(
        CarbonImmutable $today,
        bool $is_answerable
    ) {
        Carbon::setTestNow($today);
        CarbonImmutable::setTestNow($today);

        $this->selectorService->setCircle($this->circle);

        $response = $this
                    ->actingAs($this->user)
                    ->get(
                        route('forms.answers.create', [
                            'form' => $this->form,
                        ])
                    );

        $response->assertStatus(200);

        if ($is_answerable) {
            $response->assertDontSee('受付期間外');
        } else {
            $response->assertSee('受付期間外');
        }
    }

    public function 受付期間中かどうかに応じて表示が切り替わる_provider()
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
    public function 非公開のフォームにはアクセスできない()
    {
        $privateForm = factory(Form::class)->states('private')->create();

        $this->selectorService->setCircle($this->circle);

        $response = $this
                    ->actingAs($this->user)
                    ->get(
                        route('forms.answers.create', [
                            'form' => $privateForm,
                        ])
                    );

        $response->assertStatus(404);
    }

    /**
     * @test
     */
    public function 回答可能なタグを持つ企画に所属している場合フォームにアクセスできる()
    {
        $tag = factory(Tag::class)->create();

        $tagged_circle = factory(Circle::class)->create();
        $tagged_circle->tags()->attach($tag->id);

        $tagged_form = factory(Form::class)->create();
        $tagged_form->answerableTags()->attach($tag->id);

        $this->user->circles()->attach($tagged_circle->id, ['is_leader' => true]);

        $this->selectorService->setCircle($tagged_circle);

        $response = $this
                    ->actingAs($this->user)
                    ->get(
                        route('forms.answers.create', [
                            'form' => $tagged_form,
                        ])
                    );

        $response->assertStatus(200);
    }

    /**
     * @test
     */
    public function 回答可能なタグを持つ企画に所属していない場合フォームにアクセスできない()
    {
        $tag = factory(Tag::class)->create();

        $tagged_circle = factory(Circle::class)->create();

        // フォームとは別にタグを企画に紐付ける
        $tagged_circle->tags()->attach(factory(Tag::class)->create());

        $tagged_form = factory(Form::class)->create();
        $tagged_form->answerableTags()->attach($tag->id);

        $this->user->circles()->attach($tagged_circle->id, ['is_leader' => true]);

        $this->selectorService->setCircle($tagged_circle);

        $response = $this
                    ->actingAs($this->user)
                    ->get(
                        route('forms.answers.create', [
                            'form' => $tagged_form,
                        ])
                    );

        $response->assertStatus(403);
    }
}
