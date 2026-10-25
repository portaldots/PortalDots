<?php

declare(strict_types=1);

if (!function_exists('isDemoReadOnly')) {
    /**
     * デモモードで、書き込みを拒否すべきかを返す。
     * 共有サーバーのデモは読み取り専用にし、利用者ごとにブラウザ内で動くデモ（wasm-demo）だけが
     * PORTAL_DEMO_ALLOW_WRITES で書き込みを許可する
     */
    function isDemoReadOnly(): bool
    {
        return config('portal.enable_demo_mode') && !config('portal.demo_allow_writes');
    }
}

if (!function_exists('term')) {
    /**
     * config/portal.php の terms 配列から、$key に対応する用語を返す。
     * プライベートなデプロイ用パッケージが config(['portal.terms.circle' => '案件']) の
     * ように上書きすることで、OSS側のファイルを変更せずに画面上の呼称を変更できる
     */
    function term(string $key): string
    {
        return config("portal.terms.{$key}");
    }
}
