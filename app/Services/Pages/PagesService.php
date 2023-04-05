<?php

declare(strict_types=1);

namespace App\Services\Pages;

use App\Eloquents\Document;
use App\Eloquents\Page;
use App\Eloquents\User;
use App\Eloquents\Tag;
use App\Services\Emails\SendEmailService;
use App\Services\Utils\ActivityLogService;
use App\Services\Utils\FormatTextService;
use Illuminate\Support\Facades\DB;

class PagesService
{
    /**
     * @var SendEmailService
     */
    private $sendEmailService;

    /**
     * @var ReadsService
     */
    private $readsService;

    /**
     * @var ActivityLogService
     */
    private $activityLogService;

    /**
     * @var FormatTextService
     */
    private $formatTextService;

    public function __construct(
        SendEmailService $sendEmailService,
        ReadsService $readsService,
        ActivityLogService $activityLogService,
        FormatTextService $formatTextService
    ) {
        $this->sendEmailService = $sendEmailService;
        $this->readsService = $readsService;
        $this->activityLogService = $activityLogService;
        $this->formatTextService = $formatTextService;
    }

    /**
     * お知らせを作成する
     *
     * @param string $title タイトル
     * @param string $body 本文
     * @param User $created_by 作成者
     * @param string $notes スタッフ用メモ
     * @param array $viewable_tags お知らせを閲覧可能な企画のタグ
     * @param array $documents お知らせに関連する配布資料のID
     * @param bool $is_public お知らせを公開するか
     * @param bool $is_pinned お知らせを固定表示するか
     * @return Page
     */
    public function createPage(
        string $title,
        string $body,
        User $created_by,
        string $notes,
        array $viewable_tags,
        array $documents,
        bool $is_public,
        bool $is_pinned
    ): Page {
        return DB::transaction(function () use (
            $title,
            $body,
            $created_by,
            $notes,
            $viewable_tags,
            $documents,
            $is_public,
            $is_pinned
        ) {
            $page = Page::create([
                'title' => $title,
                'body' => $body,
                'is_pinned' => $is_pinned,
                'is_public' => $is_public,
                'notes' => $notes,
            ]);

            // タグ検索時は大文字小文字の区別をしない
            // ($tags と $exist_tags の間で大文字小文字が異なる場合、$exist_tags の表記を優先するため)
            $exist_tags = Tag::select('id', 'name')
                ->whereIn('name', $viewable_tags)
                ->orderBy('id')
                ->get();
            $page->viewableTags()->sync($exist_tags->pluck('id')->all());

            // タグの変更をログに残す
            $this->activityLogService->logOnlyAttributesChanged(
                'page_viewable_tag',
                $created_by,
                $page,
                [],
                $exist_tags
                    ->map(function ($tag) {
                        return [
                            'id' => $tag->id,
                            'name' => $tag->name,
                        ];
                    })
                    ->toArray()
            );

            // 関連する配布資料を保存する
            $exist_documents = Document::select('id', 'name')
                ->whereIn('id', $documents)
                ->orderBy('id')
                ->get();
            $page->documents()->sync($exist_documents->pluck('id')->all());

            // 関連する配布資料の変更をログに残す
            $this->activityLogService->logOnlyAttributesChanged(
                'page_document',
                $created_by,
                $page,
                [],
                $exist_documents
                    ->map(function ($document) {
                        return [
                            'id' => $document->id,
                            'name' => $document->name,
                        ];
                    })
                    ->toArray()
            );

            return $page;
        });
    }

