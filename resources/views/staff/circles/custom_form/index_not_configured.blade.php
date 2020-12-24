@extends('layouts.no_drawer')

@section('title', '企画参加登録の設定')

@section('navbar')
    <app-nav-bar-back href="{{ url('home_staff/circles') }}" data-turbolinks="false">
        企画情報管理
    </app-nav-bar-back>
@endsection

@section('content')
    <app-container>
        <list-view>
            <template v-slot:title>企画参加登録の設定</template>
            <template v-slot:description>企画参加登録機能は<strong>無効</strong>になっています</template>
            <list-view-card>
                <list-view-empty icon-class="far fa-star" text="企画参加登録をウェブ化して時短しよう！">
                    <p>
                        「打ち込み作業からサヨナラ！」<br>
                        企画情報を企画関係者に入力してもらうことで、Excelでの打ち込み作業が不要になります。<br>
                        企画参加登録機能を有効にして、もっとクリエイティブなことに時間を使いましょう！
                    </p>
                    <form action="{{ route('staff.circles.custom_form.store') }}" method="post">
                        @csrf
                        <p>
                            <button type="submit" class="btn is-primary is-wide">
                                企画参加登録機能を有効にする
                            </button>
                        </p>
                    </form>
                    <p>(企画参加登録機能を有効にしても、デフォルトでは企画参加登録フォームは非公開の状態です)</p>
                </list-view-empty>
            </list-view-card>
        </list-view>
    </app-container>
@endsection
