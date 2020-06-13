<?php

namespace Tests\Feature\Http\Controllers\Documents;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use App\Eloquents\Document;
use App\Eloquents\User;

class ShowActionTest extends TestCase
{
    use RefreshDatabase;

    private $document;
    private $user;

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
    }

    /**
     * @test
     */
    public function ダウンロードできる()
    {
        $response = $this->actingAs($this->user)
            ->get(route('documents.show', [
                'document' => $this->document
            ]));

        $response->assertOk();
    }

    /**
     * @test
     */
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
     * @test
     */
    public function 未ログイン状態ではダウンロードできない()
    {
        $response = $this->get(route('documents.show', [
                'document' => $this->document
            ]));

        $response->assertStatus(302);
    }
}
