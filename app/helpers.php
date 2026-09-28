<?php

declare(strict_types=1);

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
