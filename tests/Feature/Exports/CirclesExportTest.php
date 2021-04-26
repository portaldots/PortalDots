<?php

namespace Tests\Feature\Exports;

use App\Eloquents\Answer;
use App\Eloquents\AnswerDetail;
use App\Eloquents\Circle;
use App\Eloquents\CustomForm;
use App\Eloquents\Place;
use App\Eloquents\Question;
use App\Eloquents\Tag;
use App\Eloquents\User;
use App\Exports\CirclesExport;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\App;
use Tests\TestCase;

class CirclesExportTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @var User
     */
    private $staff;

    /**
     * @var Circle
     */
    private $circle;

    /**
     * @var User
     */
    private $user;

    /**
     * @var User
     */
    private $member;

    /**
     * @var User
     */
    private $anotherMember;

    /**
     * @var Place
     */
    private $place;

    /**
     * @var Tag
     */
    private $tag;

    /**
     * @var CustomForm
     */
    private $customForm;

    /**
     * @var Question
     */
    private $question;

    /**
     * @var Answer
     */
    private $answer;

    /**
     * @var AnswerDetail
     */
    private $answerDetail;

    /**
     * @var CirclesExport
     */
    private $circlesExport;

    public function setUp(): void
    {
        parent::setUp();
        $this->staff = factory(User::class)->create([
            'name' => '企画 チェック',
            'student_id' => '9999999',
        ]);
        $this->circle = factory(Circle::class)->create([
            'name' => '運河遊覧船',
            'name_yomi' => 'うんがゆうらんせん',
            'group_name' => '造船同好会',
            'group_name_yomi' => 'ぞうせんどうこうかい',
            'notes' => '川の案内をするらしい',
            'status_set_by' => $this->staff->id,
        ]);
        $this->user = factory(User::class)->create([
            'name' => '企画 偉い人',
            'student_id' => '0123abc',
        ]);
        $this->member = factory(User::class)->create([
            'name' => '企画 運営',
            'student_id' => '7890xyz',
        ]);
        $this->anotherMember = factory(User::class)->create([
            'name' => '企画 手伝い',
            'student_id' => '123123',
        ]);
        $this->place = factory(Place::class)->create([
            'name' => '近くの川',
        ]);
        $this->tag = factory(Tag::class)->create([
            'name' => '特殊な企画'
        ]);

        $this->customForm = factory(CustomForm::class)->create();
        $this->question = factory(Question::class)->create([
            'form_id' => $this->customForm->form->id,
            'name' => 'どんなことをしますか',
        ]);

        $this->answer = factory(Answer::class)->create([
            'form_id' => $this->customForm->form->id,
            'circle_id' => $this->circle->id,
        ]);
        $this->answerDetail = factory(AnswerDetail::class)->create([
            'answer_id' => $this->answer->id,
            'question_id' => $this->question->id,
            'answer' => '作った船で川を渡ります',
        ]);

        $this->user->circles()->attach($this->circle->id, ['is_leader' => true]);
        $this->member->circles()->attach($this->circle->id);
        $this->anotherMember->circles()->attach($this->circle->id);

        $this->place->circles()->attach($this->circle->id);
        $this->tag->circles()->attach($this->circle->id);

        $this->circlesExport = App::make(CirclesExport::class);
    }

    /**
     * @test
     */
    public function map_企画情報のフォーマットが正常に行われる()
    {
        $this->assertEquals(
            [
                $this->circle->id,
                '運河遊覧船',
                'うんがゆうらんせん',
                '造船同好会',
                'ぞうせんどうこうかい',
                '近くの川',
                '特殊な企画',
                $this->circle->submitted_at,
                '受理',
                $this->circle->status_set_at,
                "企画 チェック(ID:{$this->staff->id},9999999)",
                $this->circle->created_at,
                $this->circle->updated_at,
                '川の案内をするらしい',
                "企画 偉い人(ID:{$this->user->id},0123ABC)",
                "企画 運営(ID:{$this->member->id},7890XYZ),企画 手伝い(ID:{$this->anotherMember->id},123123)",
                '作った船で川を渡ります',
            ],
            $this->circlesExport->map($this->circle)
        );
    }

    /**
     * @test
     */
    public function headings_カスタムフォームからヘッダーが作成される()
    {
        $this->assertEquals(
            [
                '企画ID',
                '企画名',
                '企画名（よみ）',
                '企画を出店する団体の名称',
                '企画を出店する団体の名称（よみ）',
                '使用場所',
                'タグ',
                '参加登録提出日時',
                '登録受理状況',
                '登録受理状況設定日時',
                '登録受理状況設定ユーザー',
                '作成日時',
                '更新日時',
                'スタッフ用メモ',
                '責任者',
                '学園祭係',
                'どんなことをしますか',
            ],
            $this->circlesExport->headings()
        );
    }
}
