<?php

namespace Tests\Feature\Http\Controllers\Circles;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\Feature\Http\Controllers\Circles\BaseTestCase;
use Carbon\Carbon;
use Carbon\CarbonImmutable;
use App\Eloquents\User;
use App\Eloquents\Circle;
use App\Eloquents\Form;
use App\Eloquents\CustomForm;
use Config;

class SubmitActionTest extends BaseTestCase
{
    use RefreshDatabase;

    private $user;
    private $circle;

    public function setUp(): void
    {
        parent::setUp();

        $this->user = factory(User::class)->create();
        $this->circle = factory(Circle::class)->states('notSubmitted')->create();

        $this->user->circles()->attach($this->circle->id, ['is_leader' => true]);

        // 明示的に設定しない限り、企画には1人所属していれば参加登録を提出できるものとする
        Config::set('portal.users_number_to_submit_circle', 1);
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
                        route('circles.submit', [
                            'circle' => $this->circle,
                        ])
                    );

        $this->circle->refresh();

        if ($is_answerable) {
            $this->assertNotNull($this->circle->submitted_at);
            // トップページへリダイレクトする
            $response->assertStatus(302);
            $response->assertSessionHas('topAlert.title');
            $response->assertRedirect(route('home'));
        } else {
            $this->assertNull($this->circle->submitted_at);
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
    public function 企画メンバーが規定の人数に達していない場合はは参加登録の提出はできない()
    {
        // 規定の人数 = 2
        Config::set('portal.users_number_to_submit_circle', 2);

        // 受付期間内
        Carbon::setTestNow(new CarbonImmutable('2020-02-16 02:25:15'));
        CarbonImmutable::setTestNow(new CarbonImmutable('2020-02-16 02:25:15'));

        // 企画には1名しか所属していない状態で参加登録を提出しようとする
        $response = $this
                    ->actingAs($this->user)
                    ->post(
                        route('circles.submit', [
                            'circle' => $this->circle,
                        ])
                    );

        $this->circle->refresh();
        $this->assertNull($this->circle->submitted_at);

        // メンバー招待のページへリダイレクトされ、topAlert でエラーが表示される
        $response->assertStatus(302);
        $response->assertSessionHas('topAlert.title');
        $response->assertRedirect(route('circles.users.index', ['circle' => $this->circle]));
    }

    /**
     * @test
     */
    public function 参加登録機能が非公開のときは提出できない()
    {
        Carbon::setTestNow(new CarbonImmutable('2020-02-16 02:25:15'));
        CarbonImmutable::setTestNow(new CarbonImmutable('2020-02-16 02:25:15'));

        $form = CustomForm::getFormByType('circle');
        $form->is_public = false;
        $form->save();

        $response = $this
                    ->actingAs($this->user)
                    ->post(
                        route('circles.submit', [
                            'circle' => $this->circle,
                        ])
                    );

        $this->circle->refresh();
        $this->assertNull($this->circle->submitted_at);

        $response->assertStatus(403);
    }

    /**
     * @test
     */
    public function 他企画に成り済ました回答はできない()
    {
        Carbon::setTestNow(new CarbonImmutable('2020-02-16 02:25:15'));
        CarbonImmutable::setTestNow(new CarbonImmutable('2020-02-16 02:25:15'));

        $anotherCircle = factory(Circle::class)->states('notSubmitted')->create();

        $response = $this
                    ->actingAs($this->user)
                    ->post(
                        route('circles.submit', [
                            'circle' => $anotherCircle,
                        ])
                    );

        $anotherCircle->refresh();
        $this->assertNull($anotherCircle->submitted_at);

        $response->assertStatus(403);
    }
}
