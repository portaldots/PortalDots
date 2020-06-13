@inject('selectorService', 'App\Services\Circles\SelectorService')

@extends('errors.layout')

@section('title', '403 Forbidden')
@section('top', $exception->getMessage() ?: 'アクセスが拒否されました')
@section('message')
    <p>権限がないか、アクセスできないページです</p>
    @auth
        @if (count($selectorService->getSelectableCirclesList(Auth::user(), Request::path())) >= 2)
            <p>
                <i class="fas fa-info-circle fa-fw"></i>
                ログイン先の企画を変更するとアクセスできる場合があります
            </p>
        @endif
    @endauth
@endsection
@section('twitter', false)
