<?php

namespace Tests\Feature\Exports;

use App\Eloquents\Form;
use App\Eloquents\Question;
use App\Eloquents\Tag;
use App\Eloquents\User;
use App\Exports\FormsExport;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\App;
use Tests\TestCase;

class FormsExportTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @var FormsExport
     */
    private $formsExport;

    /**
     * @var User
     */
    private $user;

    /**
     * @var Form
     */
    private $form;

    /**
     * @var Tag
     */
    private $tag;

    public function setUp(): void
    {
        parent::setUp();

        $this->formsExport = App::make(FormsExport::class);

        $this->user = factory(User::class)->create();

        $this->form = factory(Form::class)->create([
            'name' => '場所登録申請',
            'created_by' => $this->user->id,
            'max_answers' => 2,
        ]);

        $this->tag = factory(Tag::class)->create([
            'name' => '屋内',
        ]);
        $this->form->answerableTags()->attach($this->tag->id);
    }

    /**
     * @test
     */
    public function map_フォーム情報のフォーマットが正常に行われる()
    {
        $this->assertEquals(
            [
                $this->form->id,
                '場所登録申請',
                $this->form->description,
                '屋内',
                $this->form->open_at,
                $this->form->close_at,
                2,
                'はい',
                $this->form->created_at,
                "{$this->user->name}(ID:{$this->user->id},{$this->user->student_id})",
                $this->form->updated_at,
            ],
            $this->formsExport->map(
                $this->form->load(['answerableTags', 'userCreatedBy'])
            )
        );
    }
}
