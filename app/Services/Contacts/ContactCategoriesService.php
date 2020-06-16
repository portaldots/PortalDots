<?php

declare(strict_types=1);

namespace App\Services\Contacts;

use App\Eloquents\ContactCategory;
use App\Mail\Contacts\EmailCategoryMailable;
use Illuminate\Support\Facades\Mail;

class ContactCategoriesService
{
    /**
     * ContactCategory の新規作成・アップデート時にメール送信を確認する
     *
     * @param ContactCategory $category
     */
    public function send(ContactCategory $category)
    {
        Mail::to($category->email, $category->name)
            ->send(
                (new EmailCategoryMailable($category))
                    ->subject('お問い合わせ先に設定されました')
            );
    }
}
