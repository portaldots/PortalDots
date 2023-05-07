<?php

namespace Tests\Feature\Http\Controllers\Circles;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Http\Controllers\Circles\BaseTestCase;
use Carbon\Carbon;
use Carbon\CarbonImmutable;
use App\Eloquents\User;
use App\Eloquents\Circle;

class SubmitActionTest extends BaseTestCase
{
    use RefreshDatabase;

    private $user;
    private $circle;

    private const CIRCLE_LAST_UPDATED_TIMESTAMP = 1672531200;

    public function setUp(): void
    {
        parent::setUp();

        $this->user = factory(User::class)->create();
        $this->circle = factory(Circle::class)->states('notSubmitted')->create([
            'participation_type_id' => $this->participationType->id,
        ]);

        $this->user->circles()->attach($this->circle->id, ['is_leader' => true]);

        // 明示的に設定しない限り、企画には1人所属していれば参加登録を提出できるものとする
        $this->participationType->update(['users_count_min' => 1]);

        // CIRCLE_LAST_UPDATED_TIMESTAMPと同じ日時をセット
        $this->circle->timestamps = false;
        $this->circle->created_at = Carbon::createFromTimestamp(self::CIRCLE_LAST_UPDATED_TIMESTAMP, 'UTC');
        $this->circle->updated_at = Carbon::createFromTimestamp(self::CIRCLE_LAST_UPDATED_TIMESTAMP, 'UTC');
        $this->circle->save();
    }

    /**
     * @test
     * @dataProvider 受付期間中かどうかに応じてリクエストを許可する_provider
     */
    public function 受付期間中かどうかに応じてリクエストを許可する(
        CarbonImmutable $today,
        bool $is_answerable
    ) {
        Carbon::setTestNowAndTimezone($today);
        CarbonImmutable::setTestNowAndTimezone($today);

        $response = $this
            ->actingAs($this->user)
            ->post(
                route('circles.submit', [
                    'circle' => $this->circle,
                ]),
                [
                    'last_updated_timestamp' => (string)self::CIRCLE_LAST_UPDATED_TIMESTAMP,
                ]
            );


        $this->circle->refresh();

        if ($is_answerable) {
            $this->assertNotNull($this->circle->submitted_at);
            // 完了画面へリダイレクトする
            $response->assertStatus(302);
            $response->assertSessionHas('done');
            $response->assertRedirect(route('circles.done', ['circle' => $this->circle]));
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
    public function 企画メンバーが規定の人数に達していない場合は参加登録の提出はできない()
    {
        // 規定の人数 = 2
        $this->participationType->update(['users_count_min' => 2]);

        // 受付期間内
        Carbon::setTestNowAndTimezone(new CarbonImmutable('2020-02-16 02:25:15'));
        CarbonImmutable::setTestNowAndTimezone(new CarbonImmutable('2020-02-16 02:25:15'));

        // 企画には1名しか所属していない状態で参加登録を提出しようとする
        $response = $this
            ->actingAs($this->user)
            ->post(
                route('circles.submit', [
                    'circle' => $this->circle,
                ]),
                [
                    // FIXME: このテストを実行するときだけSubmitActionのCircleのupdated_atのタイムゾーンがGMT+9になる（原因不明）ため、UTCの時間にする
                    'last_updated_timestamp' => (string)($this->circle->updated_at->timestamp - 60 * 60 * 9),
                ]
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
    public function 企画参加登録の提出時時点の企画の更新日時がデータベースと一致しない場合は参加登録の提出はできない()
    {
        // 受付期間内
        Carbon::setTestNowAndTimezone(new CarbonImmutable('2020-02-16 02:25:15'));
        CarbonImmutable::setTestNowAndTimezone(new CarbonImmutable('2020-02-16 02:25:15'));

        $response = $this
            ->actingAs($this->user)
            ->post(
                route('circles.submit', [
                    'circle' => $this->circle,
                ]),
                [
                    'last_updated_timestamp' =>
                    (string)(self::CIRCLE_LAST_UPDATED_TIMESTAMP + 15),
                ]
            );

        $this->circle->refresh();
        $this->assertNull($this->circle->submitted_at);

        // 参加登録提出のページへリダイレクトされ、topAlert でエラーが表示される
        $response->assertStatus(302);
        $response->assertSessionHas('topAlert.title');
        $response->assertRedirect(route('circles.confirm', ['circle' => $this->circle]));
    }

    /**
     * @test
     */
    public function 参加登録機能が非公開のときは提出できない()
    {
        Carbon::setTestNowAndTimezone(new CarbonImmutable('2020-02-16 02:25:15'));
        CarbonImmutable::setTestNowAndTimezone(new CarbonImmutable('2020-02-16 02:25:15'));

        $this->participationForm->is_public = false;
        $this->participationForm->save();

        $response = $this
            ->actingAs($this->user)
            ->post(
                route('circles.submit', [
                    'circle' => $this->circle,
                ]),
                [
                    'last_updated_timestamp' => (string)self::CIRCLE_LAST_UPDATED_TIMESTAMP,
                ]
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
        Carbon::setTestNowAndTimezone(new CarbonImmutable('2020-02-16 02:25:15'));
        CarbonImmutable::setTestNowAndTimezone(new CarbonImmutable('2020-02-16 02:25:15'));

        $anotherCircle = factory(Circle::class)->states('notSubmitted')->create([
            'participation_type_id' => $this->participationType->id
        ]);

        $response = $this
            ->actingAs($this->user)
            ->post(
                route('circles.submit', [
                    'circle' => $anotherCircle,
                ]),
                [
                    'last_updated_timestamp' => (string)$anotherCircle->updated_at->timestamp,
                ]
            );

        $anotherCircle->refresh();
        $this->assertNull($anotherCircle->submitted_at);

        $response->assertStatus(403);
    }

    /**
     * @test
     */
    public function 副責任者は企画を提出できない()
    {
        $member = factory(User::class)->create();
        $member->circles()->attach($this->circle->id, ['is_leader' => false]);

        // 受付期間内
        Carbon::setTestNowAndTimezone(new CarbonImmutable('2020-02-16 02:25:15'));
        CarbonImmutable::setTestNowAndTimezone(new CarbonImmutable('2020-02-16 02:25:15'));

        $responce = $this
            ->actingAs($member)
            ->post(
                route('circles.submit', [
                    'circle' => $this->circle,
                ]),
                [
                    'last_updated_timestamp' => (string)self::CIRCLE_LAST_UPDATED_TIMESTAMP,
                ]
            );

        $this->circle->refresh();
        $this->assertNull($this->circle->submitted_at);

        $responce->assertStatus(403);
    }
}
