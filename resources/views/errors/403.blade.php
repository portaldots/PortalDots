@inject('selectorService', 'App\Services\Circles\SelectorService')

@extends('errors.layout')

@section('title', '403 Forbidden')
@section('top', 'アクセス権がありません')
@section('message')
    <p>権限がないか、アクセスできないページです</p>
    @auth
        @staffpage
            @if (Auth::user()->is_staff)
                <p class="text-danger">
                    <i class="fas fa-info-circle fa-fw"></i>
                    <strong>
                        {{ config('app.name') }}の管理者に「スタッフの権限設定」の変更を依頼することで、このページへアクセスできる場合があります。<br>
                        詳細については{{ config('app.name') }}の管理者へお問い合わせください。
                    </strong>
                </p>
            @endif
        @endstaffpage
        @if (count($selectorService->getSelectableCirclesList(Auth::user(), Request::path())) >= 2)
            <p>
                <i class="fas fa-info-circle fa-fw"></i>
                ログイン先の企画を変更するとアクセスできる場合があります
            </p>
        @endif
    @endauth
@endsection
@section('twitter', false)
