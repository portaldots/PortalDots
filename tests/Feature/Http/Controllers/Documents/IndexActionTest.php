<?php

namespace Tests\Feature\Http\Controllers\Documents;

use App\Eloquents\Circle;
use App\Eloquents\Document;
use App\Eloquents\Tag;
use App\Eloquents\User;
use App\Services\Circles\SelectorService;
use App\Services\Documents\DocumentApprovalsService;
use App\Services\Documents\DocumentsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class IndexActionTest extends TestCase
{
    use RefreshDatabase;

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
    public function 非公開の配布資料は一覧に表示されない()
    {
        $publicDocumentName = '公開されている配布資料';
        $privateDocumentName = '非公開の配布資料';

        factory(Document::class)->create([
            'name' => $publicDocumentName,
            'is_public' => true,
        ]);
        factory(Document::class)->create([
            'name' => $privateDocumentName,
            'is_public' => false,
        ]);

        $response = $this->get(route('documents.index'));

        $response->assertSee($publicDocumentName);
        $response->assertDontSee($privateDocumentName);
    }

    /**
     * @return array 公開範囲・閲覧者の組み合わせと、一覧に表示できるかどうか
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
    public function 公開範囲による表示切り替え(string $audience, string $viewerType, bool $canSee)
    {
        $documentName = '公開範囲のテスト対象配布資料';

        $document = factory(Document::class)->create([
            'name' => $documentName,
            'is_public' => true,
            'audience' => $audience,
        ]);

        if ($audience === 'selected') {
            $document->viewableTags()->attach($this->tag->id);
            $document->viewableCircles()->attach($this->circleSelected->id);
        }

        $response = $this->actingAsViewer($viewerType)->get(route('documents.index'));

        if ($canSee) {
            $response->assertSee($documentName);
        } else {
            $response->assertDontSee($documentName);
        }
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function 複数版がある配布資料には版番号と過去の版へのリンクが表示される()
    {
        Storage::fake('local');

        $documentsService = App::make(DocumentsService::class);
        $document = $documentsService->createDocument(
            '複数版の配布資料',
            null,
            UploadedFile::fake()->create('第1版.pdf', 1, 'application/pdf'),
            true,
            false,
            null
        );
        $documentsService->updateDocument(
            $document,
            '複数版の配布資料',
            null,
            UploadedFile::fake()->create('第2版.pdf', 1, 'application/pdf'),
            true,
            false,
            null
        );

        $response = $this->get(route('documents.index'));

        $response->assertSee('第2版');
        $response->assertSee('第1版');
        $response->assertSee(route('documents.versions.show', [
            'document' => $document,
            'version' => $document->versions()->where('version', 1)->firstOrFail(),
        ]), false);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function 自分の企画への確認依頼があればバッジが表示される()
    {
        Storage::fake('local');

        $documentsService = App::make(DocumentsService::class);
        $document = $documentsService->createDocument(
            '確認依頼のある配布資料',
            null,
            UploadedFile::fake()->create('第1版.pdf', 1, 'application/pdf'),
            true,
            false,
            null
        );
        App::make(DocumentApprovalsService::class)
            ->requestForCircles($document, [$this->circleSelected->id], $this->circleSelectedUser);

        $this->selectorService->setCircle($this->circleSelected);

        $response = $this->actingAs($this->circleSelectedUser)
            ->get(route('documents.index'));

        $response->assertSee('確認してください');
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function 別の企画への確認依頼のバッジは表示されない()
    {
        Storage::fake('local');

        $documentsService = App::make(DocumentsService::class);
        $document = $documentsService->createDocument(
            '確認依頼のある配布資料',
            null,
            UploadedFile::fake()->create('第1版.pdf', 1, 'application/pdf'),
            true,
            false,
            null
        );
        App::make(DocumentApprovalsService::class)
            ->requestForCircles($document, [$this->circleUnrelated->id], $this->circleUnrelatedUser);

        $this->selectorService->setCircle($this->circleSelected);

        $response = $this->actingAs($this->circleSelectedUser)
            ->get(route('documents.index'));

        $response->assertDontSee('確認してください');
    }
}
