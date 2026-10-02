<?php

declare(strict_types=1);

namespace App\Services\Pages;

use App\Contracts\AudiencePolicy;
use App\Eloquents\Circle;
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
     * @param string $audience お知らせの公開範囲
     * @param array $viewable_circles お知らせを閲覧可能な企画のID
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
        bool $is_pinned,
        string $audience = AudiencePolicy::EVERYONE,
        array $viewable_circles = []
    ): Page {
        return DB::transaction(function () use (
            $title,
            $body,
            $created_by,
            $notes,
            $viewable_tags,
            $documents,
            $is_public,
            $is_pinned,
            $audience,
            $viewable_circles
        ) {
            $page = Page::create([
                'title' => $title,
                'body' => $body,
                'is_pinned' => $is_pinned,
                'is_public' => $is_public,
                'audience' => $audience,
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

            // 閲覧可能な企画を保存する
            $exist_circles = Circle::select('id', 'name')
                ->whereIn('id', $viewable_circles)
                ->orderBy('id')
                ->get();
            $page->viewableCircles()->sync($exist_circles->pluck('id')->all());

            // 閲覧可能な企画の変更をログに残す
            $this->activityLogService->logOnlyAttributesChanged(
                'page_viewable_circle',
                $created_by,
                $page,
                [],
                $exist_circles
                    ->map(function ($circle) {
                        return [
                            'id' => $circle->id,
                            'name' => $circle->name,
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
     * @param string $audience お知らせの公開範囲
     * @param array $viewable_circles お知らせを閲覧可能な企画のID
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
        bool $is_pinned,
        string $audience = AudiencePolicy::EVERYONE,
        array $viewable_circles = []
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
            $is_pinned,
            $audience,
            $viewable_circles
        ) {
            $page->update([
                'title' => $title,
                'body' => $body,
                'is_pinned' => $is_pinned,
                'is_public' => $is_public,
                'audience' => $audience,
                'notes' => $notes,
            ]);

            // 既読情報を管理する reads テーブルから、このお知らせの既読情報を全て削除する
            $this->readsService->deleteAllReadsByPage($page);

            $old_tags = $page
                ->viewableTags()
                ->orderBy('id')
                ->get();

            $old_circles = $page
                ->viewableCircles()
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

            // 閲覧可能な企画を保存する
            $exist_circles = Circle::select('id', 'name')
                ->whereIn('id', $viewable_circles)
                ->orderBy('id')
                ->get();
            $page->viewableCircles()->sync($exist_circles->pluck('id')->all());

            // 閲覧可能な企画の変更をログに残す
            $circles_map_function = function ($circle) {
                return [
                    'id' => $circle->id,
                    'name' => $circle->name,
                ];
            };
            $this->activityLogService->logOnlyAttributesChanged(
                'page_viewable_circle',
                $updated_by,
                $page,
                $old_circles->map($circles_map_function)->toArray(),
                $exist_circles->map($circles_map_function)->toArray()
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
     * お知らせの公開範囲に応じたメール送信対象ユーザーを取得する
     *
     * audience が everyone・signed_in の場合はメール認証済みの全ユーザー、
     * selected の場合は閲覧可能なタグ・企画のいずれかに該当する企画に
     * 所属しているユーザーのみが対象となる
     *
     * @param Page $page
     * @return \Illuminate\Support\Collection
     */
    private function recipientsForPage(Page $page)
    {
        if ($page->audience !== AudiencePolicy::SELECTED) {
            return User::verified()->get();
        }

        $circle_ids = $this->matchingCircleIdsForSelectedAudience($page);

        if ($circle_ids->isEmpty()) {
            return collect();
        }

        return User::verified()
            ->whereHas('circles', function ($query) use ($circle_ids) {
                $query->whereIn('circles.id', $circle_ids);
            })
            ->get();
    }

    /**
     * 配布資料が、指定したメール送信対象ユーザー全員から閲覧可能かどうかを判定する
     *
     * メール送信対象ユーザーは必ずログイン済みのユーザーであるため、
     * audience が everyone・signed_in の配布資料は常に全員が閲覧できる
     *
     * @param Document $document
     * @param \Illuminate\Support\Collection $recipients
     * @return bool
     */
    private function isDocumentVisibleToAllRecipients(Document $document, $recipients): bool
    {
        if ($recipients->isEmpty()) {
            return true;
        }

        if ($document->audience !== AudiencePolicy::SELECTED) {
            return true;
        }

        $circle_ids = $this->matchingCircleIdsForSelectedAudience($document);

        if ($circle_ids->isEmpty()) {
            return false;
        }

        return User::whereIn('id', $recipients->pluck('id'))
            ->whereDoesntHave('circles', function ($query) use ($circle_ids) {
                $query->whereIn('circles.id', $circle_ids);
            })
            ->doesntExist();
    }

    /**
     * audience が selected のお知らせ・配布資料について、閲覧可能なタグを持つか
     * 直接閲覧可能な企画として指定されている企画のIDを取得する
     *
     * @param Page|Document $model
     * @return \Illuminate\Support\Collection
     */
    private function matchingCircleIdsForSelectedAudience($model)
    {
        $tag_ids = $model->viewableTags()->pluck('tags.id');
        $circle_ids = $model->viewableCircles()->pluck('circles.id');

        if ($tag_ids->isEmpty() && $circle_ids->isEmpty()) {
            return collect();
        }

        return Circle::where(function ($query) use ($tag_ids, $circle_ids) {
            $query->whereHas('tags', function ($query) use ($tag_ids) {
                $query->whereIn('tags.id', $tag_ids);
            })->orWhereIn('id', $circle_ids);
        })->pluck('id');
    }

    /**
     * お知らせの公開範囲に応じたユーザーへ、メール送信予約を行う
     *
     * @param Page $page
     */
    public function sendEmailsByPage(Page $page)
    {
        $page->refresh();
        $users = $this->recipientsForPage($page);
        $body = $page->body;

        $page->loadMissing(['documents' => function ($query) {
            $query->public();
        }]);

        // メール送信対象ユーザー全員が閲覧できない配布資料は一覧から除く
        $visible_documents = $page->documents->filter(function ($document) use ($users) {
            return $this->isDocumentVisibleToAllRecipients($document, $users);
        });

        // 関連する配布資料の一覧を末尾に追加する
        if ($visible_documents->count() > 0) {
            $documents_markdown_list = $visible_documents
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
