<?php

namespace Tests\Feature\Http\Controllers\Documents;

use App\Eloquents\Circle;
use App\Eloquents\Tag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use App\Services\Circles\SelectorService;
use Illuminate\Support\Facades\App;
use Tests\TestCase;
use App\Eloquents\Document;
use App\Eloquents\User;

class ShowActionTest extends TestCase
{
    use RefreshDatabase;

    private $document;
    private $user;

    /**
     * @var SelectorService
     */
    private $selectorService;

    private $tag;
    private $circleWithTag;
    private $circleWithTagUser;
    private $circleSelected;
    private $circleSelectedUser;
    private $circleUnrelated;
    private $circleUnrelatedUser;
    private $userWithoutCircle;

    public function setUp(): void
    {
        parent::setUp();

        Storage::fake('local');

        // 配布資料
        $file = UploadedFile::fake()->create('ファイル.pdf', 1);
        $this->document = factory(Document::class)->create([
            'path' => $file->store('documents'),
            'size' => $file->getSize(),
            'extension' => $file->getClientOriginalExtension(),
        ]);

        // ユーザー
        $this->user = factory(User::class)->create();

        $this->selectorService = App::make(SelectorService::class);

        $this->tag = factory(Tag::class)->create();

        $this->circleWithTag = factory(Circle::class)->create();
        $this->circleWithTag->tags()->attach($this->tag->id);
        $this->circleWithTagUser = factory(User::class)->create();
        $this->circleWithTagUser->circles()->attach($this->circleWithTag->id, ['is_leader' => true]);

        $this->circleSelected = factory(Circle::class)->create();
        $this->circleSelectedUser = factory(User::class)->create();
        $this->circleSelectedUser->circles()->attach($this->circleSelected->id, ['is_leader' => true]);

        $this->circleUnrelated = factory(Circle::class)->create();
        $this->circleUnrelatedUser = factory(User::class)->create();
        $this->circleUnrelatedUser->circles()->attach($this->circleUnrelated->id, ['is_leader' => true]);

        $this->userWithoutCircle = factory(User::class)->create();
    }

    /**
     * @param string $viewerType
     * @return $this
     */
    private function actingAsViewer(string $viewerType)
    {
        switch ($viewerType) {
            case 'guest':
                return $this;
            case 'no_circle':
                return $this->actingAs($this->userWithoutCircle);
            case 'matching_tag':
                $this->selectorService->setCircle($this->circleWithTag);
                return $this->actingAs($this->circleWithTagUser);
            case 'selected_circle':
                $this->selectorService->setCircle($this->circleSelected);
                return $this->actingAs($this->circleSelectedUser);
            case 'unrelated_circle':
                $this->selectorService->setCircle($this->circleUnrelated);
                return $this->actingAs($this->circleUnrelatedUser);
        }
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function ダウンロードできる()
    {
        $response = $this->actingAs($this->user)
            ->get(route('documents.show', [
                'document' => $this->document
            ]));

        $response->assertOk();
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function 非公開の場合はダウンロードできない()
    {
        $this->document->is_public = false;
        $this->document->save();

        $response = $this->actingAs($this->user)
            ->get(route('documents.show', [
                'document' => $this->document
            ]));

        $response->assertStatus(404);
    }

    /**
     * @return array 公開範囲・閲覧者の組み合わせと、ダウンロードできるかどうか
     */
    public static function 公開範囲による表示切り替え_provider()
    {
        return [
            'everyone・ゲスト' => ['everyone', 'guest', true],
            'everyone・企画未選択のユーザー' => ['everyone', 'no_circle', true],
            'everyone・該当タグの企画' => ['everyone', 'matching_tag', true],
            'everyone・選択された企画' => ['everyone', 'selected_circle', true],
            'everyone・関係ない企画' => ['everyone', 'unrelated_circle', true],
            'signed_in・ゲスト' => ['signed_in', 'guest', false],
            'signed_in・企画未選択のユーザー' => ['signed_in', 'no_circle', true],
            'signed_in・該当タグの企画' => ['signed_in', 'matching_tag', true],
            'signed_in・選択された企画' => ['signed_in', 'selected_circle', true],
            'signed_in・関係ない企画' => ['signed_in', 'unrelated_circle', true],
            'selected・ゲスト' => ['selected', 'guest', false],
            'selected・企画未選択のユーザー' => ['selected', 'no_circle', false],
            'selected・該当タグの企画' => ['selected', 'matching_tag', true],
            'selected・選択された企画' => ['selected', 'selected_circle', true],
            'selected・関係ない企画' => ['selected', 'unrelated_circle', false],
        ];
    }

    #[\PHPUnit\Framework\Attributes\Test]
    #[\PHPUnit\Framework\Attributes\DataProvider("公開範囲による表示切り替え_provider")]
    public function 公開範囲による表示切り替え(string $audience, string $viewerType, bool $canDownload)
    {
        $this->document->audience = $audience;
        $this->document->save();

        if ($audience === 'selected') {
            $this->document->viewableTags()->attach($this->tag->id);
            $this->document->viewableCircles()->attach($this->circleSelected->id);
        }

        $response = $this->actingAsViewer($viewerType)
            ->get(route('documents.show', ['document' => $this->document]));

        if ($canDownload) {
            $response->assertOk();
        } else {
            $response->assertStatus(404);
        }
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function 公開範囲を変更するとアクセスできなくなったユーザーはすぐにダウンロードできなくなる()
    {
        $this->actingAsViewer('unrelated_circle')
            ->get(route('documents.show', ['document' => $this->document]))
            ->assertOk();

        $this->document->audience = 'selected';
        $this->document->save();
        $this->document->viewableCircles()->attach($this->circleSelected->id);

        $this->actingAsViewer('unrelated_circle')
            ->get(route('documents.show', ['document' => $this->document]))
            ->assertStatus(404);
    }
}
