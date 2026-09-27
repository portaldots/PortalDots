<?php

declare(strict_types=1);

namespace App\Services\Documents;

use App\Eloquents\Circle;
use App\Eloquents\Document;
use App\Eloquents\DocumentApproval;
use App\Eloquents\DocumentVersion;
use App\Eloquents\User;
use App\Exceptions\Documents\StaleDocumentApprovalException;
use Illuminate\Support\Facades\DB;

class DocumentApprovalsService
{
    /**
     * 配布資料の確認を、複数の企画へ依頼する
     *
     * 既に依頼済みの企画が含まれる場合、現在の版で既に確認待ちであれば何もしない。
     * それ以外（修正対応中・確認済み、または版の更新に追従できていない確認待ち）
     * であれば、現在の版の確認待ちへリセットする
     *
     * @param Document $document
     * @param int[] $circleIds
     * @param User $requestedBy
     * @return void
     */
    public function requestForCircles(Document $document, array $circleIds, User $requestedBy): void
    {
        DB::transaction(function () use ($document, $circleIds, $requestedBy) {
            $latestVersion = $document->versions()->orderByDesc('version')->firstOrFail();
            $circles = Circle::whereIn('id', $circleIds)->get();

            foreach ($circles as $circle) {
                $approval = DocumentApproval::where('document_id', $document->id)
                    ->where('circle_id', $circle->id)
                    ->lockForUpdate()
                    ->first();

                if (empty($approval)) {
                    $approval = DocumentApproval::create([
                        'document_id' => $document->id,
                        'circle_id' => $circle->id,
                        'document_version_id' => $latestVersion->id,
                        'status' => DocumentApproval::STATUS_PENDING,
                        'requested_by' => $requestedBy->id,
                    ]);
                } else {
                    $isAlreadyPendingOnLatest = $approval->status === DocumentApproval::STATUS_PENDING
                        && (int)$approval->document_version_id === $latestVersion->id;
                    if ($isAlreadyPendingOnLatest) {
                        continue;
                    }

                    $approval->update([
                        'status' => DocumentApproval::STATUS_PENDING,
                        'document_version_id' => $latestVersion->id,
                        'requested_by' => $requestedBy->id,
                        'lock_version' => $approval->lock_version + 1,
                    ]);
                }

                $approval->decisions()->create([
                    'document_version_id' => $latestVersion->id,
                    'status' => DocumentApproval::STATUS_PENDING,
                    'decided_by' => $requestedBy->id,
                ]);
            }
        });
    }

    /**
     * 確認依頼を取り消す
     *
     * @param Document $document
     * @param Circle $circle
     * @return void
     */
    public function cancelRequest(Document $document, Circle $circle): void
    {
        DocumentApproval::where('document_id', $document->id)
            ->where('circle_id', $circle->id)
            ->delete();
    }

    /**
     * 企画が確認依頼に対して決定する（確認済みにする・修正を依頼する）
     *
     * @param DocumentApproval $documentApproval
     * @param User $decidedBy
     * @param string $status DocumentApproval::STATUS_APPROVED または STATUS_CHANGES_REQUESTED
     * @param string|null $comment 修正を依頼する場合は必須
     * @param int $documentVersionId 画面表示時の版のID。現在の版と一致しない場合はStaleDocumentApprovalExceptionをthrowする
     * @param int $expectedLockVersion 画面表示時に読み込んだ lock_version。現在のDB上の値と
     *  一致しない場合はStaleDocumentApprovalExceptionをthrowする
     * @return DocumentApproval
     */
    public function decide(
        DocumentApproval $documentApproval,
        User $decidedBy,
        string $status,
        ?string $comment,
        int $documentVersionId,
        int $expectedLockVersion
    ): DocumentApproval {
        return DB::transaction(function () use (
            $documentApproval,
            $decidedBy,
            $status,
            $comment,
            $documentVersionId,
            $expectedLockVersion
        ) {
            $approval = DocumentApproval::whereKey($documentApproval->id)->lockForUpdate()->firstOrFail();

            // 確認済みの依頼は、スタッフが依頼し直すか新しい版を追加するまで判断を変えられない
            $isStale = $approval->lock_version !== $expectedLockVersion
                || (int)$approval->document_version_id !== $documentVersionId
                || $approval->status === DocumentApproval::STATUS_APPROVED;
            if ($isStale) {
                throw new StaleDocumentApprovalException($approval);
            }

            $approval->update([
                'status' => $status,
                'lock_version' => $approval->lock_version + 1,
            ]);

            $approval->decisions()->create([
                'document_version_id' => $approval->document_version_id,
                'status' => $status,
                'comment' => $comment,
                'decided_by' => $decidedBy->id,
            ]);

            return $approval;
        });
    }

    /**
     * 配布資料に新しい版が追加された際、その資料へのすべての確認依頼を
     * 新しい版の確認待ちへリセットする
     *
     * DocumentsService::updateDocument のトランザクション内、新しい版を
     * 作成した場合のみ呼び出される想定のため、ここでは独自にトランザクションを
     * 開始しない
     *
     * @param Document $document
     * @param DocumentVersion $newVersion
     * @param User|null $uploadedBy
     * @return void
     */
    public function resetAllToPendingOnNewVersion(
        Document $document,
        DocumentVersion $newVersion,
        ?User $uploadedBy
    ): void {
        $approvals = DocumentApproval::where('document_id', $document->id)->lockForUpdate()->get();

        foreach ($approvals as $approval) {
            $approval->update([
                'status' => DocumentApproval::STATUS_PENDING,
                'document_version_id' => $newVersion->id,
                'lock_version' => $approval->lock_version + 1,
            ]);

            $approval->decisions()->create([
                'document_version_id' => $newVersion->id,
                'status' => DocumentApproval::STATUS_PENDING,
                'decided_by' => $uploadedBy?->id,
            ]);
        }
    }
}
