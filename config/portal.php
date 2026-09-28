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
    'navigation' => [
        'staff_home_route' => 'staff.index',
        'show_mode_switch' => true,
    ],

    // ユーザー登録・ログイン方法・学籍番号・大学提供メールアドレスの有無
    // プライベートなデプロイ用パッケージが Service Provider から
    // config(['portal.registration.enabled' => false]) のように上書きすることで、
    // OSS側のファイルを変更せずに招待制での運用に対応できる。
    // 参照する際は config() を直接呼ばず App\Services\Auth\AuthSettings を使うこと
    'registration' => [
        // ユーザー登録を受け付けるか
        // false にすると /register のルートが登録されず(404)、登録への導線も表示されない
        'enabled' => true,
    ],
    'auth' => [
        // ログインIDとして受け付けるカラム。'email' と 'student_id' の組み合わせで指定する
        'login_identifiers' => ['email', 'student_id'],
        // 学籍番号の入力・表示・必須化を行うか
        'student_id' => true,
        // 大学提供メールアドレスの入力・表示・認証を行うか
        // false の場合、連絡先メールアドレスの認証のみで「メール認証済み」とみなす
        'univemail' => true,
    ],

    // 用語
    // term() ヘルパーで参照する。プライベートなデプロイ用パッケージが
    // config(['portal.terms.circle' => '案件']) のように上書きすることで、
    // OSS側のファイルを変更せずに画面上の呼称を変更できる
    'terms' => [
        // 「企画」の呼称
        'circle' => '企画',
        // 「申請」の呼称
        'form' => '申請',
        // 「配布資料」の呼称
        'document' => '配布資料',
        // 「お問い合わせ」の呼称
        'contact' => 'お問い合わせ',
        // 主催側（「スタッフ」）の呼称
        'staff_side' => 'スタッフ',
        // 配布資料の確認依頼の状態ラベル（企画向け）
        'document_status_circle_pending' => '確認してください',
        'document_status_circle_changes_requested' => '修正対応中',
        'document_status_circle_approved' => '確認済み',
        // 配布資料の確認依頼の状態ラベル（スタッフ向け）
        'document_status_staff_pending' => '確認待ち',
        'document_status_staff_changes_requested' => '修正依頼あり',
        'document_status_staff_approved' => '確認済み',
    ],
];
