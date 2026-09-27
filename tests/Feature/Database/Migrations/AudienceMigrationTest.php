<?php

namespace Tests\Feature\Database\Migrations;

use App\Eloquents\Circle;
use App\Eloquents\Document;
use App\Eloquents\Form;
use App\Eloquents\Page;
use App\Eloquents\Tag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AudienceMigrationTest extends TestCase
{
    use RefreshDatabase;

    #[\PHPUnit\Framework\Attributes\Test]
    public function タグが設定されているお知らせはselectedへ移行され同じ企画から引き続き閲覧できる()
    {
        $tag = factory(Tag::class)->create();

        $pageWithTag = factory(Page::class)->create();
        DB::table('page_viewable_tags')->insert([
            'page_id' => $pageWithTag->id,
            'tag_id' => $tag->id,
        ]);

        $pageWithoutTag = factory(Page::class)->create();

        // 既存データが入った状態でマイグレーションを再実行し、移行結果を確認する
        $migration = require database_path('migrations/2026_09_27_010000_add_audience_column_to_pages_table.php');
        $migration->down();
        $migration->up();

        $this->assertSame('selected', $pageWithTag->fresh()->audience);
        $this->assertSame('everyone', $pageWithoutTag->fresh()->audience);

        $circle = factory(Circle::class)->create();
        $circle->tags()->attach($tag->id);

        $this->assertTrue(
            Page::whereKey($pageWithTag->id)->visibleTo(null, $circle)->exists()
        );
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function 既存の配布資料はゲストから引き続き閲覧できる()
    {
        $document = factory(Document::class)->create([
            'is_public' => true,
        ]);

        $migration = require database_path('migrations/2026_09_27_010100_add_audience_column_to_documents_table.php');
        $migration->down();
        $migration->up();

        $this->assertSame('everyone', $document->fresh()->audience);
        $this->assertTrue(
            Document::whereKey($document->id)->visibleTo(null, null)->exists()
        );
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function タグが設定されているフォームはselectedへ移行され同じ企画から引き続き回答できる()
    {
        $tag = factory(Tag::class)->create();

        $formWithTag = factory(Form::class)->create();
        DB::table('form_answerable_tags')->insert([
            'form_id' => $formWithTag->id,
            'tag_id' => $tag->id,
        ]);

        $formWithoutTag = factory(Form::class)->create();

        // 既存データが入った状態でマイグレーションを再実行し、移行結果を確認する
        $migration = require database_path('migrations/2026_09_28_020000_add_audience_column_to_forms_table.php');
        $migration->down();
        $migration->up();

        $this->assertSame('selected', $formWithTag->fresh()->audience);
        $this->assertSame('everyone', $formWithoutTag->fresh()->audience);

        $circle = factory(Circle::class)->create();
        $circle->tags()->attach($tag->id);

        $this->assertTrue(
            Form::whereKey($formWithTag->id)->byCircle($circle)->exists()
        );
    }
}
