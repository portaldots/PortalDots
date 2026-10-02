<?php

declare(strict_types=1);

namespace App\Services\Documents;

use App\Contracts\AudiencePolicy;
use App\Contracts\FileStorageLayout;
use App\Eloquents\Circle;
use App\Eloquents\Document;
use App\Eloquents\Tag;
use App\Eloquents\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class DocumentsService
{
    /**
     * @var DocumentApprovalsService
     */
    private $documentApprovalsService;

    private FileStorageLayout $fileStorageLayout;

    public function __construct(
        DocumentApprovalsService $documentApprovalsService,
        FileStorageLayout $fileStorageLayout
    ) {
        $this->documentApprovalsService = $documentApprovalsService;
        $this->fileStorageLayout = $fileStorageLayout;
    }

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
     * @param User|null $uploaded_by ファイルをアップロードしたスタッフ
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
        array $viewable_circles = [],
        ?User $uploaded_by = null
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
            $viewable_circles,
            $uploaded_by
        ) {
            $path = $file->store($this->fileStorageLayout->directoryFor(FileStorageLayout::AREA_DOCUMENTS));
            $size = $file->getSize();
            $extension = $file->getClientOriginalExtension();

            $document = Document::create([
                'name' => $name,
                'description' => $description,
                'path' => $path,
                'size' => $size,
                'extension' => $extension,
                'is_public' => $is_public,
                'is_important' => $is_important,
                'audience' => $audience,
                'notes' => $notes,
            ]);

            $document->versions()->create([
                'version' => 1,
                'path' => $path,
                'size' => $size,
                'extension' => $extension,
                'uploaded_by' => $uploaded_by?->id,
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
     * @param User|null $uploaded_by ファイルをアップロードしたスタッフ
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
        array $viewable_circles = [],
        ?User $uploaded_by = null
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
            $viewable_circles,
            $uploaded_by
        ) {
            // バージョン番号の衝突を防ぐため、行ロックを取ってから採番する
            $document = Document::whereKey($document->id)->lockForUpdate()->firstOrFail();

            $path = $document->path;
            $size = $document->size;
            $extension = $document->extension;

            if (!empty($file)) {
                $path = $file->store($this->fileStorageLayout->directoryFor(FileStorageLayout::AREA_DOCUMENTS));
                $size = $file->getSize();
                $extension = $file->getClientOriginalExtension();

                $nextVersion = (int)$document->versions()->max('version') + 1;
                $newVersion = $document->versions()->create([
                    'version' => $nextVersion,
                    'path' => $path,
                    'size' => $size,
                    'extension' => $extension,
                    'uploaded_by' => $uploaded_by?->id,
                ]);

                // 新しい版が追加されたら、依頼済みの確認はすべて新しい版の確認待ちへ戻す
                $this->documentApprovalsService->resetAllToPendingOnNewVersion($document, $newVersion, $uploaded_by);
            }

            $result = $document->update([
                'name' => $name,
                'description' => $description,
                'path' => $path,
                'size' => $size,
                'extension' => $extension,
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
        foreach ($document->versions as $version) {
            Storage::delete($version->path);
        }
        return $document->delete();
    }
}
