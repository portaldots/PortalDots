@extends('layouts.app')

@section('title', empty($user) ? '新規作成 — ユーザー管理' : "{$user->name} — ユーザー管理")

@section('navbar')
    <app-nav-bar-back href="{{ route('staff.users.index') }}">
        ユーザー管理
    </app-nav-bar-back>
@endsection

@section('content')
    <form method="post" action="{{ empty($user) ? route('staff.users.store') : route('staff.users.update', $user) }}" enctype="multipart/form-data">
        @method(empty($user) ? 'post' : 'patch' )
        @csrf

        <app-header>
            @if (empty($user))
                <template v-slot:title>ユーザーを新規作成</template>
            @endif
            @isset ($user)
                <template v-slot:title>ユーザーを編集</template>
                <div>ユーザーID : {{ $user->id }}</div>
            @endisset
        </app-header>

        <app-container>
            <list-view>
               @include('includes.user_register_form')
            </list-view>

            <div class="text-center pt-spacing-md pb-spacing">
                <button type="submit" class="btn is-primary is-wide">保存</button>
            </div>
        </app-container>
    </form>
@endsection
