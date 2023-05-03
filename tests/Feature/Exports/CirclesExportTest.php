<?php

namespace Tests\Feature\Exports;

use App\Eloquents\Answer;
use App\Eloquents\AnswerDetail;
use App\Eloquents\Circle;
use App\Eloquents\Form;
use App\Eloquents\ParticipationType;
use App\Eloquents\Place;
use App\Eloquents\Question;
use App\Eloquents\Tag;
use App\Eloquents\User;
use App\Exports\CirclesExport;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\App;
use Tests\TestCase;

class CirclesExportTest extends TestCase
{
    use RefreshDatabase;

    private ?Form $participationForm;
    private ?ParticipationType $participationType;
    private ?User $staff;
    private ?Circle $circle;
    private ?User $user;
    private ?User $member;
    private ?User $anotherMember;
    private ?Place $place;
    private ?Tag $tag;
    private ?Question $question;
    private ?Answer $answer;
    private ?AnswerDetail $answerDetail;
    private ?CirclesExport $circlesExport;

    public function setUp(): void
    {
        parent::setUp();

        $this->participationForm = factory(Form::class)->create();
        $this->participationType = ParticipationType::factory()->create([
            'name' => '体験企画',
            'description' => '',
            'users_count_min' => 3,
            'users_count_max' => 3,
            'form_id' => $this->participationForm->id,
        ]);
        $this->staff = factory(User::class)->create([
            'name' => '企画 チェック',
            'student_id' => '9999999',
        ]);
        $this->circle = factory(Circle::class)->create([
            'participation_type_id' => $this->participationType->id,
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
        factory(Question::class)->create([
            'form_id' => $this->participationForm->id,
            'name' => '見出しですよ',
            'type' => 'heading',
            'priority' => 1,
        ]);
        $this->question = factory(Question::class)->create([
            'form_id' => $this->participationForm->id,
            'name' => 'どんなことをしますか',
            'type' => 'text',
            'priority' => 2,
        ]);

        $this->answer = factory(Answer::class)->create([
            'form_id' => $this->participationForm->id,
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
                "体験企画(ID:{$this->participationType->id})",
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
                '参加種別',
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
