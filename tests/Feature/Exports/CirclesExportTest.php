<?php

namespace Tests\Feature\Exports;

use App\Eloquents\Circle;
use App\Eloquents\Place;
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
     * @var CirclesExport
     */
    private $circlesExport;

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

    public function setUp(): void
    {
        parent::setUp();
        $this->circlesExport = App::make(CirclesExport::class);

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

        $this->user->circles()->attach($this->circle->id, ['is_leader' => true]);
        $this->member->circles()->attach($this->circle->id);
        $this->anotherMember->circles()->attach($this->circle->id);

        $this->place->circles()->attach($this->circle->id);
        $this->tag->circles()->attach($this->circle->id);
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
            ],
            $this->circlesExport->map($this->circle)
        );
    }
}
