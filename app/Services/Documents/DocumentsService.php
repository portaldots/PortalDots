<?php

declare(strict_types=1);

namespace App\Services\Documents;

use App\Contracts\AudiencePolicy;
use App\Eloquents\Circle;
use App\Eloquents\Document;
use App\Eloquents\Tag;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class DocumentsService
{
    /**
     * 配布資料を作成する
     *
     * @param string $name
     * @param string|null $description
     * @param UploadedFile $file
     * @param boolean $is_public 公開するかどうか
     * @param boolean $is_important 重要かどうか
     * @param string|null $notes スタッフ用メモ
     * @param string $audience 配布資料の公開範囲
     * @param array $viewable_tags 配布資料を閲覧可能な企画のタグ
     * @param array $viewable_circles 配布資料を閲覧可能な企画のID
     * @return Document
     */
    public function createDocument(
        string $name,
        ?string $description,
        UploadedFile $file,
        bool $is_public,
        bool $is_important,
        ?string $notes,
        string $audience = AudiencePolicy::EVERYONE,
        array $viewable_tags = [],
        array $viewable_circles = []
    ): Document {
        return DB::transaction(function () use (
            $name,
            $description,
            $file,
            $is_public,
            $is_important,
            $notes,
            $audience,
            $viewable_tags,
            $viewable_circles
        ) {
            $path = $file->store('documents');

            $document = Document::create([
                'name' => $name,
                'description' => $description,
                'path' => $path,
                'size' => $file->getSize(),
                'extension' => $file->getClientOriginalExtension(),
                'is_public' => $is_public,
                'is_important' => $is_important,
                'audience' => $audience,
                'notes' => $notes,
            ]);

            $this->syncViewableTagsAndCircles($document, $viewable_tags, $viewable_circles);

            return $document;
        });
    }

    /**
     * 配布資料を更新する
     *
     *
     * @param Document $document 更新対象の配布資料
     * @param string $name
     * @param string|null $description
     * @param UploadedFile|null $file
     * @param boolean $is_public 公開するかどうか
     * @param boolean $is_important 重要かどうか
     * @param string|null $notes スタッフ用メモ
     * @param string $audience 配布資料の公開範囲
     * @param array $viewable_tags 配布資料を閲覧可能な企画のタグ
     * @param array $viewable_circles 配布資料を閲覧可能な企画のID
     * @return bool
     */
    public function updateDocument(
        Document $document,
        string $name,
        ?string $description,
        ?UploadedFile $file,
        bool $is_public,
        bool $is_important,
        ?string $notes,
        string $audience = AudiencePolicy::EVERYONE,
        array $viewable_tags = [],
        array $viewable_circles = []
    ): bool {
        return DB::transaction(function () use (
            $document,
            $name,
            $description,
            $file,
            $is_public,
            $is_important,
            $notes,
            $audience,
            $viewable_tags,
            $viewable_circles
        ) {
            if (!empty($file)) {
                Storage::delete($document->path);
            }

            $result = $document->update([
                'name' => $name,
                'description' => $description,
                'path' => empty($file) ? $document->path : $file->store('documents'),
                'size' => empty($file) ? $document->size : $file->getSize(),
                'extension' => empty($file) ? $document->extension : $file->getClientOriginalExtension(),
                'is_public' => $is_public,
                'is_important' => $is_important,
                'audience' => $audience,
                'notes' => $notes,
            ]);

            $this->syncViewableTagsAndCircles($document, $viewable_tags, $viewable_circles);

            return $result;
        });
    }

    /**
     * 配布資料の閲覧可能なタグ・企画を同期する
     *
     * @param Document $document
     * @param array $viewable_tags
     * @param array $viewable_circles
     * @return void
     */
    private function syncViewableTagsAndCircles(Document $document, array $viewable_tags, array $viewable_circles)
    {
        // タグ検索時は大文字小文字の区別をしない
        $exist_tags = Tag::select('id')
            ->whereIn('name', $viewable_tags)
            ->get();
        $document->viewableTags()->sync($exist_tags->pluck('id')->all());

        $exist_circles = Circle::select('id')
            ->whereIn('id', $viewable_circles)
            ->get();
        $document->viewableCircles()->sync($exist_circles->pluck('id')->all());
    }

    /**
     * 配布資料を削除する
     *
     * @param Document $document
     *
     * @return bool
     */
    public function deleteDocument(Document $document): bool
    {
        Storage::delete($document->path);
        return $document->delete();
    }
}
