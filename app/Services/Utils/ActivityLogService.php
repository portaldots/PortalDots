<?php

declare(strict_types=1);

namespace App\Services\Utils;

use App\Eloquents\User;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Database\Eloquent\JsonEncodingException;
use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;
use Spatie\Activitylog\Exceptions\CouldNotLogActivity;

class ActivityLogService
{
    /**
     * Eloquent で sync を使ったデータ保存をログに残す
     *
     * @param string $logName ログの名前
     * @param User $causedBy 実施者
     * @param Model $performedOn 変更対象のモデル
     * @param array $old 旧データ
     * @param array $new 新データ
     * @return void
     * @throws BindingResolutionException
     * @throws InvalidArgumentException
     * @throws CouldNotLogActivity
     * @throws JsonEncodingException
     */
    public function logOnlyAttributesChanged(
        string $logName,
        User $causedBy,
        Model $performedOn,
        array $old,
        array $new
    ) {
        if (strcmp(json_encode($old), json_encode($new)) !== 0) {
            activity($logName)
                ->causedBy($causedBy)
                ->performedOn($performedOn)
                ->withProperties(
                    [
                        'old' => [$logName => $old],
                        'attributes' => [$logName => $new],
                    ]
                )
                ->log('updated');
        }
    }
}
