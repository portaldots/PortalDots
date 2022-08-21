@extends('layouts.app')

@section('title', 'ユーザー設定')

@section('content')
    @include('includes.user_settings_tab_strip')
    <form method="POST" action="{{ route('user.appearance') }}">
        @method('patch')
        @csrf

        <app-container>
            <appearance-settings default-theme="{{ $theme }}"></appearance-settings>

            <div class="text-center pt-spacing-md pb-spacing">
                <button type="submit" class="btn is-primary is-wide">
                    保存
                </button>
            </div>
        </app-container>
    </form>
@endsection
