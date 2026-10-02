<?php

declare(strict_types=1);

namespace App\Eloquents;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $thread_entry_id
 * @property string $path
 * @property string $name
 * @property int $size
 * @property string $mime
 */
class ThreadEntryAttachment extends Model
{
    /**
     * 1つのやり取りに添付できるファイル数の上限
     */
    public const MAX_FILES = 5;

    /**
     * 添付ファイル1つあたりの上限サイズ(KB)
     */
    public const MAX_FILE_SIZE_KB = 10240;

    protected $fillable = [
        'thread_entry_id',
        'path',
        'name',
        'size',
        'mime',
    ];

    public function threadEntry()
    {
        return $this->belongsTo(ThreadEntry::class);
    }
}
