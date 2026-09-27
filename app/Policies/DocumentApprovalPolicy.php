<?php

namespace App\Policies;

use App\Eloquents\DocumentApproval;
use App\Eloquents\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Gate;

class DocumentApprovalPolicy
{
    use HandlesAuthorization;

    /**
     * 確認依頼に対して決定できるかどうか。既定では、依頼先の企画に
     * 所属しているユーザーであれば誰でも決定できる
     *
     * @param User $user
     * @param DocumentApproval $documentApproval
     * @return bool
     */
    public function decide(User $user, DocumentApproval $documentApproval): bool
    {
        return Gate::forUser($user)->allows('circle.belongsTo', $documentApproval->circle);
    }
}
