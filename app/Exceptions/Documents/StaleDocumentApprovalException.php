<?php

namespace App\Exceptions\Documents;

use App\Eloquents\DocumentApproval;
use RuntimeException;

/**
 * lock_version が現在のDB上の値と一致しない場合、または対象の版が現在の
 * 最新版でない場合、つまり別の操作によって確認依頼がすでに更新されている
 * 場合にthrowされる
 */
class StaleDocumentApprovalException extends RuntimeException
{
    public DocumentApproval $documentApproval;

    public function __construct(DocumentApproval $documentApproval)
    {
        parent::__construct('確認依頼が別の操作によって更新されているため、この操作を行えません。');
        $this->documentApproval = $documentApproval;
    }
}