    /**
     * お知らせを更新する
     *
     * @param Page $page 更新するお知らせ
     * @param string $title タイトル
     * @param string $body 本文
     * @param User $updated_by 更新者
     * @param string $notes スタッフ用メモ
     * @param array $viewable_tags お知らせを閲覧可能な企画のタグ
     * @param array $documents お知らせに関連する配布資料のID
     * @param bool $is_public お知らせを公開するか
     * @param bool $is_pinned お知らせを固定表示するか
     * @return bool
     */
    public function updatePage(
        Page $page,
        string $title,
        string $body,
        User $updated_by,
        string $notes,
        array $viewable_tags,
        array $documents,
        bool $is_public,
        bool $is_pinned
    ): bool {
        return DB::transaction(function () use (
            $page,
            $title,
            $body,
            $updated_by,
            $notes,
            $viewable_tags,
            $documents,
            $is_public,
            $is_pinned
        ) {
            $page->update([
                'title' => $title,
                'body' => $body,
                'is_pinned' => $is_pinned,
                'is_public' => $is_public,
                'notes' => $notes,
            ]);

            // 既読情報を管理する reads テーブルから、このお知らせの既読情報を全て削除する
            $this->readsService->deleteAllReadsByPage($page);

            $old_tags = $page
                ->viewableTags()
                ->orderBy('id')
                ->get();

            $old_documents = $page
                ->documents()
                ->orderBy('id')
                ->get();

            // タグ検索時は大文字小文字の区別をしない
            // ($tags と $exist_tags の間で大文字小文字が異なる場合、$exist_tags の表記を優先するため)
            $exist_tags = Tag::select('id', 'name')
                ->whereIn('name', $viewable_tags)
                ->orderBy('id')
                ->get();
            $page->viewableTags()->sync($exist_tags->pluck('id')->all());

            // タグの変更をログに残す
            $tags_map_function = function ($tag) {
                return [
                    'id' => $tag->id,
                    'name' => $tag->name,
                ];
            };
            $this->activityLogService->logOnlyAttributesChanged(
                'page_viewable_tag',
                $updated_by,
                $page,
                $old_tags->map($tags_map_function)->toArray(),
                $exist_tags->map($tags_map_function)->toArray()
            );

            // 関連する配布資料を保存する
            $exist_documents = Document::select('id', 'name')
                ->whereIn('id', $documents)
                ->orderBy('id')
                ->get();
            $page->documents()->sync($exist_documents->pluck('id')->all());

            // 関連する配布資料の変更をログに残す
            $documents_map_function = function ($document) {
                return [
                    'id' => $document->id,
                    'name' => $document->name,
                ];
            };
            $this->activityLogService->logOnlyAttributesChanged(
                'page_document',
                $updated_by,
                $page,
                $old_documents->map($documents_map_function)->toArray(),
                $exist_documents->map($documents_map_function)->toArray()
            );

            return true;
        });
    }

    public function setPinStatusForPage(Page $page, bool $is_pinned)
    {
        return DB::transaction(function () use ($page, $is_pinned) {
            $page->is_pinned = $is_pinned;
            $page->timestamps = false;
            return $page->save();
        });
    }

    public function removePage(Page $page)
    {
        return DB::transaction(function () use ($page) {
            $page->viewableTags()->detach();
            return $page->delete();
        });
    }

    /**
     * お知らせにおいて指定されているタグに所属している企画のユーザーへ、
     * メール送信予約を行う
     *
     * @param Page $page
     */
    public function sendEmailsByPage(Page $page)
    {
        $page->refresh();
        $users = User::verified()
            ->byTags(
                $page
                    ->viewableTags()
                    ->select('tags.id')
                    ->get()
            )
            ->get();
        $body = $page->body;

        $page->loadMissing(['documents' => function ($query) {
            $query->public();
        }]);

        // 関連する配布資料の一覧を末尾に追加する
        if ($page->documents->count() > 0) {
            $documents_markdown_list = $page
                ->documents
                ->map(function ($document) {
                    $escaped_name = $this->formatTextService->escapeMarkdown(
                        e($document->name)
                    );
                    $url = route('documents.show', ['document' => $document]);
                    $list_item = "- [**{$escaped_name}**]({$url})";
                    if (!empty($document->description)) {
                        $escaped_description = $this->formatTextService->escapeMarkdown(
                            e($document->description)
                        );
                        $escaped_description = str_replace(
                            ["\r\n", "\n", "\r"],
                            '',
                            $escaped_description
                        );
                        $list_item .= "\n   - {$escaped_description}";
                    }
                    return $list_item;
                })
                ->join("\n");
            $body .= <<<EOL


## 関連する配布資料
{$documents_markdown_list}
EOL;
        }

        $this->sendEmailService->bulkEnqueue($page->title, $body, $users);
    }
}
