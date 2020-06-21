@extends('layouts.no_drawer')

@section('title', 'Markdownガイド')

@section('content')
    <app-header>
        <template v-slot:title>Markdownガイド</template>
        このページでは、Markdown記法のうち、よく利用されるものを紹介します。
    </app-header>

    {{-- Blade フォーマッターが Markdown にも余計な整形をしてしまうため、冗長な書き方になっている --}}
    <app-container class="markdown py-spacing-lg">
        @markdown(str_replace([str_repeat(' ', 8), '/indent/'], ['', str_repeat(' ', 4)], '
        # 見出し

        ```
        # これは大きい見出し(h1)です
        ## これは見出し(h2)です
        ### これは見出し(h3)です
        #### これは見出し(h4)です
        ##### これは見出し(h5)です
        ###### これは見出し(h6)です
        ```

        # これは大きい見出し(h1)です
        ## これは見出し(h2)です
        ### これは見出し(h3)です
        #### これは見出し(h4)です
        ##### これは見出し(h5)です
        ######これは見出し(h6)です
        # 箇条書き
        ```
        * 箇条書き1
        * 箇条書き2
        /indent/* 箇条書き2-1 — * の前に半角スペースを2つ以上入れると段下げできます
        /indent/* 箇条書き2-2
        * 箇条書き3
        ```

        * 箇条書き1
        * 箇条書き2
        /indent/* 箇条書き2-1 — * の前に半角スペースを2つ以上入れると段下げできます
        /indent/* 箇条書き2-2
        * 箇条書き3

        # 番号付きリスト
        ```
        1. 最初の項目
        1. 次の項目
        /indent/1. 子項目a — 1. の前に半角スペースを2つ以上入れると段下げできます
        /indent/1. 子項目b
        1. 最後の項目
        ```

        1. 最初の項目
        1. 次の項目
        /indent/1. 子項目a — 1. の前に半角スペースを2つ以上入れると段下げできます
        /indent/1. 子項目b
        1. 最後の項目

        # 太字
        ```
        **ここ**が太字になります
        ```

        **ここ**が太字になります

        # 取り消し線
        ```
        ~~ここ~~に取り消し線を引きます
        ```

        ~~ここ~~に取り消し線を引きます

        # リンク
        ```
        [ここをクリック](https://www.google.com)するとGoogleにアクセスします
        ```

        <a href="https://www.google.com" rel="noreferrer">ここをクリック</a>するとGoogleにアクセスします

        # 引用

        ```
        > 一人ひとりの「想い」をつなぎ、学園祭を最高に楽しくする
        ```

        > 一人ひとりの「想い」をつなぎ、学園祭を最高に楽しくする

        # 表(テーブル)

        ```
        | 列1 | 列2 | 列3 |
        | --- | --- | --- |
        | John | Doe | Male |
        | Mary | Smith | Female |
        ```

        | 列1 | 列2 | 列3 |
        | --- | --- | --- |
        | John | Doe | Male |
        | Mary | Smith | Female |
        '))
    </app-container>
@endsection
