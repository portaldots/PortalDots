@extends('layouts.app')

@section('title', '企画参加登録の設定')

@section('navbar')
    <app-nav-bar-back href="{{ route('staff.circles.index') }}">
        企画情報管理
    </app-nav-bar-back>
@endsection

@section('content')
    <app-header>
        <template v-slot:title>企画参加登録の設定</template>
    </app-header>
    <app-container>
        <list-view>
            <template v-slot:description>企画参加登録機能は<strong>無効</strong>になっています</template>
            <list-view-card no-border>
                <list-view-empty icon-class="far fa-star" text="企画参加登録をウェブ化して時短しよう！">
                    <form action="{{ route('staff.circles.custom_form.store') }}" method="post">
                        @csrf
                        <p>
                            <button type="submit" class="btn is-primary is-wide">
                                企画参加登録機能を有効にする
                            </button>
                        </p>
                    </form>
                </list-view-empty>
            </list-view-card>
            <list-view-card no-border>
                <div class="markdown">
                    <h2>企画参加登録機能について</h2>
                    @include('includes.circles_custom_form_instructions')
                </div>
                <div class="pt-spacing"></div>
                <app-info-box primary>
                    企画参加登録機能を有効にしても、デフォルトでは企画参加登録フォームは非公開の状態です
                </app-info-box>
            </list-view-card>
        </list-view>
    </app-container>
@endsection
