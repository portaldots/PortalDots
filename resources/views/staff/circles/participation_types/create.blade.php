@extends('layouts.app')

@section('title', '新規作成 — 参加種別')

@section('navbar')
    <app-nav-bar-back href="{{ route('staff.circles.index') }}">
        企画情報管理
    </app-nav-bar-back>
@endsection

@section('content')
    <app-header>
        <template v-slot:title>参加種別を新規作成</template>
    </app-header>
    <app-container>
        <form action="{{ route('staff.circles.participation_types.store') }}" method="post">
            @csrf
            <list-view>
                <list-view-form-group label-for="name">
                    <template v-slot:label>参加種別名</template>
                    <template v-slot:description>
                        一般ユーザーに対して表示されます。例：模擬店、ステージ、など。
                    </template>
                    <input id="name" type="text" class="form-control @error('name') is-invalid @enderror" name="name"
                        required>
                    @error('name')
                        <template v-slot:invalid>{{ $message }}</template>
                    @enderror
                </list-view-form-group>
            </list-view>
            <app-fixed-form-footer>
                <button type="submit" class="btn is-primary is-wide">保存</button>
            </app-fixed-form-footer>
            <p class="pt-spacing-md text-center">
                保存すると、企画参加登録フォームの設定ができるようになります。
            </p>
        </form>
        <list-view>
            <template v-slot:title>企画参加登録機能について</template>
            <list-view-card class="markdown">
                @include('includes.circles_custom_form_instructions')
            </list-view-card>
        </list-view>
        <list-view>
            <template v-slot:title>参加種別について</template>
            <list-view-card class="markdown">
                <ul>
                    <li>{{ config('app.name') }}上で作成された企画は、いずれかの参加種別に属します。</li>
                    <li>参加種別ごとの企画一覧を表示できます。</li>
                </ul>
            </list-view-card>
        </list-view>
    </app-container>
@endsection
