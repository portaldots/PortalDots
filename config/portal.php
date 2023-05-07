<?php

return [
    // ポータルの説明
    'description' => env('PORTAL_DESCRIPTION', ''),
    // ポータル管理者の組織名
    // ポータルを管理している実行委員会名などを指定します。
    'admin_name' => env('PORTAL_ADMIN_NAME'),
    // 連絡先メールアドレス
    // ここで設定したメールアドレスが、運営者の連絡先として表示されるほか、お問い合わせフォームの送信先として使用されます。
    'contact_email' => env('PORTAL_CONTACT_EMAIL'),
    // 管理者のTwitterのスクリーンネーム
    'admin_twitter' => env('PORTAL_ADMIN_TWITTER'),
    // 大学提供メールアドレスのドメイン・@ より前の種別を指定
    // 'student_id' (学籍番号) または 'user_id` (学籍番号ではない文字列) のどちらかを指定
    'univemail_local_part' => env('PORTAL_UNIVEMAIL_LOCAL_PART'),
    // 大学提供メールアドレスのドメイン・@ より後ろの文字列を指定
    'univemail_domain_part' => explode('|', env('PORTAL_UNIVEMAIL_DOMAIN_PART')),
    // 「学籍番号」の呼称
    'student_id_name' => env('PORTAL_STUDENT_ID_NAME'),
    // 「学校発行メールアドレス」の呼称
    'univemail_name' => env('PORTAL_UNIVEMAIL_NAME'),
    // アクセントカラー
    'primary_color_hsl' => [env('PORTAL_PRIMARY_COLOR_H', null), env('PORTAL_PRIMARY_COLOR_S', null), env('PORTAL_PRIMARY_COLOR_L', null)],
    // デモモード
    'enable_demo_mode' => env('PORTAL_ENABLE_DEMO_MODE', false),
];
