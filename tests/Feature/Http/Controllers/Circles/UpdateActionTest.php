<?php

namespace Tests\Feature\Http\Controllers\Circles;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\Feature\Http\Controllers\Circles\BaseTestCase;
use Tests\TestCase;
use Carbon\Carbon;
use Carbon\CarbonImmutable;
use App\Eloquents\User;
use App\Eloquents\Circle;
use App\Eloquents\Form;
use App\Eloquents\CustomForm;
use App\Eloquents\Answer;

class UpdateActionTest extends BaseTestCase
{
    use RefreshDatabase;

    private $user;
    private $circle;
    private $answer;

    public function setUp(): void
    {
        parent::setUp();

        $this->user = factory(User::class)->create();
        $this->circle = factory(Circle::class)->states('notSubmitted')->create();
        $this->answer = factory(Answer::class)->create([
            'form_id' => $this->form->id,
            'circle_id' => $this->circle->id,
        ]);

        $this->user->circles()->attach($this->circle->id, ['is_leader' => true]);

        // 受付期間内
        Carbon::setTestNow(new CarbonImmutable('2020-02-16 02:25:15'));
        CarbonImmutable::setTestNow(new CarbonImmutable('2020-02-16 02:25:15'));
    }

    /**
     * @test
     */
    public function メンバーは企画の情報を更新できる()
    {
        $this->assertDatabaseHas('circles', [
            'name' => $this->circle->name,
            'name_yomi' => $this->circle->name_yomi,
        ]);

        $data = [
            'name' => '新しい名前の企画',
            'name_yomi' => 'あたらしいなまえのきかく',
            'group_name' => '新しい団体の名前',
            'group_name_yomi' => 'あたらしいだんたいのなまえ',
        ];

        $response = $this
                    ->actingAs($this->user)
                    ->patch(
                        route('circles.update', [
                            'circle' => $this->circle,
                        ]),
                        $data
                    );

        $this->assertDatabaseHas('circles', $data);

        $response->assertStatus(302);
        $response->assertRedirect(route('circles.users.index', ['circle' => $this->circle]));
    }

    /**
     * @test
     */
    public function 部外者は企画の情報を更新できない()
    {
        $anotherUser = factory(User::class)->create();

        $data = [
            'name' => '新しい名前の企画',
            'name_yomi' => 'あたらしいなまえのきかく',
            'group_name' => '新しい団体の名前',
            'group_name_yomi' => 'あたらしいだんたいのなまえ',
        ];

        $response = $this
                    ->actingAs($anotherUser)
                    ->patch(
                        route('circles.update', [
                            'circle' => $this->circle,
                        ]),
                        $data
                    );

        $this->assertDatabaseMissing('circles', $data);

        $response->assertStatus(403);
    }

    /**
     * @test
     */
    public function 提出済みの企画の情報は更新できない()
    {
        $this->circle->submitted_at = now();
        $this->circle->save();

        $data = [
            'name' => '新しい名前の企画',
            'name_yomi' => 'あたらしいなまえのきかく',
            'group_name' => '新しい団体の名前',
            'group_name_yomi' => 'あたらしいだんたいのなまえ',
        ];

        $response = $this
                    ->actingAs($this->user)
                    ->patch(
                        route('circles.update', [
                            'circle' => $this->circle,
                        ]),
                        $data
                    );

        $this->assertDatabaseMissing('circles', $data);

        $response->assertStatus(403);
    }
}
