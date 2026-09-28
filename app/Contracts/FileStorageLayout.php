<?php

declare(strict_types=1);

namespace App\Contracts;

/**
 * アップロードされたファイルの保存先ディレクトリを決める拡張ポイント。
 * answer_details・documents・thread_attachments の各領域について、
 * 保存時と配信時（パスがその領域の配下かどうかのチェック）の両方で使う
 */
interface FileStorageLayout
{
    /**
     * 回答のアップロードファイル
     */
    public const AREA_ANSWER_DETAILS = 'answer_details';

    /**
     * 配布資料のファイル
     */
    public const AREA_DOCUMENTS = 'documents';

    /**
     * 会話の添付ファイル
     */
    public const AREA_THREAD_ATTACHMENTS = 'thread_attachments';

    /**
     * $area に対応する保存先ディレクトリ（Storage の相対パス）を返す
     *
     * @param string $area self::AREA_* のいずれか
     */
    public function directoryFor(string $area): string;
}
