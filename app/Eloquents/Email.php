<?php

namespace App\Eloquents;

use Illuminate\Database\Eloquent\Model;

/**
 * @property bool $is_sent
 * @property bool $is_locked
 */
class Email extends Model
{
    /**
     * メール送信済であれば true を返す動的プロパティを作る
     *
     * @return bool
     */
    public function getIsSentAttribute(): bool
    {
        return !empty($this->sent_at);
    }

    /**
     * 排他ロック中であれば true を返す動的プロパティを作る
     *
     * @return bool
     */
    public function getIsLockedAttribute(): bool
    {
        return !empty($this->locked_at);
    }
}
