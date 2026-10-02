<?php

namespace Tests\Feature\Database\Migrations;

use App\Eloquents\Document;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class DocumentVersionsMigrationTest extends TestCase
{
    use RefreshDatabase;

    #[\PHPUnit\Framework\Attributes\Test]
    public function 既存の配布資料はそれぞれ第1版として引き継がれる()
    {
        $document = factory(Document::class)->create([
            'path' => 'documents/existing.pdf',
            'size' => 123,
            'extension' => 'pdf',
        ]);

        // 既存データが入った状態でマイグレーションを再実行し、移行結果を確認する
        // document_approvals が document_versions を外部キー参照しているため、
        // テーブルの再作成中は一時的に外部キー制約を無効化する
        $migration = require database_path('migrations/2026_09_28_000000_create_document_versions_table.php');
        Schema::disableForeignKeyConstraints();
        $migration->down();
        $migration->up();
        Schema::enableForeignKeyConstraints();

        $this->assertSame(1, $document->versions()->count());

        $version = $document->versions()->first();
        $this->assertSame(1, $version->version);
        $this->assertSame('documents/existing.pdf', $version->path);
        $this->assertSame(123, $version->size);
        $this->assertSame('pdf', $version->extension);
        $this->assertNull($version->uploaded_by);
    }
}
